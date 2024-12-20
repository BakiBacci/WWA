<?php

namespace App\Controller\Front;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\CartService;
use App\Service\EmailService;
use App\Service\InvoiceService;
use App\Service\OrderService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;

#[IsGranted('IS_AUTHENTICATED')]
#[Route('/commande', name: 'front_order_')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, CartService $cartService, OrderService $orderService): Response
    {
        $user = $this->getUser();
        $cart = $cartService->getCart();

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('front_cart_index');
        }

        $order = $orderService->createOrder($user, $cart);

        $em->persist($order);
        $em->flush();

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $stripeSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande #' . $order->getId(),
                    ],
                    'unit_amount' => $order->getTotal() * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('front_order_validated', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('front_order_confirmation', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->render('front/order/checkout.html.twig', [
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'checkout_session_id' => $stripeSession->id,
            'order' => $order
        ]);
    }

    #[Route('/confirmation/{id}', name: 'confirmation', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function confirmation(int $id, OrderRepository $repository): Response
    {
        $order = $repository->findOrderWithRelations($id);

        return $this->render('front/order/confirmation.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/validation/{id}', name: 'validated', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function validated(int $id, OrderRepository $repository, EmailService $emailService, EntityManagerInterface $em, InvoiceService $invoiceService)
    {
        $order = $repository->findOrderWithRelations($id);

        if ($order->getStatus() !== OrderStatus::PAID) {
            $order->setStatus(OrderStatus::PAID)
                  ->setPaidAt(new DateTimeImmutable());

            $em->flush();

            $emailService->sendOrderConfirmationEmail($order);
            $pdf = $invoiceService->generateInvoice($order);
            $invoiceService->saveInvoice($order, $pdf);

            $this->addFlash('success', 'Votre commande a bien été passée, vous allez recevoir un email de confirmation');
        } else {
            $this->addFlash('info', 'Cette commande a déjà été validée');
        }

        return $this->redirectToRoute('front_home_index');
    }

    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request, EntityManagerInterface $em, OrderRepository $repository): JsonResponse
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');
        $endpoint_secret = $this->getParameter('stripe_webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return new JsonResponse(['error' => 'Invalid signature'], 400);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id;

            $order = $repository->find($orderId);
            if ($order && $order->getStatus() !== OrderStatus::PAID) {
                $order->setStatus(OrderStatus::PAID);
                $order->setPaidAt(new \DateTimeImmutable());
                $em->flush();
            }
        }

        return new JsonResponse(['status' => 'success']);
    }
}

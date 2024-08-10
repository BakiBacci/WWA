<?php

namespace App\Controller\Front;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/profile', name: 'front_profile_', methods: ['GET'])]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $userId = $this->getUser()->getId();
        // $order = $orderRepository->findOrdersByCustomerId($userId);
        $order = $orderRepository->paginateOrdersByCustomerId($request->query->getInt('page', 1), $userId);

        return $this->render('front/profile/index.html.twig', [
            'orders' => $order,
        ]);
    }

    #[Route('/detail/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function show(?Order $order): Response
    {
        return $this->render('front/profile/show.html.twig', [
            'order' => $order,
        ]);
    }
}

<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART = 'cart';
    private $session;

    public function __construct(
        private ProductRepository $repository,
        private RequestStack $requestStack
    ) {
        $this->session = $this->requestStack->getSession();
    }

    public function getCart()
    {
        return $this->session->get(self::CART, []);
    }

    public function getCartContent(): array
    {
        $cart = $this->session->get(self::CART, []);

        $dataCart = [];
        $totalCart = 0;

        foreach ($cart as $id => $quantity) {
            $product = $this->repository->find($id);
            if (!$product) {
                continue;
            }

            $total = $product->getPrice() * $quantity;

            $dataCart[] = [
                'product' => $product,
                'quantity' => $quantity,
                'total' => $total,
            ];

            $totalCart = $totalCart + $total;
        }

        return [
            'dataCart' => $dataCart,
            'total' => $totalCart
        ];
    }

    public function incrementProductQuantity(int $id): void
    {
        $cart = $this->session->get(self::CART, []);

        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set(self::CART, $cart);
    }

    public function decreaseProductQuantity(int $id): void
    {
        $cart = $this->session->get(self::CART, []);

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $this->session->set(self::CART, $cart);
    }

    public function removeProductFromCart(int $id): void
    {
        $cart = $this->session->get(self::CART, []);
        unset($cart[$id]);
        $this->session->set(self::CART, $cart);
    }

    public function emptyCart(): void
    {
        $this->session->remove(self::CART);
    }
}

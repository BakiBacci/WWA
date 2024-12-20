<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'front_products_')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(ProductRepository $repository, Request $request): Response
    {
        $pagination = $repository->paginateProductsOrderByUpdatedAt($request->query->getInt('page', 1));

        return $this->render('front/home/list.html.twig', [
            'products' => $pagination,
        ]);
    }

    #[Route('/detail/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug, ProductRepository $repository): Response
    {
        $product = $repository->findProductWithCategoryBySlug($slug);

        return $this->render('front/home/show.html.twig', [
            'product' => $product
        ]);
    }
}
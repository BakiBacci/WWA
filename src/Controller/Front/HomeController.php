<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'front_home_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        // Contenu pour votre page d'accueil
        return $this->render('front/home/index.html.twig');
    }

    #[Route('/conditions-du-site', name: 'terms_conditions')]
    public function terms()
    {
        return $this->render('front/home/terms.html.twig');
    }
}
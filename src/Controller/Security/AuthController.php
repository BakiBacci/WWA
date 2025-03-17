<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\LoginType;
use App\Form\RegistrationType;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/auth', name: 'front_auth_')]
class AuthController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils
    ): Response {
       
        $user = new User();
        $registrationForm = $this->createForm(RegistrationType::class, $user);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $plainPassword= $registrationForm->get('plainPassword')->getData();
            
            $hashedPassword = $passwordHasher->hashPassword(
                $user,$plainPassword
            );
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            
            return $this->redirectToRoute('front_auth_index');
        }

        
        $loginForm = $this->createForm(LoginType::class);


        $error = $authenticationUtils->getLastAuthenticationError();


        return $this->render('front/auth/index.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'loginForm' => $loginForm->createView(),
            'error' => $error, 
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        
        throw new \LogicException('Cette méthode doit être interceptée par le pare-feu de déconnexion de Symfony.');
    }
}
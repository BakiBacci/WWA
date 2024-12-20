<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('/auth', name: 'app_auth')]
    public function index(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        // Formulaire d'inscription
        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);
        $registrationForm->handleRequest($request);

        // Formulaire de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $loginForm = $this->createForm(LoginType::class);

        // Traitement du formulaire d'inscription
        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            // Hachage du mot de passe et enregistrement de l'utilisateur
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $registrationForm->get('plainPassword')->getData()
                )
            );

            // Enregistrez l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirigez vers la page de connexion ou affichez un message de succès
            return $this->redirectToRoute('app_login'); // ou une autre route
        }

        return $this->render('security/index.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'loginForm' => $loginForm->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Cette méthode est maintenant intégrée dans le formulaire d'authentification général
        return new Response(); // Vous pouvez laisser cela vide ou gérer une redirection si nécessaire.
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
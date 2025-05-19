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
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth', name: 'front_auth_')]
class AuthController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils,
        ValidatorInterface $validator
    ): Response {
        $user = new User();
        $registrationForm = $this->createForm(RegistrationType::class, $user);
        $registrationForm->handleRequest($request);

        // Diagnostic complet avant validation
        if ($registrationForm->isSubmitted()) {
            // Vérification de la structure de l'entité
            $entityDiagnostic = [
                'email_defined' => property_exists($user, 'email'),
                'plainPassword_defined' => property_exists($user, 'plainPassword'),
                'email_getter' => method_exists($user, 'getEmail'),
                'plainPassword_getter' => method_exists($user, 'getPlainPassword')
            ];

            // Validation manuelle de l'entité
            $validationErrors = $validator->validate($user);
            $entityErrors = [];
            foreach ($validationErrors as $error) {
                $entityErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }

            if (!$registrationForm->isValid()) {
                $formErrors = [];
                foreach ($registrationForm->getErrors(true) as $error) {
                    $formErrors[] = [
                        'field' => $error->getOrigin()->getName(),
                        'message' => $error->getMessage(),
                        'origin' => 'FORM'
                    ];
                }

                dd(
                    "🔴 ERREUR D'INSCRIPTION - DIAGNOSTIC COMPLET 🔴",
                    [
                        'SOURCE_DU_PROBLEME' => $this->determineErrorSource($entityDiagnostic, $entityErrors, $formErrors),
                        'ETAT_ENTITE' => [
                            'email' => $user->getEmail() ?? 'NULL',
                            'plainPassword' => method_exists($user, 'getPlainPassword') 
                                ? ($user->getPlainPassword() ?? 'NULL') 
                                : 'GETTER_MANQUANT'
                        ],
                        'DIAGNOSTIC_ENTITE' => $entityDiagnostic,
                        'ERREURS_ENTITE' => $entityErrors,
                        'ERREURS_FORMULAIRE' => $formErrors,
                        'DONNEES_RECUES' => $request->request->all()['registration'] ?? []
                    ]
                );
            }
        }

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPassword($hashedPassword);
                
                $entityManager->persist($user);
                $entityManager->flush();

                dd(
                    "🟢 INSCRIPTION REUSSIE 🟢",
                    [
                        'UTILISATEUR_CREE' => [
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'roles' => $user->getRoles()
                        ],
                        'ETAT_SYSTEME' => 'Tous les contrôles sont valides'
                    ]
                );

            } catch (\Exception $e) {
                dd(
                    "🔴 ERREUR CRITIQUE 🔴",
                    [
                        'TYPE' => 'ERREUR_BDD',
                        'MESSAGE' => $e->getMessage(),
                        'TRACE' => $e->getTraceAsString(),
                        'ETAT_USER' => [
                            'email' => $user->getEmail(),
                            'password' => $user->getPassword()
                        ]
                    ]
                );
            }
        }

        return $this->render('front/auth/index.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'loginForm' => $this->createForm(LoginType::class)->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    private function determineErrorSource(array $entityDiag, array $entityErrors, array $formErrors): string
    {
        if (!$entityDiag['email_defined'] || !$entityDiag['plainPassword_defined']) {
            return 'ENTITE_INCOMPLETE (propriétés manquantes dans User.php)';
        }

        if (!$entityDiag['email_getter'] || !$entityDiag['plainPassword_getter']) {
            return 'ENTITE_INCOMPLETE (getters/setters manquants dans User.php)';
        }

        if (!empty($entityErrors)) {
            return 'VALIDATION_ENTITE (contraintes violées dans User.php)';
        }

        if (!empty($formErrors)) {
            return 'VALIDATION_FORMULAIRE (contraintes dans RegistrationType.php)';
        }

        return 'SOURCE_INCONNUE';
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode doit être interceptée par le pare-feu de déconnexion de Symfony.');
    }
}
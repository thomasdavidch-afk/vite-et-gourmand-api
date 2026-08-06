<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Sécurité & Compte')]
class SecurityController extends AbstractController
{
    /**
     * Inscription d'un nouvel utilisateur
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/register',
        summary: 'Inscription d\'un nouvel utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'MotDePasse123!'),
                    new OA\Property(property: 'prenom', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'telephone', type: 'string', example: '0612345678'),
                    new OA\Property(property: 'ville', type: 'string', example: 'Paris'),
                    new OA\Property(property: 'pays', type: 'string', example: 'France'),
                    new OA\Property(property: 'adressePostale', type: 'string', example: '10 rue de la Paix')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Utilisateur créé avec succès'),
            new OA\Response(response: 400, description: 'Champs obligatoires manquants'),
            new OA\Response(response: 409, description: 'Un compte existe déjà avec cet email')
        ]
    )]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Champs obligatoires manquants (email, mot de passe).'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de l'unicité de l'email
        $existingUser = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Un compte existe déjà avec cet email.'], Response::HTTP_CONFLICT);
        }

        $user = new Utilisateur();
        $user->setEmail($email);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Champs optionnels
        if (isset($data['prenom'])) $user->setPrenom($data['prenom']);
        if (isset($data['telephone'])) $user->setTelephone($data['telephone']);
        if (isset($data['ville'])) $user->setVille($data['ville']);
        if (isset($data['pays'])) $user->setPays($data['pays']);
        if (isset($data['adressePostale'])) $user->setAdressePostale($data['adressePostale']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'Utilisateur créé avec succès.',
            'utilisateurId' => $user->getUtilisateurId(),
            'email' => $user->getEmail()
        ], Response::HTTP_CREATED);
    }

    /**
     * Connexion (Génération du token d'accès)
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Connexion (Génération du token d\'accès)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'MotDePasse123!')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie avec retour du token'),
            new OA\Response(response: 400, description: 'Email et mot de passe requis'),
            new OA\Response(response: 401, description: 'Identifiants invalides')
        ]
    )]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email et mot de passe requis.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants invalides.'], Response::HTTP_UNAUTHORIZED);
        }

        // Génération d'un token aléatoire de 64 caractères
        $token = bin2hex(random_bytes(32));
        $user->setApiToken($token);
        $em->flush();

        return new JsonResponse([
            'token' => $token,
            'utilisateurId' => $user->getUtilisateurId(),
            'email' => $user->getEmail(),
            'prenom' => $user->getPrenom(),
            'roles' => $user->getRoles()
        ]);
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     */
    #[Route('/account/me', name: 'me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/account/me',
        summary: 'Récupérer les informations de l\'utilisateur connecté',
        security: [['ApiKeyAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Informations de l\'utilisateur'),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié')
        ]
    )]
    #[OA\Security(name: 'X-AUTH-TOKEN')] // <--- Indique à Swagger le cadenas de sécurité
    public function me(): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'utilisateurId' => $user->getUtilisateurId(),
            'email' => $user->getEmail(),
            'prenom' => $user->getPrenom(),
            'telephone' => $user->getTelephone(),
            'ville' => $user->getVille(),
            'pays' => $user->getPays(),
            'adressePostale' => $user->getAdressePostale(),
            'roles' => $user->getRoles()
        ]);
    }

    /**
     * Modifier les informations du compte de l'utilisateur connecté
     */
    #[Route('/account/edit', name: 'edit_profile', methods: ['PUT', 'PATCH'])]
    #[OA\Put(
        path: '/api/account/edit',
        summary: 'Modifier les informations du compte de l\'utilisateur connecté',
        security: [['ApiKeyAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'nouveau.email@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'NouveauMotDePasse123!'),
                    new OA\Property(property: 'prenom', type: 'string', example: 'JeanModifie'),
                    new OA\Property(property: 'telephone', type: 'string', example: '0698765432'),
                    new OA\Property(property: 'ville', type: 'string', example: 'Lyon'),
                    new OA\Property(property: 'pays', type: 'string', example: 'France'),
                    new OA\Property(property: 'adressePostale', type: 'string', example: '5 rue de la République')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profil mis à jour avec succès'),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié'),
            new OA\Response(response: 409, description: 'Cet email est déjà utilisé')
        ]
    )]
    #[OA\Security(name: 'X-AUTH-TOKEN')] // <--- Indique à Swagger le cadenas de sécurité
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        // Modification optionnelle de l'email
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }

        // Modification optionnelle du mot de passe
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        // Modification des autres champs personnels
        if (array_key_exists('prenom', $data)) $user->setPrenom($data['prenom']);
        if (array_key_exists('telephone', $data)) $user->setTelephone($data['telephone']);
        if (array_key_exists('ville', $data)) $user->setVille($data['ville']);
        if (array_key_exists('pays', $data)) $user->setPays($data['pays']);
        if (array_key_exists('adressePostale', $data)) $user->setAdressePostale($data['adressePostale']);

        $em->flush();

        return new JsonResponse([
            'message' => 'Profil mis à jour avec succès.',
            'user' => [
                'utilisateurId' => $user->getUtilisateurId(),
                'email' => $user->getEmail(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'ville' => $user->getVille(),
                'pays' => $user->getPays(),
                'adressePostale' => $user->getAdressePostale()
            ]
        ]);
    }
}
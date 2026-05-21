<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/api/utilisateurs/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 1️⃣ Récupérer les données JSON
        $data = json_decode($request->getContent(), true);

        // 2️⃣ Vérifier les champs obligatoires
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'error' => 'Email et mot de passe obligatoires.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // 3️⃣ Trouver l'utilisateur par email
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);

        if (!$utilisateur) {
            return $this->json([
                'error' => 'Email ou mot de passe incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 4️⃣ Vérifier le mot de passe en clair
        if ($utilisateur->getPassword() !== $data['password']) {
            return $this->json([
                'error' => 'Email ou mot de passe incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 5️⃣ Retourner les infos utilisateur
        return $this->json([
            'id'     => $utilisateur->getUtilisateurId(),
            'email'  => $utilisateur->getEmail(),
            'prenom' => $utilisateur->getPrenom(),
            'role'   => $utilisateur->getRoles()[0] ?? 'ROLE_USER',
            'token'  => 'token_' . $utilisateur->getUtilisateurId(),
        ], Response::HTTP_OK);
    }
}

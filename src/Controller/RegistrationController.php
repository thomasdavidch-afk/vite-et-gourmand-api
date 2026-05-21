<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/utilisateurs', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        // 1️⃣ Récupérer les données JSON
        $data = json_decode($request->getContent(), true);

        // 2️⃣ Vérifier que les champs obligatoires sont présents
        $requiredFields = ['email', 'password', 'prenom', 'adressePostale', 'ville', 'pays', 'telephone'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field] ?? null)) {
                return $this->json([
                    'error' => "Le champ '$field' est obligatoire."
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // 3️⃣ Vérifier que l'email n'existe pas déjà
        $existingUser = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json([
                'error' => 'Un compte avec cet email existe déjà.'
            ], Response::HTTP_CONFLICT);
        }

        // 4️⃣ Créer un nouvel utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail($data['email']);
        $utilisateur->setPrenom($data['prenom']);
        $utilisateur->setAdressePostale($data['adressePostale']);
        $utilisateur->setVille($data['ville']);
        $utilisateur->setPays($data['pays']);
        $utilisateur->setTelephone($data['telephone']);

        // 5️⃣ Hasher le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $data['password']);
        $utilisateur->setPassword($hashedPassword);

        // 6️⃣ Valider l'entité (optionnel mais recommandé)
        $errors = $validator->validate($utilisateur);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'error' => 'Erreurs de validation',
                'details' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        // 7️⃣ Sauvegarder en base de données
        try {
            $em->persist($utilisateur);
            $em->flush();
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'enregistrement en base de données.',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 8️⃣ Retourner une réponse de succès
        return $this->json([
            'id' => $utilisateur->getUtilisateurId(),
            'email' => $utilisateur->getEmail(),
            'prenom' => $utilisateur->getPrenom(),
            'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.'
        ], Response::HTTP_CREATED);
    }
}

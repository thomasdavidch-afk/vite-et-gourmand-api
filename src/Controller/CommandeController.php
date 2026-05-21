<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/api/commandes', name: 'create_commande', methods: ['POST'])]
    public function createCommande(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $commande = new Commande();
        // Le numeroCommande est généré automatiquement dans le constructeur

        // Statut par défaut
        $commande->setStatut('en attente');

        // Date de commande automatique
        $commande->setDateCommande(new \DateTime());

        // Date de prestation
        if (isset($data['datePrestation'])) {
            $commande->setDatePrestation(new \DateTime($data['datePrestation']));
        }

        // Heure de livraison
        if (isset($data['heureLivraison'])) {
            $commande->setHeureLivraison($data['heureLivraison']);
        }

        // Nombre de personnes
        if (isset($data['nombrePersonne'])) {
            $commande->setNombrePersonne($data['nombrePersonne']);
        }

        // Prix menu
        if (isset($data['prixMenu'])) {
            $commande->setPrixMenu($data['prixMenu']);
        }

        // Prix livraison
        if (isset($data['prixLivraison'])) {
            $commande->setPrixLivraison($data['prixLivraison']);
        }

        // Prêt matériel
        if (isset($data['pretMateriel'])) {
            $commande->setPretMateriel($data['pretMateriel']);
        }

        // Restitution matériel
        if (isset($data['restitutionMateriel'])) {
            $commande->setRestitutionMateriel($data['restitutionMateriel']);
        }

        // Lier un menu si fourni
        if (isset($data['menuId'])) {
            $menu = $em->getRepository(Menu::class)->find($data['menuId']);
            if ($menu) {
                $commande->setMenu($menu);
            } else {
                return $this->json(['error' => 'Menu introuvable'], 404);
            }
        }

        // Lier un utilisateur si fourni
        if (isset($data['utilisateurId'])) {
            $utilisateur = $em->getRepository(Utilisateur::class)->find($data['utilisateurId']);
            if ($utilisateur) {
                $commande->setUtilisateur($utilisateur);
            } else {
                return $this->json(['error' => 'Utilisateur introuvable'], 404);
            }
        }

        $em->persist($commande);
        $em->flush();

        return $this->json([
            'message'        => 'Commande créée avec succès',
            'numeroCommande' => $commande->getNumeroCommande(),
            'statut'         => $commande->getStatut(),
            'dateCommande'   => $commande->getDateCommande()->format('Y-m-d'),
        ], 201);
    }
}

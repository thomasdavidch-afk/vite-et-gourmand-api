<?php

namespace App\Controller;

use App\Entity\Allergene;
use App\Entity\Plat;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/plats')]
#[OA\Tag(name: 'Plats')]
class PlatController extends AbstractController
{
    /**
     * Obtenir la liste de tous les plats
     */
    #[Route('', name: 'plat_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/plats',
        summary: 'Obtenir la liste de tous les plats',
        responses: [
            new OA\Response(response: 200, description: 'Liste des plats récupérée avec succès')
        ]
    )]
    public function index(PlatRepository $platRepository): JsonResponse
    {
        $plats = $platRepository->findAll();

        $data = array_map(function (Plat $plat) {
            return $this->serializePlat($plat);
        }, $plats);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Obtenir les détails d'un plat par son ID
     */
    #[Route('/{id}', name: 'plat_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/plats/{id}',
        summary: 'Obtenir les détails d\'un plat',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du plat'),
            new OA\Response(response: 404, description: 'Plat non trouvé')
        ]
    )]
    public function show(int $id, PlatRepository $platRepository): JsonResponse
    {
        $plat = $platRepository->find($id);

        if (!$plat) {
            return new JsonResponse(['error' => 'Plat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializePlat($plat), Response::HTTP_OK);
    }

    /**
     * Créer un nouveau plat (Admin uniquement)
     */
    #[Route('', name: 'plat_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut ajouter un plat.')]
    #[OA\Post(
        path: '/api/plats',
        summary: 'Créer un nouveau plat (Admin uniquement)',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['titrePlat'],
                properties: [
                    new OA\Property(property: 'titrePlat', type: 'string', example: 'Carpaccio de Saint-Jacques'),
                    new OA\Property(property: 'allergeneIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Plat créé avec succès'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès refusé')
        ]
    )]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['titrePlat'])) {
            return new JsonResponse(['error' => 'Le titre du plat est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $plat = new Plat();
        $plat->setTitrePlat($data['titrePlat']);

        // Association des allergènes si fournis
        if (!empty($data['allergeneIds']) && is_array($data['allergeneIds'])) {
            $allergeneRepo = $em->getRepository(Allergene::class);
            foreach ($data['allergeneIds'] as $allergeneId) {
                $allergene = $allergeneRepo->find($allergeneId);
                if ($allergene) {
                    $plat->addAllergene($allergene);
                }
            }
        }

        $em->persist($plat);
        $em->flush();

        return new JsonResponse([
            'message' => 'Plat créé avec succès.',
            'plat' => $this->serializePlat($plat)
        ], Response::HTTP_CREATED);
    }

    /**
     * Modifier un plat (Admin uniquement)
     */
    #[Route('/{id}', name: 'plat_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut modifier un plat.')]
    #[OA\Put(
        path: '/api/plats/{id}',
        summary: 'Modifier un plat (Admin uniquement)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titrePlat', type: 'string', example: 'Tartare de Saumon'),
                    new OA\Property(property: 'allergeneIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Plat mis à jour avec succès'),
            new OA\Response(response: 404, description: 'Plat non trouvé')
        ]
    )]
    public function update(int $id, Request $request, PlatRepository $platRepository, EntityManagerInterface $em): JsonResponse
    {
        $plat = $platRepository->find($id);

        if (!$plat) {
            return new JsonResponse(['error' => 'Plat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['titrePlat'])) {
            $plat->setTitrePlat($data['titrePlat']);
        }

        // Mise à jour des allergènes si transmis
        if (isset($data['allergeneIds']) && is_array($data['allergeneIds'])) {
            // Nettoyage des allergènes existants
            foreach ($plat->getAllergenes() as $allergene) {
                $plat->removeAllergene($allergene);
            }

            // Ajout des nouveaux allergènes
            $allergeneRepo = $em->getRepository(Allergene::class);
            foreach ($data['allergeneIds'] as $allergeneId) {
                $allergene = $allergeneRepo->find($allergeneId);
                if ($allergene) {
                    $plat->addAllergene($allergene);
                }
            }
        }

        $em->flush();

        return new JsonResponse([
            'message' => 'Plat mis à jour avec succès.',
            'plat' => $this->serializePlat($plat)
        ], Response::HTTP_OK);
    }

    /**
     * Supprimer un plat (Admin uniquement)
     */
    #[Route('/{id}', name: 'plat_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut supprimer un plat.')]
    #[OA\Delete(
        path: '/api/plats/{id}',
        summary: 'Supprimer un plat (Admin uniquement)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plat supprimé avec succès'),
            new OA\Response(response: 404, description: 'Plat non trouvé')
        ]
    )]
    public function delete(int $id, PlatRepository $platRepository, EntityManagerInterface $em): JsonResponse
    {
        $plat = $platRepository->find($id);

        if (!$plat) {
            return new JsonResponse(['error' => 'Plat non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($plat);
        $em->flush();

        return new JsonResponse(['message' => 'Plat supprimé avec succès.'], Response::HTTP_OK);
    }

    /**
     * Sérialisation de l'objet Plat en tableau JSON
     */
    private function serializePlat(Plat $plat): array
    {
        return [
            'platId' => $plat->getPlatId(),
            'titrePlat' => $plat->getTitrePlat(),
            'photo' => $plat->getPhoto() ? base64_encode(is_resource($plat->getPhoto()) ? stream_get_contents($plat->getPhoto()) : $plat->getPhoto()) : null,
            'allergenes' => array_map(function ($allergene) {
                return [
                    'allergeneId' => method_exists($allergene, 'getAllergeneId') ? $allergene->getAllergeneId() : null,
                    'nom' => method_exists($allergene, 'getNom') ? $allergene->getNom() : null,
                ];
            }, $plat->getAllergenes()->toArray()),
        ];
    }
}
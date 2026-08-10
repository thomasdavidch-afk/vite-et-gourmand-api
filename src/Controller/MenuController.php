<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\ImageRepository;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
use App\Repository\RegimeRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/menus')]
#[OA\Tag(name: 'Menus')]
class MenuController extends AbstractController
{
    /**
     * Liste de tous les menus
     */
    #[Route('', name: 'menu_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus',
        summary: 'Obtenir la liste de tous les menus',
        responses: [
            new OA\Response(response: 200, description: 'Liste des menus récupérée avec succès')
        ]
    )]
    public function index(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findAll();

        $data = array_map(fn(Menu $menu) => $this->serializeMenu($menu), $menus);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Détail d'un menu par son ID
     */
    #[Route('/{id}', name: 'menu_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus/{id}',
        summary: 'Obtenir les détails d\'un menu',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menu trouvé'),
            new OA\Response(response: 404, description: 'Menu non trouvé')
        ]
    )]
    public function show(int $id, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeMenu($menu), Response::HTTP_OK);
    }

    /**
     * Créer un ou plusieurs menus (Admin uniquement)
     */
    #[Route('', name: 'menu_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut ajouter un menu.')]
    #[OA\Post(
        path: '/api/menus',
        summary: 'Créer un ou plusieurs menus (Admin uniquement)',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: 'object',
                oneOf: [
                    new OA\Schema(
                        properties: [
                            new OA\Property(property: 'titre', type: 'string', example: 'Menu Gastronomique'),
                            new OA\Property(property: 'nombrePersonneMinimum', type: 'integer', example: 2),
                            new OA\Property(property: 'prixParPersonne', type: 'number', format: 'float', example: 45.50),
                            new OA\Property(property: 'description', type: 'string', example: 'Un délicieux menu de saison'),
                            new OA\Property(property: 'quantiteRestante', type: 'integer', example: 20),
                            new OA\Property(property: 'regimeIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                            new OA\Property(property: 'themeIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1]),
                            new OA\Property(property: 'platIds', type: 'array', items: new OA\Items(type: 'integer'), example: [3, 4, 5]),
                            new OA\Property(property: 'imageIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                        ]
                    ),
                    new OA\Schema(
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'titre', type: 'string', example: 'Menu Végétarien'),
                                new OA\Property(property: 'nombrePersonneMinimum', type: 'integer', example: 1),
                                new OA\Property(property: 'prixParPersonne', type: 'number', format: 'float', example: 35.00),
                                new OA\Property(property: 'description', type: 'string', example: 'Menu 100% végétal'),
                                new OA\Property(property: 'quantiteRestante', type: 'integer', example: 15)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Menu(s) créé(s) avec succès'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 403, description: 'Accès refusé')
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        RegimeRepository $regimeRepository,
        ThemeRepository $themeRepository,
        PlatRepository $platRepository,
        ImageRepository $imageRepository
    ): JsonResponse {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if ($content !== '' && json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Format JSON invalide : ' . json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data)) {
            return new JsonResponse(['error' => 'Le corps de la requête ne peut pas être vide.'], Response::HTTP_BAD_REQUEST);
        }

        // Si la requête contient une liste (tableau numérique) -> Ajout multiple
        if (array_is_list($data)) {
            $createdMenus = [];

            foreach ($data as $index => $itemData) {
                if (empty($itemData['titre']) || !isset($itemData['prixParPersonne'])) {
                    return new JsonResponse([
                        'error' => sprintf('Élément à l\'index %d invalide : Le titre et le prix par personne sont obligatoires.', $index)
                    ], Response::HTTP_BAD_REQUEST);
                }

                $menu = $this->buildMenuFromData($itemData, $regimeRepository, $themeRepository, $platRepository, $imageRepository);
                $em->persist($menu);
                $createdMenus[] = $menu;
            }

            $em->flush();

            return new JsonResponse([
                'message' => count($createdMenus) . ' menu(s) créé(s) avec succès.',
                'menus' => array_map(fn(Menu $m) => $this->serializeMenu($m), $createdMenus)
            ], Response::HTTP_CREATED);
        }

        // Sinon -> Ajout simple d'un seul menu
        if (empty($data['titre']) || !isset($data['prixParPersonne'])) {
            return new JsonResponse(['error' => 'Le titre et le prix par personne sont obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        $menu = $this->buildMenuFromData($data, $regimeRepository, $themeRepository, $platRepository, $imageRepository);
        $em->persist($menu);
        $em->flush();

        return new JsonResponse([
            'message' => 'Menu créé avec succès.',
            'menu' => $this->serializeMenu($menu)
        ], Response::HTTP_CREATED);
    }

    /**
     * Modifier un menu (Admin uniquement)
     */
    #[Route('/{id}', name: 'menu_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut modifier un menu.')]
    #[OA\Put(
        path: '/api/menus/{id}',
        summary: 'Modifier un menu existant (Admin uniquement)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titre', type: 'string', example: 'Menu Gourmand'),
                    new OA\Property(property: 'nombrePersonneMinimum', type: 'integer', example: 4),
                    new OA\Property(property: 'prixParPersonne', type: 'number', format: 'float', example: 50.00),
                    new OA\Property(property: 'description', type: 'string', example: 'Nouvelle description'),
                    new OA\Property(property: 'quantiteRestante', type: 'integer', example: 15),
                    new OA\Property(property: 'regimeIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1]),
                    new OA\Property(property: 'themeIds', type: 'array', items: new OA\Items(type: 'integer'), example: [2]),
                    new OA\Property(property: 'platIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                    new OA\Property(property: 'imageIds', type: 'array', items: new OA\Items(type: 'integer'), example: [1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Menu mis à jour avec succès'),
            new OA\Response(response: 404, description: 'Menu non trouvé'),
            new OA\Response(response: 403, description: 'Accès refusé')
        ]
    )]
    public function update(
        int $id,
        Request $request,
        MenuRepository $menuRepository,
        RegimeRepository $regimeRepository,
        ThemeRepository $themeRepository,
        PlatRepository $platRepository,
        ImageRepository $imageRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $content = $request->getContent();
        $data = json_decode($content, true);

        if ($content !== '' && json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Format JSON invalide : ' . json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $data = $data ?? [];

        if (array_key_exists('titre', $data)) $menu->setTitre($data['titre']);
        if (array_key_exists('nombrePersonneMinimum', $data)) $menu->setNombrePersonneMinimum((int) $data['nombrePersonneMinimum']);
        if (array_key_exists('prixParPersonne', $data)) $menu->setPrixParPersonne((float) $data['prixParPersonne']);
        if (array_key_exists('description', $data)) $menu->setDescription($data['description']);
        if (array_key_exists('quantiteRestante', $data)) $menu->setQuantiteRestante((int) $data['quantiteRestante']);

        // Mise à jour des relations ManyToMany
        if (array_key_exists('regimeIds', $data) && is_array($data['regimeIds'])) {
            foreach ($menu->getRegimes() as $r) { $menu->removeRegime($r); }
            foreach ($data['regimeIds'] as $rId) {
                $r = $regimeRepository->find($rId);
                if ($r) $menu->addRegime($r);
            }
        }

        if (array_key_exists('themeIds', $data) && is_array($data['themeIds'])) {
            foreach ($menu->getThemes() as $t) { $menu->removeTheme($t); }
            foreach ($data['themeIds'] as $tId) {
                $t = $themeRepository->find($tId);
                if ($t) $menu->addTheme($t);
            }
        }

        if (array_key_exists('platIds', $data) && is_array($data['platIds'])) {
            foreach ($menu->getPlats() as $p) { $menu->removePlat($p); }
            foreach ($data['platIds'] as $pId) {
                $p = $platRepository->find($pId);
                if ($p) $menu->addPlat($p);
            }
        }

        if (array_key_exists('imageIds', $data) && is_array($data['imageIds'])) {
            foreach ($menu->getImages() as $img) { $menu->removeImage($img); }
            foreach ($data['imageIds'] as $imgId) {
                $img = $imageRepository->find($imgId);
                if ($img) $menu->addImage($img);
            }
        }

        $em->flush();

        return new JsonResponse([
            'message' => 'Menu mis à jour avec succès.',
            'menu' => $this->serializeMenu($menu)
        ], Response::HTTP_OK);
    }

    /**
     * Supprimer un menu (Admin uniquement)
     */
    #[Route('/{id}', name: 'menu_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès refusé. Seul un administrateur peut supprimer un menu.')]
    #[OA\Delete(
        path: '/api/menus/{id}',
        summary: 'Supprimer un menu (Admin uniquement)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menu supprimé avec succès'),
            new OA\Response(response: 404, description: 'Menu non trouvé'),
            new OA\Response(response: 403, description: 'Accès refusé')
        ]
    )]
    public function delete(int $id, MenuRepository $menuRepository, EntityManagerInterface $em): JsonResponse
    {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return new JsonResponse(['error' => 'Menu non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($menu);
        $em->flush();

        return new JsonResponse(['message' => 'Menu supprimé avec succès.'], Response::HTTP_OK);
    }

    /**
     * Construit un objet Menu à partir des données reçues
     */
    private function buildMenuFromData(
        array $data,
        RegimeRepository $regimeRepository,
        ThemeRepository $themeRepository,
        PlatRepository $platRepository,
        ImageRepository $imageRepository
    ): Menu {
        $menu = new Menu();
        $menu->setTitre($data['titre']);
        $menu->setNombrePersonneMinimum($data['nombrePersonneMinimum'] ?? 1);
        $menu->setPrixParPersonne((float) $data['prixParPersonne']);
        $menu->setDescription($data['description'] ?? null);
        $menu->setQuantiteRestante($data['quantiteRestante'] ?? 0);

        if (!empty($data['regimeIds']) && is_array($data['regimeIds'])) {
            foreach ($data['regimeIds'] as $regimeId) {
                $regime = $regimeRepository->find($regimeId);
                if ($regime) $menu->addRegime($regime);
            }
        }

        if (!empty($data['themeIds']) && is_array($data['themeIds'])) {
            foreach ($data['themeIds'] as $themeId) {
                $theme = $themeRepository->find($themeId);
                if ($theme) $menu->addTheme($theme);
            }
        }

        if (!empty($data['platIds']) && is_array($data['platIds'])) {
            foreach ($data['platIds'] as $platId) {
                $plat = $platRepository->find($platId);
                if ($plat) $menu->addPlat($plat);
            }
        }

        if (!empty($data['imageIds']) && is_array($data['imageIds'])) {
            foreach ($data['imageIds'] as $imageId) {
                $image = $imageRepository->find($imageId);
                if ($image) $menu->addImage($image);
            }
        }

        return $menu;
    }

    /**
     * Transforme l'objet Menu en tableau JSON incluant les relations
     */
    private function serializeMenu(Menu $menu): array
    {
        return [
            'menuId' => $menu->getMenuId(),
            'titre' => $menu->getTitre(),
            'nombrePersonneMinimum' => $menu->getNombrePersonneMinimum(),
            'prixParPersonne' => $menu->getPrixParPersonne(),
            'description' => $menu->getDescription(),
            'quantiteRestante' => $menu->getQuantiteRestante(),
            'regimes' => array_map(fn($r) => [
                'regimeId' => $r->getRegimeId(),
                'nom' => method_exists($r, 'getNom') ? $r->getNom() : (method_exists($r, 'getLibelle') ? $r->getLibelle() : null)
            ], $menu->getRegimes()->toArray()),
            'themes' => array_map(fn($t) => [
                'themeId' => $t->getThemeId(),
                'nom' => method_exists($t, 'getNom') ? $t->getNom() : (method_exists($t, 'getLibelle') ? $t->getLibelle() : null)
            ], $menu->getThemes()->toArray()),
            'plats' => array_map(fn($p) => [
                'platId' => $p->getPlatId(),
                'nom' => method_exists($p, 'getNom') ? $p->getNom() : (method_exists($p, 'getTitre') ? $p->getTitre() : null)
            ], $menu->getPlats()->toArray()),
            'images' => array_map(fn($i) => [
                'imageId' => $i->getImageId(),
                'path' => method_exists($i, 'getPath') ? $i->getPath() : (method_exists($i, 'getUrl') ? $i->getUrl() : null)
            ], method_exists($menu, 'getImages') ? $menu->getImages()->toArray() : []),
        ];
    }
}
<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menu')]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'menu_id', type: 'integer')]
    private ?int $menuId = null;

    #[ORM\Column(name: 'titre', type: 'string', length: 50, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(name: 'nombre_personne_minimum', type: 'integer', nullable: true)]
    private ?int $nombrePersonneMinimum = null;

    #[ORM\Column(name: 'prix_par_personne', type: 'float', nullable: true)]
    private ?float $prixParPersonne = null;

    #[ORM\Column(name: 'regime', type: 'string', length: 50, nullable: true)]
    private ?string $regime = null;

    #[ORM\Column(name: 'description', type: 'string', length: 50, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'quantite_restante', type: 'integer', nullable: true)]
    private ?int $quantiteRestante = null;

    #[ORM\ManyToMany(targetEntity: Regime::class, inversedBy: 'menus')]
    #[ORM\JoinTable(
        name: 'adapte',
        joinColumns: [new ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'regime_id', referencedColumnName: 'regime_id')]
    )]
    private Collection $regimes;

    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'menus')]
    #[ORM\JoinTable(
        name: 'propose',
        joinColumns: [new ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'theme_id')]
    )]
    private Collection $themes;

    #[ORM\ManyToMany(targetEntity: Plat::class, inversedBy: 'menus')]
    #[ORM\JoinTable(
        name: 'propose_plat',
        joinColumns: [new ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'menu_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'plat_id', referencedColumnName: 'plat_id')]
    )]
    private Collection $plats;

    public function __construct()
    {
        $this->regimes = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->plats = new ArrayCollection();
    }

    public function getMenuId(): ?int { return $this->menuId; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(?string $titre): self { $this->titre = $titre; return $this; }

    public function getNombrePersonneMinimum(): ?int { return $this->nombrePersonneMinimum; }
    public function setNombrePersonneMinimum(?int $n): self { $this->nombrePersonneMinimum = $n; return $this; }

    public function getPrixParPersonne(): ?float { return $this->prixParPersonne; }
    public function setPrixParPersonne(?float $p): self { $this->prixParPersonne = $p; return $this; }

    public function getRegime(): ?string { return $this->regime; }
    public function setRegime(?string $regime): self { $this->regime = $regime; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getQuantiteRestante(): ?int { return $this->quantiteRestante; }
    public function setQuantiteRestante(?int $q): self { $this->quantiteRestante = $q; return $this; }

    public function getRegimes(): Collection { return $this->regimes; }
    public function addRegime(Regime $regime): self { if (!$this->regimes->contains($regime)) { $this->regimes[] = $regime; } return $this; }
    public function removeRegime(Regime $regime): self { $this->regimes->removeElement($regime); return $this; }

    public function getThemes(): Collection { return $this->themes; }
    public function addTheme(Theme $theme): self { if (!$this->themes->contains($theme)) { $this->themes[] = $theme; } return $this; }
    public function removeTheme(Theme $theme): self { $this->themes->removeElement($theme); return $this; }

    public function getPlats(): Collection { return $this->plats; }
    public function addPlat(Plat $plat): self { if (!$this->plats->contains($plat)) { $this->plats[] = $plat; } return $this; }
    public function removePlat(Plat $plat): self { $this->plats->removeElement($plat); return $this; }
}

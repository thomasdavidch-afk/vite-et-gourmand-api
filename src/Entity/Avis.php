<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'avis_id', type: 'integer')]
    #[Groups(['avis:read', 'avis:write'])]
    private ?int $avisId = null;

    #[ORM\Column(name: 'note', type: 'string', length: 50, nullable: true)]
    #[Groups(['avis:read', 'avis:write'])]
    private ?string $note = null;

    #[ORM\Column(name: 'description', type: 'string', length: 50, nullable: true)]
    #[Groups(['avis:read', 'avis:write'])]
    private ?string $description = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 50, nullable: true)]
    #[Groups(['avis:read', 'avis:write'])]
    private ?string $statut = null;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'avis')]
    private Collection $utilisateurs;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
    }

    public function getAvisId(): ?int { return $this->avisId; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): self { $this->note = $note; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): self { $this->statut = $statut; return $this; }

    public function getUtilisateurs(): Collection { return $this->utilisateurs; }
    public function addUtilisateur(Utilisateur $utilisateur): self { if (!$this->utilisateurs->contains($utilisateur)) { $this->utilisateurs[] = $utilisateur; } return $this; }
    public function removeUtilisateur(Utilisateur $utilisateur): self { $this->utilisateurs->removeElement($utilisateur); return $this; }
}

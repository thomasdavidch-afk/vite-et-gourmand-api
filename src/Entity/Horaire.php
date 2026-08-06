<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
#[ORM\Table(name: 'horaire')]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'horaire_id', type: 'integer')]
    private ?int $horaireId = null;

    #[ORM\Column(name: 'jour', type: 'string', length: 50, nullable: true)]
    private ?string $jour = null;

    #[ORM\Column(name: 'heure_ouverture', type: 'string', length: 50, nullable: true)]
    private ?string $heureOuverture = null;

    #[ORM\Column(name: 'heure_fermeture', type: 'string', length: 50, nullable: true)]
    private ?string $heureFermeture = null;

    public function getHoraireId(): ?int { return $this->horaireId; }

    public function getJour(): ?string { return $this->jour; }
    public function setJour(?string $jour): self { $this->jour = $jour; return $this; }

    public function getHeureOuverture(): ?string { return $this->heureOuverture; }
    public function setHeureOuverture(?string $heureOuverture): self { $this->heureOuverture = $heureOuverture; return $this; }

    public function getHeureFermeture(): ?string { return $this->heureFermeture; }
    public function setHeureFermeture(?string $heureFermeture): self { $this->heureFermeture = $heureFermeture; return $this; }
}

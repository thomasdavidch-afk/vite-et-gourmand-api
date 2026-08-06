<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'utilisateur_id', type: 'integer')]
    #[Groups(['utilisateur:read'])]
    private ?int $utilisateurId = null;

    #[ORM\Column(name: 'email', type: 'string', length: 180, unique: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $email = null;

    // Modifié à 255 caractères pour stocker le hash Symfony
    #[ORM\Column(name: 'password', type: 'string', length: 255)]
    #[Groups(['utilisateur:write'])] // Jamais lu dans utilisateur:read !
    private ?string $password = null;

    #[ORM\Column(name: 'api_token', type: 'string', length: 255, unique: true, nullable: true)]
    #[Groups(['utilisateur:read'])]
    private ?string $apiToken = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 50, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $prenom = null;

    #[ORM\Column(name: 'telephone', type: 'string', length: 50, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $telephone = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 50, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $ville = null;

    #[ORM\Column(name: 'pays', type: 'string', length: 50, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $pays = null;

    #[ORM\Column(name: 'adresse_postale', type: 'string', length: 50, nullable: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?string $adressePostale = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(
        name: 'possede',
        joinColumns: [new ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    )]
    #[Groups(['utilisateur:read'])]
    private Collection $roleEntities;

    #[ORM\ManyToMany(targetEntity: Avis::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(
        name: 'publie',
        joinColumns: [new ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'utilisateur_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'avis_id', referencedColumnName: 'avis_id')]
    )]
    #[Groups(['utilisateur:read'])]
    private Collection $avis;

    public function __construct()
    {
        $this->roleEntities = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;
        return $this;
    }

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(?string $adressePostale): self
    {
        $this->adressePostale = $adressePostale;
        return $this;
    }

    public function getRoleEntities(): Collection
    {
        return $this->roleEntities;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roleEntities->contains($role)) {
            $this->roleEntities[] = $role;
        }
        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roleEntities->removeElement($role);
        return $this;
    }

    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
        }
        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        $this->avis->removeElement($avi);
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retourne la liste des rôles de l'utilisateur sous forme de tableau de chaînes.
     * Garantit que ROLE_USER est toujours inclus et que les libellés sont au bon format.
     */
    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->roleEntities as $role) {
            $libelle = strtoupper((string) $role->getLibelle());
            // Ajoute 'ROLE_' si ce n'est pas déjà présent dans votre table Role
            if (!str_starts_with($libelle, 'ROLE_')) {
                $libelle = 'ROLE_' . $libelle;
            }
            $roles[] = $libelle;
        }

        // Tout utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles, effacez-les ici
    }
}
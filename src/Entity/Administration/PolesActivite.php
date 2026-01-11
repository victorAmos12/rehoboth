<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * Entité pour les pôles d'activité hospitaliers
 * 
 * Un pôle regroupe plusieurs services par type de pathologies ou fonctions
 * Exemple: Pôle Cardio (Cardiologie + Chirurgie Cardiaque)
 */
#[ORM\Entity]
#[ORM\Table(name: 'poles_activite', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_responsable', columns: ["responsable_id"])
    ])]
class PolesActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 100)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $typePole = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $budgetAnnuel = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = true;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Personnel\Utilisateurs')]
    #[ORM\JoinColumn(name: 'responsable_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateurs $responsableId = null;

    #[ORM\ManyToOne(targetEntity: TypesPoles::class, inversedBy: 'poles')]
    #[ORM\JoinColumn(name: 'type_pole_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TypesPoles $typePoleId = null;

    #[ORM\OneToMany(targetEntity: Services::class, mappedBy: 'poleId')]
    private Collection $services;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->services = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTypePole(): ?string
    {
        return $this->typePole;
    }

    public function setTypePole(?string $typePole): static
    {
        $this->typePole = $typePole;
        return $this;
    }

    public function getBudgetAnnuel(): ?string
    {
        return $this->budgetAnnuel;
    }

    public function setBudgetAnnuel(?string $budgetAnnuel): static
    {
        $this->budgetAnnuel = $budgetAnnuel;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getResponsableId(): ?Utilisateurs
    {
        return $this->responsableId;
    }

    public function setResponsableId(?Utilisateurs $responsableId): static
    {
        $this->responsableId = $responsableId;
        return $this;
    }

    public function getTypePoleId(): ?TypesPoles
    {
        return $this->typePoleId;
    }

    public function setTypePoleId(?TypesPoles $typePoleId): static
    {
        $this->typePoleId = $typePoleId;
        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setPoleId($this);
        }
        return $this;
    }

    public function removeService(Services $service): static
    {
        if ($this->services->removeElement($service)) {
            if ($service->getPoleId() === $this) {
                $service->setPoleId(null);
            }
        }
        return $this;
    }
}

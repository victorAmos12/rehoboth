<?php

namespace App\Entity\Patients;

use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Services;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'chambres', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_service', columns: ["service_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"])
    ])]
class Chambres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroChambre = '';

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $etage = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $nombreLits = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeChambre = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $climatisee = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $sanitairesPrives = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $television = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $telephone = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\OneToMany(targetEntity: Lits::class, mappedBy: 'chambreId')]
    private Collection $lits;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->lits = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroChambre(): string
    {
        return $this->numeroChambre;
    }

    public function setNumeroChambre(string $numeroChambre): static
    {
        $this->numeroChambre = $numeroChambre;
        return $this;
    }

    public function getEtage(): ?int
    {
        return $this->etage;
    }

    public function setEtage(?int $etage): static
    {
        $this->etage = $etage;
        return $this;
    }

    public function getNombreLits(): ?int
    {
        return $this->nombreLits;
    }

    public function setNombreLits(?int $nombreLits): static
    {
        $this->nombreLits = $nombreLits;
        return $this;
    }

    public function getTypeChambre(): ?string
    {
        return $this->typeChambre;
    }

    public function setTypeChambre(?string $typeChambre): static
    {
        $this->typeChambre = $typeChambre;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
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

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function isClimatisee(): ?bool
    {
        return $this->climatisee;
    }

    public function setClimatisee(?bool $climatisee): static
    {
        $this->climatisee = $climatisee;
        return $this;
    }

    public function isSanitairesPrives(): ?bool
    {
        return $this->sanitairesPrives;
    }

    public function setSanitairesPrives(?bool $sanitairesPrives): static
    {
        $this->sanitairesPrives = $sanitairesPrives;
        return $this;
    }

    public function isTelevision(): ?bool
    {
        return $this->television;
    }

    public function setTelevision(?bool $television): static
    {
        $this->television = $television;
        return $this;
    }

    public function isTelephone(): ?bool
    {
        return $this->telephone;
    }

    public function setTelephone(?bool $telephone): static
    {
        $this->telephone = $telephone;
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

    public function getServiceId(): Services
    {
        return $this->serviceId;
    }

    public function setServiceId(Services $serviceId): static
    {
        $this->serviceId = $serviceId;
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

    public function getLits(): Collection
    {
        return $this->lits;
    }

    public function addLit(Lits $lit): static
    {
        if (!$this->lits->contains($lit)) {
            $this->lits->add($lit);
            $lit->setChambreId($this);
        }
        return $this;
    }

    public function removeLit(Lits $lit): static
    {
        if ($this->lits->removeElement($lit)) {
            if ($lit->getChambreId() === $this) {
                $lit->setChambreId(null);
            }
        }
        return $this;
    }
}

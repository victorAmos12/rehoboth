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
#[ORM\Table(name: 'lits', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_service', columns: ["service_id"]),
        new ORM\Index(name: 'idx_chambre', columns: ["chambre_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"])
    ])]
class Lits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroLit = '';

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeLit = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $etage = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDerniereMaintenance = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Chambres::class, inversedBy: 'lits')]
    #[ORM\JoinColumn(name: 'chambre_id', referencedColumnName: 'id', nullable: false)]
    private Chambres $chambreId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroLit(): string
    {
        return $this->numeroLit;
    }

    public function setNumeroLit(string $numeroLit): static
    {
        $this->numeroLit = $numeroLit;
        return $this;
    }

    public function getTypeLit(): ?string
    {
        return $this->typeLit;
    }

    public function setTypeLit(?string $typeLit): static
    {
        $this->typeLit = $typeLit;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateDerniereMaintenance(): ?\DateTimeInterface
    {
        return $this->dateDerniereMaintenance;
    }

    public function setDateDerniereMaintenance(?\DateTimeInterface $dateDerniereMaintenance): static
    {
        $this->dateDerniereMaintenance = $dateDerniereMaintenance;
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

    public function getChambreId(): Chambres
    {
        return $this->chambreId;
    }

    public function setChambreId(Chambres $chambreId): static
    {
        $this->chambreId = $chambreId;
        return $this;
    }
}

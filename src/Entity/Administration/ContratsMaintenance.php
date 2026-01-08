<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'contrats_maintenance', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'equipement_id', columns: ["equipement_id"]),
        new ORM\Index(name: 'fournisseur_id', columns: ["fournisseur_id"]),
        new ORM\Index(name: 'IDX_1323B1D0CC0FBF92', columns: ["hopital_id"])
    ])]
class ContratsMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroContrat = '';

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeMaintenance = null;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $frequenceMaintenance = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $coutMaintenance = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Equipements::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id', nullable: false)]
    private Equipements $equipementId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Fournisseurs::class)]
    #[ORM\JoinColumn(name: 'fournisseur_id', referencedColumnName: 'id', nullable: false)]
    private Fournisseurs $fournisseurId;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: 'devise_id', referencedColumnName: 'id', nullable: false)]
    private Devises $deviseId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroContrat(): string
    {
        return $this->numeroContrat;
    }

    public function setNumeroContrat(string $numeroContrat): static
    {
        $this->numeroContrat = $numeroContrat;
        return $this;
    }

    public function getTypeMaintenance(): ?string
    {
        return $this->typeMaintenance;
    }

    public function setTypeMaintenance(?string $typeMaintenance): static
    {
        $this->typeMaintenance = $typeMaintenance;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getFrequenceMaintenance(): ?string
    {
        return $this->frequenceMaintenance;
    }

    public function setFrequenceMaintenance(?string $frequenceMaintenance): static
    {
        $this->frequenceMaintenance = $frequenceMaintenance;
        return $this;
    }

    public function getCoutMaintenance(): ?string
    {
        return $this->coutMaintenance;
    }

    public function setCoutMaintenance(?string $coutMaintenance): static
    {
        $this->coutMaintenance = $coutMaintenance;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getEquipementId(): Equipements
    {
        return $this->equipementId;
    }

    public function setEquipementId(Equipements $equipementId): static
    {
        $this->equipementId = $equipementId;
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

    public function getFournisseurId(): Fournisseurs
    {
        return $this->fournisseurId;
    }

    public function setFournisseurId(Fournisseurs $fournisseurId): static
    {
        $this->fournisseurId = $fournisseurId;
        return $this;
    }

    public function getDeviseId(): Devises
    {
        return $this->deviseId;
    }

    public function setDeviseId(Devises $deviseId): static
    {
        $this->deviseId = $deviseId;
        return $this;
    }

}

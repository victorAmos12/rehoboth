<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'interventions_maintenance', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_intervention"]),
        new ORM\Index(name: 'idx_equipement', columns: ["equipement_id"]),
        new ORM\Index(name: 'technicien_id', columns: ["technicien_id"]),
        new ORM\Index(name: 'IDX_5D831C8BCC0FBF92', columns: ["hopital_id"])
    ])]
class InterventionsMaintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroIntervention = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateIntervention;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeIntervention = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $descriptionIntervention = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $piecesRemplacees = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeIntervention = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $coutIntervention = null;

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

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'technicien_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $technicienId;

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

    public function getNumeroIntervention(): string
    {
        return $this->numeroIntervention;
    }

    public function setNumeroIntervention(string $numeroIntervention): static
    {
        $this->numeroIntervention = $numeroIntervention;
        return $this;
    }

    public function getDateIntervention(): \DateTimeInterface
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(\DateTimeInterface $dateIntervention): static
    {
        $this->dateIntervention = $dateIntervention;
        return $this;
    }

    public function getTypeIntervention(): ?string
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(?string $typeIntervention): static
    {
        $this->typeIntervention = $typeIntervention;
        return $this;
    }

    public function getDescriptionIntervention(): ?string
    {
        return $this->descriptionIntervention;
    }

    public function setDescriptionIntervention(?string $descriptionIntervention): static
    {
        $this->descriptionIntervention = $descriptionIntervention;
        return $this;
    }

    public function getPiecesRemplacees(): ?string
    {
        return $this->piecesRemplacees;
    }

    public function setPiecesRemplacees(?string $piecesRemplacees): static
    {
        $this->piecesRemplacees = $piecesRemplacees;
        return $this;
    }

    public function getDureeIntervention(): ?int
    {
        return $this->dureeIntervention;
    }

    public function setDureeIntervention(?int $dureeIntervention): static
    {
        $this->dureeIntervention = $dureeIntervention;
        return $this;
    }

    public function getCoutIntervention(): ?string
    {
        return $this->coutIntervention;
    }

    public function setCoutIntervention(?string $coutIntervention): static
    {
        $this->coutIntervention = $coutIntervention;
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

    public function getTechnicienId(): Utilisateurs
    {
        return $this->technicienId;
    }

    public function setTechnicienId(Utilisateurs $technicienId): static
    {
        $this->technicienId = $technicienId;
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

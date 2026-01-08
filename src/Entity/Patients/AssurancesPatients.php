<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'assurances_patients', indexes: [
        new ORM\Index(name: 'convention_id', columns: ["convention_id"]),
        new ORM\Index(name: 'patient_id', columns: ["patient_id"])
    ])]
class AssurancesPatients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroPolice = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $beneficiaireNom = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $beneficiaireLien = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxCouverture = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $montantFranchise = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: ConventionsAssurance::class)]
    #[ORM\JoinColumn(name: 'convention_id', referencedColumnName: 'id', nullable: false)]
    private ConventionsAssurance $conventionId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroPolice(): ?string
    {
        return $this->numeroPolice;
    }

    public function setNumeroPolice(?string $numeroPolice): static
    {
        $this->numeroPolice = $numeroPolice;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
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

    public function getBeneficiaireNom(): ?string
    {
        return $this->beneficiaireNom;
    }

    public function setBeneficiaireNom(?string $beneficiaireNom): static
    {
        $this->beneficiaireNom = $beneficiaireNom;
        return $this;
    }

    public function getBeneficiaireLien(): ?string
    {
        return $this->beneficiaireLien;
    }

    public function setBeneficiaireLien(?string $beneficiaireLien): static
    {
        $this->beneficiaireLien = $beneficiaireLien;
        return $this;
    }

    public function getTauxCouverture(): ?string
    {
        return $this->tauxCouverture;
    }

    public function setTauxCouverture(?string $tauxCouverture): static
    {
        $this->tauxCouverture = $tauxCouverture;
        return $this;
    }

    public function getMontantFranchise(): ?string
    {
        return $this->montantFranchise;
    }

    public function setMontantFranchise(?string $montantFranchise): static
    {
        $this->montantFranchise = $montantFranchise;
        return $this;
    }

    public function getActif(): ?bool
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

    public function getPatientId(): Patients
    {
        return $this->patientId;
    }

    public function setPatientId(Patients $patientId): static
    {
        $this->patientId = $patientId;
        return $this;
    }

    public function getConventionId(): ConventionsAssurance
    {
        return $this->conventionId;
    }

    public function setConventionId(ConventionsAssurance $conventionId): static
    {
        $this->conventionId = $conventionId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

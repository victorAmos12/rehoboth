<?php

namespace App\Entity\ChirurgieEtSoins;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'rapports_operatoires', indexes: [
        new ORM\Index(name: 'chirurgien_id', columns: ["chirurgien_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'idx_planning', columns: ["planning_id"]),
        new ORM\Index(name: 'IDX_13217826CC0FBF92', columns: ["hopital_id"])
    ])]
class RapportsOperatoires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroRapport = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateRapport;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $titreIntervention = null;

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $descriptionIntervention = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $complications = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $produitsUtilises = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $specimensPreleves = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $recommandationsPostop = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateSignature = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: PlanningOperatoire::class)]
    #[ORM\JoinColumn(name: 'planning_id', referencedColumnName: 'id', nullable: false)]
    private PlanningOperatoire $planningId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'chirurgien_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $chirurgienId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroRapport(): string
    {
        return $this->numeroRapport;
    }

    public function setNumeroRapport(string $numeroRapport): static
    {
        $this->numeroRapport = $numeroRapport;
        return $this;
    }

    public function getDateRapport(): \DateTimeInterface
    {
        return $this->dateRapport;
    }

    public function setDateRapport(\DateTimeInterface $dateRapport): static
    {
        $this->dateRapport = $dateRapport;
        return $this;
    }

    public function getTitreIntervention(): ?string
    {
        return $this->titreIntervention;
    }

    public function setTitreIntervention(?string $titreIntervention): static
    {
        $this->titreIntervention = $titreIntervention;
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

    public function getComplications(): ?string
    {
        return $this->complications;
    }

    public function setComplications(?string $complications): static
    {
        $this->complications = $complications;
        return $this;
    }

    public function getProduitsUtilises(): ?string
    {
        return $this->produitsUtilises;
    }

    public function setProduitsUtilises(?string $produitsUtilises): static
    {
        $this->produitsUtilises = $produitsUtilises;
        return $this;
    }

    public function getSpecimensPreleves(): ?string
    {
        return $this->specimensPreleves;
    }

    public function setSpecimensPreleves(?string $specimensPreleves): static
    {
        $this->specimensPreleves = $specimensPreleves;
        return $this;
    }

    public function getRecommandationsPostop(): ?string
    {
        return $this->recommandationsPostop;
    }

    public function setRecommandationsPostop(?string $recommandationsPostop): static
    {
        $this->recommandationsPostop = $recommandationsPostop;
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

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->dateSignature;
    }

    public function setDateSignature(?\DateTimeInterface $dateSignature): static
    {
        $this->dateSignature = $dateSignature;
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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getPlanningId(): PlanningOperatoire
    {
        return $this->planningId;
    }

    public function setPlanningId(PlanningOperatoire $planningId): static
    {
        $this->planningId = $planningId;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getChirurgienId(): Utilisateurs
    {
        return $this->chirurgienId;
    }

    public function setChirurgienId(Utilisateurs $chirurgienId): static
    {
        $this->chirurgienId = $chirurgienId;
        return $this;
    }

}

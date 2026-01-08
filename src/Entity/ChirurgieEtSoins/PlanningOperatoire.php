<?php

namespace App\Entity\ChirurgieEtSoins;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'planning_operatoire', indexes: [
        new ORM\Index(name: 'chirurgien_id', columns: ["chirurgien_id"]),
        new ORM\Index(name: 'demande_intervention_id', columns: ["demande_intervention_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_operation"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_salle', columns: ["salle_operation_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"]),
        new ORM\Index(name: 'patient_id', columns: ["patient_id"])
    ])]
class PlanningOperatoire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateOperation;

    #[ORM\Column(type: 'time', precision: 10)]
    private \DateTimeInterface $heureDebut;

    #[ORM\Column(type: 'time', precision: 10, nullable: true)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeReelle = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $raisonAnnulation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateAnnulation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: SallesOperations::class)]
    #[ORM\JoinColumn(name: 'salle_operation_id', referencedColumnName: 'id', nullable: false)]
    private SallesOperations $salleOperationId;

    #[ORM\ManyToOne(targetEntity: DemandesInterventions::class)]
    #[ORM\JoinColumn(name: 'demande_intervention_id', referencedColumnName: 'id', nullable: false)]
    private DemandesInterventions $demandeInterventionId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

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

    public function getDateOperation(): \DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(\DateTimeInterface $dateOperation): static
    {
        $this->dateOperation = $dateOperation;
        return $this;
    }

    public function getHeureDebut(): \DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getDureeReelle(): ?int
    {
        return $this->dureeReelle;
    }

    public function setDureeReelle(?int $dureeReelle): static
    {
        $this->dureeReelle = $dureeReelle;
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

    public function getRaisonAnnulation(): ?string
    {
        return $this->raisonAnnulation;
    }

    public function setRaisonAnnulation(?string $raisonAnnulation): static
    {
        $this->raisonAnnulation = $raisonAnnulation;
        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeInterface
    {
        return $this->dateAnnulation;
    }

    public function setDateAnnulation(?\DateTimeInterface $dateAnnulation): static
    {
        $this->dateAnnulation = $dateAnnulation;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getSalleOperationId(): SallesOperations
    {
        return $this->salleOperationId;
    }

    public function setSalleOperationId(SallesOperations $salleOperationId): static
    {
        $this->salleOperationId = $salleOperationId;
        return $this;
    }

    public function getDemandeInterventionId(): DemandesInterventions
    {
        return $this->demandeInterventionId;
    }

    public function setDemandeInterventionId(DemandesInterventions $demandeInterventionId): static
    {
        $this->demandeInterventionId = $demandeInterventionId;
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

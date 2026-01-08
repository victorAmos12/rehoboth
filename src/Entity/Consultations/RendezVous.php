<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'rendez_vous', indexes: [
        new ORM\Index(name: 'creneau_id', columns: ["creneau_id"]),
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_rendez_vous"]),
        new ORM\Index(name: 'idx_medecin', columns: ["medecin_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"]),
        new ORM\Index(name: 'service_id', columns: ["service_id"])
    ])]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateRendezVous;

    #[ORM\Column(type: 'time', precision: 10)]
    private \DateTimeInterface $heureRendezVous;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifConsultation = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeConsultation = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesPreConsultation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateConfirmation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateRealisation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateAnnulation = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $raisonAnnulation = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $rappelSmsEnvoye = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $rappelEmailEnvoye = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: CreneauxConsultation::class)]
    #[ORM\JoinColumn(name: 'creneau_id', referencedColumnName: 'id', nullable: false)]
    private CreneauxConsultation $creneauId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateRendezVous(): \DateTimeInterface
    {
        return $this->dateRendezVous;
    }

    public function setDateRendezVous(\DateTimeInterface $dateRendezVous): static
    {
        $this->dateRendezVous = $dateRendezVous;
        return $this;
    }

    public function getHeureRendezVous(): \DateTimeInterface
    {
        return $this->heureRendezVous;
    }

    public function setHeureRendezVous(\DateTimeInterface $heureRendezVous): static
    {
        $this->heureRendezVous = $heureRendezVous;
        return $this;
    }

    public function getMotifConsultation(): ?string
    {
        return $this->motifConsultation;
    }

    public function setMotifConsultation(?string $motifConsultation): static
    {
        $this->motifConsultation = $motifConsultation;
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

    public function getTypeConsultation(): ?string
    {
        return $this->typeConsultation;
    }

    public function setTypeConsultation(?string $typeConsultation): static
    {
        $this->typeConsultation = $typeConsultation;
        return $this;
    }

    public function getNotesPreConsultation(): ?string
    {
        return $this->notesPreConsultation;
    }

    public function setNotesPreConsultation(?string $notesPreConsultation): static
    {
        $this->notesPreConsultation = $notesPreConsultation;
        return $this;
    }

    public function getDateConfirmation(): ?\DateTimeInterface
    {
        return $this->dateConfirmation;
    }

    public function setDateConfirmation(?\DateTimeInterface $dateConfirmation): static
    {
        $this->dateConfirmation = $dateConfirmation;
        return $this;
    }

    public function getDateRealisation(): ?\DateTimeInterface
    {
        return $this->dateRealisation;
    }

    public function setDateRealisation(?\DateTimeInterface $dateRealisation): static
    {
        $this->dateRealisation = $dateRealisation;
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

    public function getRaisonAnnulation(): ?string
    {
        return $this->raisonAnnulation;
    }

    public function setRaisonAnnulation(?string $raisonAnnulation): static
    {
        $this->raisonAnnulation = $raisonAnnulation;
        return $this;
    }

    public function getRappelSmsEnvoye(): ?bool
    {
        return $this->rappelSmsEnvoye;
    }

    public function setRappelSmsEnvoye(?bool $rappelSmsEnvoye): static
    {
        $this->rappelSmsEnvoye = $rappelSmsEnvoye;
        return $this;
    }

    public function getRappelEmailEnvoye(): ?bool
    {
        return $this->rappelEmailEnvoye;
    }

    public function setRappelEmailEnvoye(?bool $rappelEmailEnvoye): static
    {
        $this->rappelEmailEnvoye = $rappelEmailEnvoye;
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

    public function getPatientId(): Patients
    {
        return $this->patientId;
    }

    public function setPatientId(Patients $patientId): static
    {
        $this->patientId = $patientId;
        return $this;
    }

    public function getCreneauId(): CreneauxConsultation
    {
        return $this->creneauId;
    }

    public function setCreneauId(CreneauxConsultation $creneauId): static
    {
        $this->creneauId = $creneauId;
        return $this;
    }

    public function getMedecinId(): Utilisateurs
    {
        return $this->medecinId;
    }

    public function setMedecinId(Utilisateurs $medecinId): static
    {
        $this->medecinId = $medecinId;
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

    public function isRappelSmsEnvoye(): ?bool
    {
        return $this->rappelSmsEnvoye;
    }

    public function isRappelEmailEnvoye(): ?bool
    {
        return $this->rappelEmailEnvoye;
    }

}

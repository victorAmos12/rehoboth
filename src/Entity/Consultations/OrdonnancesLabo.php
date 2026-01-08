<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'ordonnances_labo', indexes: [
        new ORM\Index(name: 'admission_id', columns: ["admission_id"]),
        new ORM\Index(name: 'consultation_id', columns: ["consultation_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_ordonnance"]),
        new ORM\Index(name: 'idx_medecin', columns: ["medecin_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'IDX_22418DF3CC0FBF92', columns: ["hopital_id"])
    ])]
class OrdonnancesLabo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroOrdonnance = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateOrdonnance;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifExamen = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $urgence = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesOrdonnance = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Consultations::class)]
    #[ORM\JoinColumn(name: 'consultation_id', referencedColumnName: 'id', nullable: false)]
    private Consultations $consultationId;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroOrdonnance(): string
    {
        return $this->numeroOrdonnance;
    }

    public function setNumeroOrdonnance(string $numeroOrdonnance): static
    {
        $this->numeroOrdonnance = $numeroOrdonnance;
        return $this;
    }

    public function getDateOrdonnance(): \DateTimeInterface
    {
        return $this->dateOrdonnance;
    }

    public function setDateOrdonnance(\DateTimeInterface $dateOrdonnance): static
    {
        $this->dateOrdonnance = $dateOrdonnance;
        return $this;
    }

    public function getMotifExamen(): ?string
    {
        return $this->motifExamen;
    }

    public function setMotifExamen(?string $motifExamen): static
    {
        $this->motifExamen = $motifExamen;
        return $this;
    }

    public function getUrgence(): ?bool
    {
        return $this->urgence;
    }

    public function setUrgence(?bool $urgence): static
    {
        $this->urgence = $urgence;
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

    public function getNotesOrdonnance(): ?string
    {
        return $this->notesOrdonnance;
    }

    public function setNotesOrdonnance(?string $notesOrdonnance): static
    {
        $this->notesOrdonnance = $notesOrdonnance;
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

    public function getMedecinId(): Utilisateurs
    {
        return $this->medecinId;
    }

    public function setMedecinId(Utilisateurs $medecinId): static
    {
        $this->medecinId = $medecinId;
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

    public function getConsultationId(): Consultations
    {
        return $this->consultationId;
    }

    public function setConsultationId(Consultations $consultationId): static
    {
        $this->consultationId = $consultationId;
        return $this;
    }

    public function getAdmissionId(): Admissions
    {
        return $this->admissionId;
    }

    public function setAdmissionId(Admissions $admissionId): static
    {
        $this->admissionId = $admissionId;
        return $this;
    }

    public function isUrgence(): ?bool
    {
        return $this->urgence;
    }

}

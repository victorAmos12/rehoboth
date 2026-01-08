<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'prescriptions', indexes: [
        new ORM\Index(name: 'admission_id', columns: ["admission_id"]),
        new ORM\Index(name: 'consultation_id', columns: ["consultation_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_prescription"]),
        new ORM\Index(name: 'idx_medecin', columns: ["medecin_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"]),
        new ORM\Index(name: 'IDX_E41E1AC3CC0FBF92', columns: ["hopital_id"])
    ])]
class Prescriptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroPrescription = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $datePrescription;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDebutTraitement = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFinTraitement = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesPrescription = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Consultations::class)]
    #[ORM\JoinColumn(name: 'consultation_id', referencedColumnName: 'id', nullable: false)]
    private Consultations $consultationId;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

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

    public function getNumeroPrescription(): string
    {
        return $this->numeroPrescription;
    }

    public function setNumeroPrescription(string $numeroPrescription): static
    {
        $this->numeroPrescription = $numeroPrescription;
        return $this;
    }

    public function getDatePrescription(): \DateTimeInterface
    {
        return $this->datePrescription;
    }

    public function setDatePrescription(\DateTimeInterface $datePrescription): static
    {
        $this->datePrescription = $datePrescription;
        return $this;
    }

    public function getDateDebutTraitement(): ?\DateTimeInterface
    {
        return $this->dateDebutTraitement;
    }

    public function setDateDebutTraitement(?\DateTimeInterface $dateDebutTraitement): static
    {
        $this->dateDebutTraitement = $dateDebutTraitement;
        return $this;
    }

    public function getDateFinTraitement(): ?\DateTimeInterface
    {
        return $this->dateFinTraitement;
    }

    public function setDateFinTraitement(?\DateTimeInterface $dateFinTraitement): static
    {
        $this->dateFinTraitement = $dateFinTraitement;
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

    public function getNotesPrescription(): ?string
    {
        return $this->notesPrescription;
    }

    public function setNotesPrescription(?string $notesPrescription): static
    {
        $this->notesPrescription = $notesPrescription;
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

}

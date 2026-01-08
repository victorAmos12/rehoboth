<?php

namespace App\Entity\Patients;

use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Services;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'admissions', indexes: [
        new ORM\Index(name: 'idx_date_admission', columns: ["date_admission"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'idx_service', columns: ["service_id"]),
        new ORM\Index(name: 'idx_statut', columns: ["statut"]),
        new ORM\Index(name: 'lit_id', columns: ["lit_id"]),
        new ORM\Index(name: 'medecin_id', columns: ["medecin_id"]),
        new ORM\Index(name: 'medecin_sortie_id', columns: ["medecin_sortie_id"])
    ])]
class Admissions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroAdmission = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateAdmission;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateSortie = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeAdmission = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifAdmission = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $diagnosticPrincipal = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $diagnosticSecondaire = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $raisonSortie = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesSortie = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeSejour = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    #[ORM\ManyToOne(targetEntity: Lits::class)]
    #[ORM\JoinColumn(name: 'lit_id', referencedColumnName: 'id', nullable: false)]
    private Lits $litId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_sortie_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinSortieId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroAdmission(): string
    {
        return $this->numeroAdmission;
    }

    public function setNumeroAdmission(string $numeroAdmission): static
    {
        $this->numeroAdmission = $numeroAdmission;
        return $this;
    }

    public function getDateAdmission(): \DateTimeInterface
    {
        return $this->dateAdmission;
    }

    public function setDateAdmission(\DateTimeInterface $dateAdmission): static
    {
        $this->dateAdmission = $dateAdmission;
        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(?\DateTimeInterface $dateSortie): static
    {
        $this->dateSortie = $dateSortie;
        return $this;
    }

    public function getTypeAdmission(): ?string
    {
        return $this->typeAdmission;
    }

    public function setTypeAdmission(?string $typeAdmission): static
    {
        $this->typeAdmission = $typeAdmission;
        return $this;
    }

    public function getMotifAdmission(): ?string
    {
        return $this->motifAdmission;
    }

    public function setMotifAdmission(?string $motifAdmission): static
    {
        $this->motifAdmission = $motifAdmission;
        return $this;
    }

    public function getDiagnosticPrincipal(): ?string
    {
        return $this->diagnosticPrincipal;
    }

    public function setDiagnosticPrincipal(?string $diagnosticPrincipal): static
    {
        $this->diagnosticPrincipal = $diagnosticPrincipal;
        return $this;
    }

    public function getDiagnosticSecondaire(): ?string
    {
        return $this->diagnosticSecondaire;
    }

    public function setDiagnosticSecondaire(?string $diagnosticSecondaire): static
    {
        $this->diagnosticSecondaire = $diagnosticSecondaire;
        return $this;
    }

    public function getRaisonSortie(): ?string
    {
        return $this->raisonSortie;
    }

    public function setRaisonSortie(?string $raisonSortie): static
    {
        $this->raisonSortie = $raisonSortie;
        return $this;
    }

    public function getNotesSortie(): ?string
    {
        return $this->notesSortie;
    }

    public function setNotesSortie(?string $notesSortie): static
    {
        $this->notesSortie = $notesSortie;
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

    public function getDureeSejour(): ?int
    {
        return $this->dureeSejour;
    }

    public function setDureeSejour(?int $dureeSejour): static
    {
        $this->dureeSejour = $dureeSejour;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
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

    public function getLitId(): Lits
    {
        return $this->litId;
    }

    public function setLitId(Lits $litId): static
    {
        $this->litId = $litId;
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

    public function getMedecinSortieId(): Utilisateurs
    {
        return $this->medecinSortieId;
    }

    public function setMedecinSortieId(Utilisateurs $medecinSortieId): static
    {
        $this->medecinSortieId = $medecinSortieId;
        return $this;
    }

}

<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'feuilles_sortie', indexes: [
        new ORM\Index(name: 'idx_admission', columns: ["admission_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'medecin_id', columns: ["medecin_id"]),
        new ORM\Index(name: 'medecin_suivi_id', columns: ["medecin_suivi_id"]),
        new ORM\Index(name: 'IDX_5017B32ACC0FBF92', columns: ["hopital_id"])
    ])]
class FeuillesSortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroFeuille = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateSortie;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $diagnosticPrincipal = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $diagnosticSecondaire = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $proceduresEffectuees = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $medicamentsSortie = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $recommandationsSortie = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $suiviRecommande = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateProchainRendezVous = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $restrictionsActivites = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $regimeAlimentaire = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $soinsPlaies = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $signesAlerte = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_suivi_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinSuiviId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroFeuille(): string
    {
        return $this->numeroFeuille;
    }

    public function setNumeroFeuille(string $numeroFeuille): static
    {
        $this->numeroFeuille = $numeroFeuille;
        return $this;
    }

    public function getDateSortie(): \DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(\DateTimeInterface $dateSortie): static
    {
        $this->dateSortie = $dateSortie;
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

    public function getProceduresEffectuees(): ?string
    {
        return $this->proceduresEffectuees;
    }

    public function setProceduresEffectuees(?string $proceduresEffectuees): static
    {
        $this->proceduresEffectuees = $proceduresEffectuees;
        return $this;
    }

    public function getMedicamentsSortie(): ?string
    {
        return $this->medicamentsSortie;
    }

    public function setMedicamentsSortie(?string $medicamentsSortie): static
    {
        $this->medicamentsSortie = $medicamentsSortie;
        return $this;
    }

    public function getRecommandationsSortie(): ?string
    {
        return $this->recommandationsSortie;
    }

    public function setRecommandationsSortie(?string $recommandationsSortie): static
    {
        $this->recommandationsSortie = $recommandationsSortie;
        return $this;
    }

    public function getSuiviRecommande(): ?string
    {
        return $this->suiviRecommande;
    }

    public function setSuiviRecommande(?string $suiviRecommande): static
    {
        $this->suiviRecommande = $suiviRecommande;
        return $this;
    }

    public function getDateProchainRendezVous(): ?\DateTimeInterface
    {
        return $this->dateProchainRendezVous;
    }

    public function setDateProchainRendezVous(?\DateTimeInterface $dateProchainRendezVous): static
    {
        $this->dateProchainRendezVous = $dateProchainRendezVous;
        return $this;
    }

    public function getRestrictionsActivites(): ?string
    {
        return $this->restrictionsActivites;
    }

    public function setRestrictionsActivites(?string $restrictionsActivites): static
    {
        $this->restrictionsActivites = $restrictionsActivites;
        return $this;
    }

    public function getRegimeAlimentaire(): ?string
    {
        return $this->regimeAlimentaire;
    }

    public function setRegimeAlimentaire(?string $regimeAlimentaire): static
    {
        $this->regimeAlimentaire = $regimeAlimentaire;
        return $this;
    }

    public function getSoinsPlaies(): ?string
    {
        return $this->soinsPlaies;
    }

    public function setSoinsPlaies(?string $soinsPlaies): static
    {
        $this->soinsPlaies = $soinsPlaies;
        return $this;
    }

    public function getSignesAlerte(): ?string
    {
        return $this->signesAlerte;
    }

    public function setSignesAlerte(?string $signesAlerte): static
    {
        $this->signesAlerte = $signesAlerte;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
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

    public function getMedecinSuiviId(): Utilisateurs
    {
        return $this->medecinSuiviId;
    }

    public function setMedecinSuiviId(Utilisateurs $medecinSuiviId): static
    {
        $this->medecinSuiviId = $medecinSuiviId;
        return $this;
    }

}

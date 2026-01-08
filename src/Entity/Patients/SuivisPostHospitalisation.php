<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'suivis_post_hospitalisation', indexes: [
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_feuille', columns: ["feuille_sortie_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'utilisateur_suivi_id', columns: ["utilisateur_suivi_id"])
    ])]
class SuivisPostHospitalisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateSuivi;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeSuivi = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsSuivi = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $complicationsObservees = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $adherenceTraitement = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $readmissionNecessaire = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $raisonReadmission = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: FeuillesSortie::class)]
    #[ORM\JoinColumn(name: 'feuille_sortie_id', referencedColumnName: 'id', nullable: false)]
    private FeuillesSortie $feuilleSortieId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_suivi_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurSuiviId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateSuivi(): \DateTimeInterface
    {
        return $this->dateSuivi;
    }

    public function setDateSuivi(\DateTimeInterface $dateSuivi): static
    {
        $this->dateSuivi = $dateSuivi;
        return $this;
    }

    public function getTypeSuivi(): ?string
    {
        return $this->typeSuivi;
    }

    public function setTypeSuivi(?string $typeSuivi): static
    {
        $this->typeSuivi = $typeSuivi;
        return $this;
    }

    public function getObservationsSuivi(): ?string
    {
        return $this->observationsSuivi;
    }

    public function setObservationsSuivi(?string $observationsSuivi): static
    {
        $this->observationsSuivi = $observationsSuivi;
        return $this;
    }

    public function getComplicationsObservees(): ?string
    {
        return $this->complicationsObservees;
    }

    public function setComplicationsObservees(?string $complicationsObservees): static
    {
        $this->complicationsObservees = $complicationsObservees;
        return $this;
    }

    public function getAdherenceTraitement(): ?string
    {
        return $this->adherenceTraitement;
    }

    public function setAdherenceTraitement(?string $adherenceTraitement): static
    {
        $this->adherenceTraitement = $adherenceTraitement;
        return $this;
    }

    public function getReadmissionNecessaire(): ?bool
    {
        return $this->readmissionNecessaire;
    }

    public function setReadmissionNecessaire(?bool $readmissionNecessaire): static
    {
        $this->readmissionNecessaire = $readmissionNecessaire;
        return $this;
    }

    public function getRaisonReadmission(): ?string
    {
        return $this->raisonReadmission;
    }

    public function setRaisonReadmission(?string $raisonReadmission): static
    {
        $this->raisonReadmission = $raisonReadmission;
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

    public function getFeuilleSortieId(): FeuillesSortie
    {
        return $this->feuilleSortieId;
    }

    public function setFeuilleSortieId(FeuillesSortie $feuilleSortieId): static
    {
        $this->feuilleSortieId = $feuilleSortieId;
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

    public function getUtilisateurSuiviId(): Utilisateurs
    {
        return $this->utilisateurSuiviId;
    }

    public function setUtilisateurSuiviId(Utilisateurs $utilisateurSuiviId): static
    {
        $this->utilisateurSuiviId = $utilisateurSuiviId;
        return $this;
    }

    public function isReadmissionNecessaire(): ?bool
    {
        return $this->readmissionNecessaire;
    }

}

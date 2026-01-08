<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'consultations', indexes: [
        new ORM\Index(name: 'admission_id', columns: ["admission_id"]),
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_consultation"]),
        new ORM\Index(name: 'idx_medecin', columns: ["medecin_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'rendez_vous_id', columns: ["rendez_vous_id"]),
        new ORM\Index(name: 'service_id', columns: ["service_id"])
    ])]
class Consultations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateConsultation;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifConsultation = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $diagnosticPrincipal = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $diagnosticSecondaire = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $planTraitement = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsCliniques = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $examenPhysique = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $tensionArterielle = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $frequenceCardiaque = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 1, nullable: true)]
    private ?string $temperature = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private ?string $poids = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $taille = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $imc = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

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

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: RendezVous::class)]
    #[ORM\JoinColumn(name: 'rendez_vous_id', referencedColumnName: 'id', nullable: false)]
    private RendezVous $rendezVousId;

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

    public function getDateConsultation(): \DateTimeInterface
    {
        return $this->dateConsultation;
    }

    public function setDateConsultation(\DateTimeInterface $dateConsultation): static
    {
        $this->dateConsultation = $dateConsultation;
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

    public function getPlanTraitement(): ?string
    {
        return $this->planTraitement;
    }

    public function setPlanTraitement(?string $planTraitement): static
    {
        $this->planTraitement = $planTraitement;
        return $this;
    }

    public function getObservationsCliniques(): ?string
    {
        return $this->observationsCliniques;
    }

    public function setObservationsCliniques(?string $observationsCliniques): static
    {
        $this->observationsCliniques = $observationsCliniques;
        return $this;
    }

    public function getExamenPhysique(): ?string
    {
        return $this->examenPhysique;
    }

    public function setExamenPhysique(?string $examenPhysique): static
    {
        $this->examenPhysique = $examenPhysique;
        return $this;
    }

    public function getTensionArterielle(): ?string
    {
        return $this->tensionArterielle;
    }

    public function setTensionArterielle(?string $tensionArterielle): static
    {
        $this->tensionArterielle = $tensionArterielle;
        return $this;
    }

    public function getFrequenceCardiaque(): ?int
    {
        return $this->frequenceCardiaque;
    }

    public function setFrequenceCardiaque(?int $frequenceCardiaque): static
    {
        $this->frequenceCardiaque = $frequenceCardiaque;
        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function setPoids(?string $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(?string $taille): static
    {
        $this->taille = $taille;
        return $this;
    }

    public function getImc(): ?string
    {
        return $this->imc;
    }

    public function setImc(?string $imc): static
    {
        $this->imc = $imc;
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

    public function getRendezVousId(): RendezVous
    {
        return $this->rendezVousId;
    }

    public function setRendezVousId(RendezVous $rendezVousId): static
    {
        $this->rendezVousId = $rendezVousId;
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

}

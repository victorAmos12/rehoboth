<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'triages', indexes: [
        new ORM\Index(name: 'idx_date', columns: ["date_triage"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'infirmier_triage_id', columns: ["infirmier_triage_id"]),
        new ORM\Index(name: 'niveau_triage_id', columns: ["niveau_triage_id"]),
        new ORM\Index(name: 'service_urgences_id', columns: ["service_urgences_id"])
    ])]
class Triages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroTriage = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateTriage;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifConsultation = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $antecedentsPertinents = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $medicamentsActuels = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $tensionArterielle = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $frequenceCardiaque = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 1, nullable: true)]
    private ?string $temperature = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $frequenceRespiratoire = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $saturationO2 = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private ?string $poids = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsTriage = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $datePriseEnCharge = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_urgences_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceUrgencesId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'infirmier_triage_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $infirmierTriageId;

    #[ORM\ManyToOne(targetEntity: NiveauxTriage::class)]
    #[ORM\JoinColumn(name: 'niveau_triage_id', referencedColumnName: 'id', nullable: false)]
    private NiveauxTriage $niveauTriageId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroTriage(): string
    {
        return $this->numeroTriage;
    }

    public function setNumeroTriage(string $numeroTriage): static
    {
        $this->numeroTriage = $numeroTriage;
        return $this;
    }

    public function getDateTriage(): \DateTimeInterface
    {
        return $this->dateTriage;
    }

    public function setDateTriage(\DateTimeInterface $dateTriage): static
    {
        $this->dateTriage = $dateTriage;
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

    public function getAntecedentsPertinents(): ?string
    {
        return $this->antecedentsPertinents;
    }

    public function setAntecedentsPertinents(?string $antecedentsPertinents): static
    {
        $this->antecedentsPertinents = $antecedentsPertinents;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getMedicamentsActuels(): ?string
    {
        return $this->medicamentsActuels;
    }

    public function setMedicamentsActuels(?string $medicamentsActuels): static
    {
        $this->medicamentsActuels = $medicamentsActuels;
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

    public function getFrequenceRespiratoire(): ?int
    {
        return $this->frequenceRespiratoire;
    }

    public function setFrequenceRespiratoire(?int $frequenceRespiratoire): static
    {
        $this->frequenceRespiratoire = $frequenceRespiratoire;
        return $this;
    }

    public function getSaturationO2(): ?string
    {
        return $this->saturationO2;
    }

    public function setSaturationO2(?string $saturationO2): static
    {
        $this->saturationO2 = $saturationO2;
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

    public function getObservationsTriage(): ?string
    {
        return $this->observationsTriage;
    }

    public function setObservationsTriage(?string $observationsTriage): static
    {
        $this->observationsTriage = $observationsTriage;
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

    public function getDatePriseEnCharge(): ?\DateTimeInterface
    {
        return $this->datePriseEnCharge;
    }

    public function setDatePriseEnCharge(?\DateTimeInterface $datePriseEnCharge): static
    {
        $this->datePriseEnCharge = $datePriseEnCharge;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getServiceUrgencesId(): Services
    {
        return $this->serviceUrgencesId;
    }

    public function setServiceUrgencesId(Services $serviceUrgencesId): static
    {
        $this->serviceUrgencesId = $serviceUrgencesId;
        return $this;
    }

    public function getInfirmierTriageId(): Utilisateurs
    {
        return $this->infirmierTriageId;
    }

    public function setInfirmierTriageId(Utilisateurs $infirmierTriageId): static
    {
        $this->infirmierTriageId = $infirmierTriageId;
        return $this;
    }

    public function getNiveauTriageId(): NiveauxTriage
    {
        return $this->niveauTriageId;
    }

    public function setNiveauTriageId(NiveauxTriage $niveauTriageId): static
    {
        $this->niveauTriageId = $niveauTriageId;
        return $this;
    }

}

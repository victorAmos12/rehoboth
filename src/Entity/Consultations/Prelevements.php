<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'prelevements', indexes: [
        new ORM\Index(name: 'idx_date', columns: ["date_prelevement"]),
        new ORM\Index(name: 'idx_ordonnance', columns: ["ordonnance_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'infirmier_id', columns: ["infirmier_id"]),
        new ORM\Index(name: 'IDX_968E0A99CC0FBF92', columns: ["hopital_id"])
    ])]
class Prelevements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroPrelevement = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $datePrelevement;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeSpecimen = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $volumeSpecimen = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $tubePrelevement = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $numeroTube = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $conditionsConservation = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsPrelevement = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateReceptionLabo = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $raisonRejet = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: OrdonnancesLabo::class)]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'id', nullable: false)]
    private OrdonnancesLabo $ordonnanceId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'infirmier_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $infirmierId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroPrelevement(): string
    {
        return $this->numeroPrelevement;
    }

    public function setNumeroPrelevement(string $numeroPrelevement): static
    {
        $this->numeroPrelevement = $numeroPrelevement;
        return $this;
    }

    public function getDatePrelevement(): \DateTimeInterface
    {
        return $this->datePrelevement;
    }

    public function setDatePrelevement(\DateTimeInterface $datePrelevement): static
    {
        $this->datePrelevement = $datePrelevement;
        return $this;
    }

    public function getTypeSpecimen(): ?string
    {
        return $this->typeSpecimen;
    }

    public function setTypeSpecimen(?string $typeSpecimen): static
    {
        $this->typeSpecimen = $typeSpecimen;
        return $this;
    }

    public function getVolumeSpecimen(): ?int
    {
        return $this->volumeSpecimen;
    }

    public function setVolumeSpecimen(?int $volumeSpecimen): static
    {
        $this->volumeSpecimen = $volumeSpecimen;
        return $this;
    }

    public function getTubePrelevement(): ?string
    {
        return $this->tubePrelevement;
    }

    public function setTubePrelevement(?string $tubePrelevement): static
    {
        $this->tubePrelevement = $tubePrelevement;
        return $this;
    }

    public function getNumeroTube(): ?string
    {
        return $this->numeroTube;
    }

    public function setNumeroTube(?string $numeroTube): static
    {
        $this->numeroTube = $numeroTube;
        return $this;
    }

    public function getConditionsConservation(): ?string
    {
        return $this->conditionsConservation;
    }

    public function setConditionsConservation(?string $conditionsConservation): static
    {
        $this->conditionsConservation = $conditionsConservation;
        return $this;
    }

    public function getObservationsPrelevement(): ?string
    {
        return $this->observationsPrelevement;
    }

    public function setObservationsPrelevement(?string $observationsPrelevement): static
    {
        $this->observationsPrelevement = $observationsPrelevement;
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

    public function getDateReceptionLabo(): ?\DateTimeInterface
    {
        return $this->dateReceptionLabo;
    }

    public function setDateReceptionLabo(?\DateTimeInterface $dateReceptionLabo): static
    {
        $this->dateReceptionLabo = $dateReceptionLabo;
        return $this;
    }

    public function getRaisonRejet(): ?string
    {
        return $this->raisonRejet;
    }

    public function setRaisonRejet(?string $raisonRejet): static
    {
        $this->raisonRejet = $raisonRejet;
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

    public function getOrdonnanceId(): OrdonnancesLabo
    {
        return $this->ordonnanceId;
    }

    public function setOrdonnanceId(OrdonnancesLabo $ordonnanceId): static
    {
        $this->ordonnanceId = $ordonnanceId;
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

    public function getInfirmierId(): Utilisateurs
    {
        return $this->infirmierId;
    }

    public function setInfirmierId(Utilisateurs $infirmierId): static
    {
        $this->infirmierId = $infirmierId;
        return $this;
    }

}

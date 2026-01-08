<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'examens_imagerie', indexes: [
        new ORM\Index(name: 'idx_ordonnance', columns: ["ordonnance_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'imagerie_id', columns: ["imagerie_id"]),
        new ORM\Index(name: 'technicien_id', columns: ["technicien_id"]),
        new ORM\Index(name: 'IDX_23A8D0ABCC0FBF92', columns: ["hopital_id"])
    ])]
class ExamensImagerie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroExamen = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateExamen;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $modalite = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $zoneAnatomique = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsTechnique = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: OrdonnancesImagerie::class)]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'id', nullable: false)]
    private OrdonnancesImagerie $ordonnanceId;

    #[ORM\ManyToOne(targetEntity: TypesImagerie::class)]
    #[ORM\JoinColumn(name: 'imagerie_id', referencedColumnName: 'id', nullable: false)]
    private TypesImagerie $imagerieId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'technicien_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $technicienId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroExamen(): string
    {
        return $this->numeroExamen;
    }

    public function setNumeroExamen(string $numeroExamen): static
    {
        $this->numeroExamen = $numeroExamen;
        return $this;
    }

    public function getDateExamen(): \DateTimeInterface
    {
        return $this->dateExamen;
    }

    public function setDateExamen(\DateTimeInterface $dateExamen): static
    {
        $this->dateExamen = $dateExamen;
        return $this;
    }

    public function getModalite(): ?string
    {
        return $this->modalite;
    }

    public function setModalite(?string $modalite): static
    {
        $this->modalite = $modalite;
        return $this;
    }

    public function getZoneAnatomique(): ?string
    {
        return $this->zoneAnatomique;
    }

    public function setZoneAnatomique(?string $zoneAnatomique): static
    {
        $this->zoneAnatomique = $zoneAnatomique;
        return $this;
    }

    public function getObservationsTechnique(): ?string
    {
        return $this->observationsTechnique;
    }

    public function setObservationsTechnique(?string $observationsTechnique): static
    {
        $this->observationsTechnique = $observationsTechnique;
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

    public function getOrdonnanceId(): OrdonnancesImagerie
    {
        return $this->ordonnanceId;
    }

    public function setOrdonnanceId(OrdonnancesImagerie $ordonnanceId): static
    {
        $this->ordonnanceId = $ordonnanceId;
        return $this;
    }

    public function getImagerieId(): TypesImagerie
    {
        return $this->imagerieId;
    }

    public function setImagerieId(TypesImagerie $imagerieId): static
    {
        $this->imagerieId = $imagerieId;
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

    public function getTechnicienId(): Utilisateurs
    {
        return $this->technicienId;
    }

    public function setTechnicienId(Utilisateurs $technicienId): static
    {
        $this->technicienId = $technicienId;
        return $this;
    }

}

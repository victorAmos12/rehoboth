<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'archives', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'utilisateur_archivage_id', columns: ["utilisateur_archivage_id"])
    ])]
class Archives
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroArchive = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateArchivage;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeArchivage = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $raisonArchivage = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $localisationArchive = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $supportArchive = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeConservation = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDestructionPrevue = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_archivage_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurArchivageId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroArchive(): string
    {
        return $this->numeroArchive;
    }

    public function setNumeroArchive(string $numeroArchive): static
    {
        $this->numeroArchive = $numeroArchive;
        return $this;
    }

    public function getDateArchivage(): \DateTimeInterface
    {
        return $this->dateArchivage;
    }

    public function setDateArchivage(\DateTimeInterface $dateArchivage): static
    {
        $this->dateArchivage = $dateArchivage;
        return $this;
    }

    public function getTypeArchivage(): ?string
    {
        return $this->typeArchivage;
    }

    public function setTypeArchivage(?string $typeArchivage): static
    {
        $this->typeArchivage = $typeArchivage;
        return $this;
    }

    public function getRaisonArchivage(): ?string
    {
        return $this->raisonArchivage;
    }

    public function setRaisonArchivage(?string $raisonArchivage): static
    {
        $this->raisonArchivage = $raisonArchivage;
        return $this;
    }

    public function getLocalisationArchive(): ?string
    {
        return $this->localisationArchive;
    }

    public function setLocalisationArchive(?string $localisationArchive): static
    {
        $this->localisationArchive = $localisationArchive;
        return $this;
    }

    public function getSupportArchive(): ?string
    {
        return $this->supportArchive;
    }

    public function setSupportArchive(?string $supportArchive): static
    {
        $this->supportArchive = $supportArchive;
        return $this;
    }

    public function getDureeConservation(): ?int
    {
        return $this->dureeConservation;
    }

    public function setDureeConservation(?int $dureeConservation): static
    {
        $this->dureeConservation = $dureeConservation;
        return $this;
    }

    public function getDateDestructionPrevue(): ?\DateTimeInterface
    {
        return $this->dateDestructionPrevue;
    }

    public function setDateDestructionPrevue(?\DateTimeInterface $dateDestructionPrevue): static
    {
        $this->dateDestructionPrevue = $dateDestructionPrevue;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
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

    public function getUtilisateurArchivageId(): Utilisateurs
    {
        return $this->utilisateurArchivageId;
    }

    public function setUtilisateurArchivageId(Utilisateurs $utilisateurArchivageId): static
    {
        $this->utilisateurArchivageId = $utilisateurArchivageId;
        return $this;
    }

}

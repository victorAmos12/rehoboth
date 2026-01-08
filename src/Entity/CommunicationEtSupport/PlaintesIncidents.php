<?php

namespace App\Entity\CommunicationEtSupport;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'plaintes_incidents', indexes: [
        new ORM\Index(name: 'idx_date', columns: ["date_plainte"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'patient_id', columns: ["patient_id"]),
        new ORM\Index(name: 'responsable_investigation_id', columns: ["responsable_investigation_id"]),
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"])
    ])]
class PlaintesIncidents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroPlainte = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $datePlainte;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typePlainte = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $descriptionPlainte = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $gravite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateResolution = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $actionsCorrectives = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'responsable_investigation_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $responsableInvestigationId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroPlainte(): string
    {
        return $this->numeroPlainte;
    }

    public function setNumeroPlainte(string $numeroPlainte): static
    {
        $this->numeroPlainte = $numeroPlainte;
        return $this;
    }

    public function getDatePlainte(): \DateTimeInterface
    {
        return $this->datePlainte;
    }

    public function setDatePlainte(\DateTimeInterface $datePlainte): static
    {
        $this->datePlainte = $datePlainte;
        return $this;
    }

    public function getTypePlainte(): ?string
    {
        return $this->typePlainte;
    }

    public function setTypePlainte(?string $typePlainte): static
    {
        $this->typePlainte = $typePlainte;
        return $this;
    }

    public function getDescriptionPlainte(): ?string
    {
        return $this->descriptionPlainte;
    }

    public function setDescriptionPlainte(?string $descriptionPlainte): static
    {
        $this->descriptionPlainte = $descriptionPlainte;
        return $this;
    }

    public function getGravite(): ?string
    {
        return $this->gravite;
    }

    public function setGravite(?string $gravite): static
    {
        $this->gravite = $gravite;
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

    public function getDateResolution(): ?\DateTimeInterface
    {
        return $this->dateResolution;
    }

    public function setDateResolution(?\DateTimeInterface $dateResolution): static
    {
        $this->dateResolution = $dateResolution;
        return $this;
    }

    public function getActionsCorrectives(): ?string
    {
        return $this->actionsCorrectives;
    }

    public function setActionsCorrectives(?string $actionsCorrectives): static
    {
        $this->actionsCorrectives = $actionsCorrectives;
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

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function getResponsableInvestigationId(): Utilisateurs
    {
        return $this->responsableInvestigationId;
    }

    public function setResponsableInvestigationId(Utilisateurs $responsableInvestigationId): static
    {
        $this->responsableInvestigationId = $responsableInvestigationId;
        return $this;
    }

}

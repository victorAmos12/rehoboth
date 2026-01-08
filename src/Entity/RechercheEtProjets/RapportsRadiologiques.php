<?php

namespace App\Entity\RechercheEtProjets;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'rapports_radiologiques', indexes: [
        new ORM\Index(name: 'idx_examen', columns: ["examen_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'radiologue_id', columns: ["radiologue_id"]),
        new ORM\Index(name: 'IDX_E6598CEDCC0FBF92', columns: ["hopital_id"])
    ])]
class RapportsRadiologiques
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroRapport = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateRapport;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $titreRapport = null;

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $contenuRapport = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $conclusion = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $recommandations = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateSignature = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: ExamensImagerie::class)]
    #[ORM\JoinColumn(name: 'examen_id', referencedColumnName: 'id', nullable: false)]
    private ExamensImagerie $examenId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'radiologue_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $radiologueId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroRapport(): string
    {
        return $this->numeroRapport;
    }

    public function setNumeroRapport(string $numeroRapport): static
    {
        $this->numeroRapport = $numeroRapport;
        return $this;
    }

    public function getDateRapport(): \DateTimeInterface
    {
        return $this->dateRapport;
    }

    public function setDateRapport(\DateTimeInterface $dateRapport): static
    {
        $this->dateRapport = $dateRapport;
        return $this;
    }

    public function getTitreRapport(): ?string
    {
        return $this->titreRapport;
    }

    public function setTitreRapport(?string $titreRapport): static
    {
        $this->titreRapport = $titreRapport;
        return $this;
    }

    public function getContenuRapport(): ?string
    {
        return $this->contenuRapport;
    }

    public function setContenuRapport(?string $contenuRapport): static
    {
        $this->contenuRapport = $contenuRapport;
        return $this;
    }

    public function getConclusion(): ?string
    {
        return $this->conclusion;
    }

    public function setConclusion(?string $conclusion): static
    {
        $this->conclusion = $conclusion;
        return $this;
    }

    public function getRecommandations(): ?string
    {
        return $this->recommandations;
    }

    public function setRecommandations(?string $recommandations): static
    {
        $this->recommandations = $recommandations;
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

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->dateSignature;
    }

    public function setDateSignature(?\DateTimeInterface $dateSignature): static
    {
        $this->dateSignature = $dateSignature;
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

    public function getExamenId(): ExamensImagerie
    {
        return $this->examenId;
    }

    public function setExamenId(ExamensImagerie $examenId): static
    {
        $this->examenId = $examenId;
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

    public function getRadiologueId(): Utilisateurs
    {
        return $this->radiologueId;
    }

    public function setRadiologueId(Utilisateurs $radiologueId): static
    {
        $this->radiologueId = $radiologueId;
        return $this;
    }

}

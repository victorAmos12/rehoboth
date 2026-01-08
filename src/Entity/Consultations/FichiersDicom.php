<?php

namespace App\Entity\Consultations;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'fichiers_dicom', indexes: [
        new ORM\Index(name: 'idx_examen', columns: ["examen_id"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"])
    ])]
class FichiersDicom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroInstance = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $cheminFichier = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $nomFichier = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $tailleFichier = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $formatFichier = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreationDicom = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateArchivage = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $localisationPacs = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: ExamensImagerie::class)]
    #[ORM\JoinColumn(name: 'examen_id', referencedColumnName: 'id', nullable: false)]
    private ExamensImagerie $examenId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(?string $numeroSerie): static
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    public function getNumeroInstance(): ?string
    {
        return $this->numeroInstance;
    }

    public function setNumeroInstance(?string $numeroInstance): static
    {
        $this->numeroInstance = $numeroInstance;
        return $this;
    }

    public function getCheminFichier(): ?string
    {
        return $this->cheminFichier;
    }

    public function setCheminFichier(?string $cheminFichier): static
    {
        $this->cheminFichier = $cheminFichier;
        return $this;
    }

    public function getNomFichier(): ?string
    {
        return $this->nomFichier;
    }

    public function setNomFichier(?string $nomFichier): static
    {
        $this->nomFichier = $nomFichier;
        return $this;
    }

    public function getTailleFichier(): ?int
    {
        return $this->tailleFichier;
    }

    public function setTailleFichier(?int $tailleFichier): static
    {
        $this->tailleFichier = $tailleFichier;
        return $this;
    }

    public function getFormatFichier(): ?string
    {
        return $this->formatFichier;
    }

    public function setFormatFichier(?string $formatFichier): static
    {
        $this->formatFichier = $formatFichier;
        return $this;
    }

    public function getDateCreationDicom(): ?\DateTimeInterface
    {
        return $this->dateCreationDicom;
    }

    public function setDateCreationDicom(?\DateTimeInterface $dateCreationDicom): static
    {
        $this->dateCreationDicom = $dateCreationDicom;
        return $this;
    }

    public function getDateArchivage(): ?\DateTimeInterface
    {
        return $this->dateArchivage;
    }

    public function setDateArchivage(?\DateTimeInterface $dateArchivage): static
    {
        $this->dateArchivage = $dateArchivage;
        return $this;
    }

    public function getLocalisationPacs(): ?string
    {
        return $this->localisationPacs;
    }

    public function setLocalisationPacs(?string $localisationPacs): static
    {
        $this->localisationPacs = $localisationPacs;
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

    public function getExamenId(): ExamensImagerie
    {
        return $this->examenId;
    }

    public function setExamenId(ExamensImagerie $examenId): static
    {
        $this->examenId = $examenId;
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

}

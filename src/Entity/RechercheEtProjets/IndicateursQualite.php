<?php

namespace App\Entity\RechercheEtProjets;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'indicateurs_qualite', indexes: [
        new ORM\Index(name: 'service_id', columns: ["service_id"]),
        new ORM\Index(name: 'IDX_30E5964FCC0FBF92', columns: ["hopital_id"])
    ])]
class IndicateursQualite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeIndicateur = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomIndicateur = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeIndicateur = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $uniteMesure = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $valeurCible = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $frequenceMesure = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCodeIndicateur(): string
    {
        return $this->codeIndicateur;
    }

    public function setCodeIndicateur(string $codeIndicateur): static
    {
        $this->codeIndicateur = $codeIndicateur;
        return $this;
    }

    public function getNomIndicateur(): string
    {
        return $this->nomIndicateur;
    }

    public function setNomIndicateur(string $nomIndicateur): static
    {
        $this->nomIndicateur = $nomIndicateur;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeIndicateur(): ?string
    {
        return $this->typeIndicateur;
    }

    public function setTypeIndicateur(?string $typeIndicateur): static
    {
        $this->typeIndicateur = $typeIndicateur;
        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->uniteMesure;
    }

    public function setUniteMesure(?string $uniteMesure): static
    {
        $this->uniteMesure = $uniteMesure;
        return $this;
    }

    public function getValeurCible(): ?string
    {
        return $this->valeurCible;
    }

    public function setValeurCible(?string $valeurCible): static
    {
        $this->valeurCible = $valeurCible;
        return $this;
    }

    public function getFrequenceMesure(): ?string
    {
        return $this->frequenceMesure;
    }

    public function setFrequenceMesure(?string $frequenceMesure): static
    {
        $this->frequenceMesure = $frequenceMesure;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;
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

    public function getServiceId(): Services
    {
        return $this->serviceId;
    }

    public function setServiceId(Services $serviceId): static
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

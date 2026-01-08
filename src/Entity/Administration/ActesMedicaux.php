<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'actes_medicaux', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'taux_tva_id', columns: ["taux_tva_id"]),
        new ORM\Index(name: 'IDX_3C3D92DACC0FBF92', columns: ["hopital_id"])
    ])]
class ActesMedicaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeActe = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomActe = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeCcam = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeCim = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixActe = null;

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

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: 'devise_id', referencedColumnName: 'id', nullable: false)]
    private Devises $deviseId;

    #[ORM\ManyToOne(targetEntity: TauxTva::class)]
    #[ORM\JoinColumn(name: 'taux_tva_id', referencedColumnName: 'id', nullable: false)]
    private TauxTva $tauxTvaId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCodeActe(): string
    {
        return $this->codeActe;
    }

    public function setCodeActe(string $codeActe): static
    {
        $this->codeActe = $codeActe;
        return $this;
    }

    public function getNomActe(): string
    {
        return $this->nomActe;
    }

    public function setNomActe(string $nomActe): static
    {
        $this->nomActe = $nomActe;
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

    public function getCodeCcam(): ?string
    {
        return $this->codeCcam;
    }

    public function setCodeCcam(?string $codeCcam): static
    {
        $this->codeCcam = $codeCcam;
        return $this;
    }

    public function getCodeCim(): ?string
    {
        return $this->codeCim;
    }

    public function setCodeCim(?string $codeCim): static
    {
        $this->codeCim = $codeCim;
        return $this;
    }

    public function getPrixActe(): ?string
    {
        return $this->prixActe;
    }

    public function setPrixActe(?string $prixActe): static
    {
        $this->prixActe = $prixActe;
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

    public function getDeviseId(): Devises
    {
        return $this->deviseId;
    }

    public function setDeviseId(Devises $deviseId): static
    {
        $this->deviseId = $deviseId;
        return $this;
    }

    public function getTauxTvaId(): TauxTva
    {
        return $this->tauxTvaId;
    }

    public function setTauxTvaId(TauxTva $tauxTvaId): static
    {
        $this->tauxTvaId = $tauxTvaId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

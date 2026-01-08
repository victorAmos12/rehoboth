<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'panels_examens', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'taux_tva_id', columns: ["taux_tva_id"])
    ])]
class PanelsExamens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codePanel = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomPanel = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixPanel = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

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

    public function getCodePanel(): string
    {
        return $this->codePanel;
    }

    public function setCodePanel(string $codePanel): static
    {
        $this->codePanel = $codePanel;
        return $this;
    }

    public function getNomPanel(): string
    {
        return $this->nomPanel;
    }

    public function setNomPanel(string $nomPanel): static
    {
        $this->nomPanel = $nomPanel;
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

    public function getPrixPanel(): ?string
    {
        return $this->prixPanel;
    }

    public function setPrixPanel(?string $prixPanel): static
    {
        $this->prixPanel = $prixPanel;
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

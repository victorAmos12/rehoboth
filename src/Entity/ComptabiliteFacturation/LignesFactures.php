<?php

namespace App\Entity\ComptabiliteFacturation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_factures', indexes: [
        new ORM\Index(name: 'acte_id', columns: ["acte_id"]),
        new ORM\Index(name: 'facture_id', columns: ["facture_id"])
    ])]
class LignesFactures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $descriptionLigne = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantLigne = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxTva = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantTva = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Factures::class)]
    #[ORM\JoinColumn(name: 'facture_id', referencedColumnName: 'id', nullable: false)]
    private Factures $factureId;

    #[ORM\ManyToOne(targetEntity: ActesMedicaux::class)]
    #[ORM\JoinColumn(name: 'acte_id', referencedColumnName: 'id', nullable: false)]
    private ActesMedicaux $acteId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescriptionLigne(): ?string
    {
        return $this->descriptionLigne;
    }

    public function setDescriptionLigne(?string $descriptionLigne): static
    {
        $this->descriptionLigne = $descriptionLigne;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getMontantLigne(): ?string
    {
        return $this->montantLigne;
    }

    public function setMontantLigne(?string $montantLigne): static
    {
        $this->montantLigne = $montantLigne;
        return $this;
    }

    public function getTauxTva(): ?string
    {
        return $this->tauxTva;
    }

    public function setTauxTva(?string $tauxTva): static
    {
        $this->tauxTva = $tauxTva;
        return $this;
    }

    public function getMontantTva(): ?string
    {
        return $this->montantTva;
    }

    public function setMontantTva(?string $montantTva): static
    {
        $this->montantTva = $montantTva;
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

    public function getFactureId(): Factures
    {
        return $this->factureId;
    }

    public function setFactureId(Factures $factureId): static
    {
        $this->factureId = $factureId;
        return $this;
    }

    public function getActeId(): ActesMedicaux
    {
        return $this->acteId;
    }

    public function setActeId(ActesMedicaux $acteId): static
    {
        $this->acteId = $acteId;
        return $this;
    }

}

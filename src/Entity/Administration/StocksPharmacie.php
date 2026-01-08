<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'stocks_pharmacie', indexes: [
        new ORM\Index(name: 'fournisseur_id', columns: ["fournisseur_id"]),
        new ORM\Index(name: 'idx_date_expiration', columns: ["date_expiration"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_medicament', columns: ["medicament_id"])
    ])]
class StocksPharmacie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'integer', precision: 10)]
    private int $quantiteStock = 0;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $quantiteMinimale = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $quantiteMaximale = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $lotNumero = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixAchatUnitaire = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateReception = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $localisationStockage = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Medicaments::class)]
    #[ORM\JoinColumn(name: 'medicament_id', referencedColumnName: 'id', nullable: false)]
    private Medicaments $medicamentId;

    #[ORM\ManyToOne(targetEntity: Fournisseurs::class)]
    #[ORM\JoinColumn(name: 'fournisseur_id', referencedColumnName: 'id', nullable: false)]
    private Fournisseurs $fournisseurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;
        return $this;
    }

    public function getQuantiteMinimale(): ?int
    {
        return $this->quantiteMinimale;
    }

    public function setQuantiteMinimale(?int $quantiteMinimale): static
    {
        $this->quantiteMinimale = $quantiteMinimale;
        return $this;
    }

    public function getQuantiteMaximale(): ?int
    {
        return $this->quantiteMaximale;
    }

    public function setQuantiteMaximale(?int $quantiteMaximale): static
    {
        $this->quantiteMaximale = $quantiteMaximale;
        return $this;
    }

    public function getLotNumero(): ?string
    {
        return $this->lotNumero;
    }

    public function setLotNumero(?string $lotNumero): static
    {
        $this->lotNumero = $lotNumero;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function getPrixAchatUnitaire(): ?string
    {
        return $this->prixAchatUnitaire;
    }

    public function setPrixAchatUnitaire(?string $prixAchatUnitaire): static
    {
        $this->prixAchatUnitaire = $prixAchatUnitaire;
        return $this;
    }

    public function getDateReception(): ?\DateTimeInterface
    {
        return $this->dateReception;
    }

    public function setDateReception(?\DateTimeInterface $dateReception): static
    {
        $this->dateReception = $dateReception;
        return $this;
    }

    public function getLocalisationStockage(): ?string
    {
        return $this->localisationStockage;
    }

    public function setLocalisationStockage(?string $localisationStockage): static
    {
        $this->localisationStockage = $localisationStockage;
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

    public function getMedicamentId(): Medicaments
    {
        return $this->medicamentId;
    }

    public function setMedicamentId(Medicaments $medicamentId): static
    {
        $this->medicamentId = $medicamentId;
        return $this;
    }

    public function getFournisseurId(): Fournisseurs
    {
        return $this->fournisseurId;
    }

    public function setFournisseurId(Fournisseurs $fournisseurId): static
    {
        $this->fournisseurId = $fournisseurId;
        return $this;
    }

}

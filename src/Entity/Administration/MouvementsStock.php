<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'mouvements_stock', indexes: [
        new ORM\Index(name: 'idx_date', columns: ["date_mouvement"]),
        new ORM\Index(name: 'idx_stock', columns: ["stock_id"]),
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"])
    ])]
class MouvementsStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeMouvement = null;

    #[ORM\Column(type: 'integer', precision: 10)]
    private int $quantite = 0;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $referenceDocument = null;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateMouvement;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: StocksPharmacie::class)]
    #[ORM\JoinColumn(name: 'stock_id', referencedColumnName: 'id', nullable: false)]
    private StocksPharmacie $stockId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTypeMouvement(): ?string
    {
        return $this->typeMouvement;
    }

    public function setTypeMouvement(?string $typeMouvement): static
    {
        $this->typeMouvement = $typeMouvement;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getReferenceDocument(): ?string
    {
        return $this->referenceDocument;
    }

    public function setReferenceDocument(?string $referenceDocument): static
    {
        $this->referenceDocument = $referenceDocument;
        return $this;
    }

    public function getDateMouvement(): \DateTimeInterface
    {
        return $this->dateMouvement;
    }

    public function setDateMouvement(\DateTimeInterface $dateMouvement): static
    {
        $this->dateMouvement = $dateMouvement;
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

    public function getStockId(): StocksPharmacie
    {
        return $this->stockId;
    }

    public function setStockId(StocksPharmacie $stockId): static
    {
        $this->stockId = $stockId;
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

}

<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_bons_commande', indexes: [
        new ORM\Index(name: 'bon_commande_id', columns: ["bon_commande_id"]),
        new ORM\Index(name: 'medicament_id', columns: ["medicament_id"])
    ])]
class LignesBonsCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $descriptionArticle = null;

    #[ORM\Column(type: 'integer', precision: 10)]
    private int $quantite = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantLigne = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: BonsCommande::class)]
    #[ORM\JoinColumn(name: 'bon_commande_id', referencedColumnName: 'id', nullable: false)]
    private BonsCommande $bonCommandeId;

    #[ORM\ManyToOne(targetEntity: Medicaments::class)]
    #[ORM\JoinColumn(name: 'medicament_id', referencedColumnName: 'id', nullable: false)]
    private Medicaments $medicamentId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescriptionArticle(): ?string
    {
        return $this->descriptionArticle;
    }

    public function setDescriptionArticle(?string $descriptionArticle): static
    {
        $this->descriptionArticle = $descriptionArticle;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getBonCommandeId(): BonsCommande
    {
        return $this->bonCommandeId;
    }

    public function setBonCommandeId(BonsCommande $bonCommandeId): static
    {
        $this->bonCommandeId = $bonCommandeId;
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

}

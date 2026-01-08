<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_distributions', indexes: [
        new ORM\Index(name: 'distribution_id', columns: ["distribution_id"]),
        new ORM\Index(name: 'medicament_id', columns: ["medicament_id"])
    ])]
class LignesDistributions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'integer', precision: 10)]
    private int $quantite = 0;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $lotNumero = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: DistributionsPharmacie::class)]
    #[ORM\JoinColumn(name: 'distribution_id', referencedColumnName: 'id', nullable: false)]
    private DistributionsPharmacie $distributionId;

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

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
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

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
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

    public function getDistributionId(): DistributionsPharmacie
    {
        return $this->distributionId;
    }

    public function setDistributionId(DistributionsPharmacie $distributionId): static
    {
        $this->distributionId = $distributionId;
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

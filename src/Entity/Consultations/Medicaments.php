<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'medicaments', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'idx_code', columns: ["code_medicament"]),
        new ORM\Index(name: 'idx_nom', columns: ["nom_commercial"]),
        new ORM\Index(name: 'taux_tva_id', columns: ["taux_tva_id"])
    ])]
class Medicaments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeMedicament = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomCommercial = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $nomGenerique = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $formePharmaceutique = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $dosage = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $uniteDosage = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $fabricant = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeAtc = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeCip = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixUnitaire = null;

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

    public function getCodeMedicament(): string
    {
        return $this->codeMedicament;
    }

    public function setCodeMedicament(string $codeMedicament): static
    {
        $this->codeMedicament = $codeMedicament;
        return $this;
    }

    public function getNomCommercial(): string
    {
        return $this->nomCommercial;
    }

    public function setNomCommercial(string $nomCommercial): static
    {
        $this->nomCommercial = $nomCommercial;
        return $this;
    }

    public function getNomGenerique(): ?string
    {
        return $this->nomGenerique;
    }

    public function setNomGenerique(?string $nomGenerique): static
    {
        $this->nomGenerique = $nomGenerique;
        return $this;
    }

    public function getFormePharmaceutique(): ?string
    {
        return $this->formePharmaceutique;
    }

    public function setFormePharmaceutique(?string $formePharmaceutique): static
    {
        $this->formePharmaceutique = $formePharmaceutique;
        return $this;
    }

    public function getDosage(): ?string
    {
        return $this->dosage;
    }

    public function setDosage(?string $dosage): static
    {
        $this->dosage = $dosage;
        return $this;
    }

    public function getUniteDosage(): ?string
    {
        return $this->uniteDosage;
    }

    public function setUniteDosage(?string $uniteDosage): static
    {
        $this->uniteDosage = $uniteDosage;
        return $this;
    }

    public function getFabricant(): ?string
    {
        return $this->fabricant;
    }

    public function setFabricant(?string $fabricant): static
    {
        $this->fabricant = $fabricant;
        return $this;
    }

    public function getCodeAtc(): ?string
    {
        return $this->codeAtc;
    }

    public function setCodeAtc(?string $codeAtc): static
    {
        $this->codeAtc = $codeAtc;
        return $this;
    }

    public function getCodeCip(): ?string
    {
        return $this->codeCip;
    }

    public function setCodeCip(?string $codeCip): static
    {
        $this->codeCip = $codeCip;
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

<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'types_imagerie', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'taux_tva_id', columns: ["taux_tva_id"])
    ])]
class TypesImagerie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeImagerie = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomImagerie = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeModalite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeSnomed = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixExamen = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeExamen = null;

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

    public function getCodeImagerie(): string
    {
        return $this->codeImagerie;
    }

    public function setCodeImagerie(string $codeImagerie): static
    {
        $this->codeImagerie = $codeImagerie;
        return $this;
    }

    public function getNomImagerie(): string
    {
        return $this->nomImagerie;
    }

    public function setNomImagerie(string $nomImagerie): static
    {
        $this->nomImagerie = $nomImagerie;
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

    public function getTypeModalite(): ?string
    {
        return $this->typeModalite;
    }

    public function setTypeModalite(?string $typeModalite): static
    {
        $this->typeModalite = $typeModalite;
        return $this;
    }

    public function getCodeSnomed(): ?string
    {
        return $this->codeSnomed;
    }

    public function setCodeSnomed(?string $codeSnomed): static
    {
        $this->codeSnomed = $codeSnomed;
        return $this;
    }

    public function getPrixExamen(): ?string
    {
        return $this->prixExamen;
    }

    public function setPrixExamen(?string $prixExamen): static
    {
        $this->prixExamen = $prixExamen;
        return $this;
    }

    public function getDureeExamen(): ?int
    {
        return $this->dureeExamen;
    }

    public function setDureeExamen(?int $dureeExamen): static
    {
        $this->dureeExamen = $dureeExamen;
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

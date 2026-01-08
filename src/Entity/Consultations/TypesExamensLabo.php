<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'types_examens_labo', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'idx_code', columns: ["code_examen"]),
        new ORM\Index(name: 'taux_tva_id', columns: ["taux_tva_id"])
    ])]
class TypesExamensLabo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeExamen = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomExamen = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeLoinc = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $codeSnomed = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeSpecimen = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $volumeSpecimen = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $tubePrelevement = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $conditionsConservation = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $delaiResultat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $prixExamen = null;

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

    public function getCodeExamen(): string
    {
        return $this->codeExamen;
    }

    public function setCodeExamen(string $codeExamen): static
    {
        $this->codeExamen = $codeExamen;
        return $this;
    }

    public function getNomExamen(): string
    {
        return $this->nomExamen;
    }

    public function setNomExamen(string $nomExamen): static
    {
        $this->nomExamen = $nomExamen;
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

    public function getCodeLoinc(): ?string
    {
        return $this->codeLoinc;
    }

    public function setCodeLoinc(?string $codeLoinc): static
    {
        $this->codeLoinc = $codeLoinc;
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

    public function getTypeSpecimen(): ?string
    {
        return $this->typeSpecimen;
    }

    public function setTypeSpecimen(?string $typeSpecimen): static
    {
        $this->typeSpecimen = $typeSpecimen;
        return $this;
    }

    public function getVolumeSpecimen(): ?int
    {
        return $this->volumeSpecimen;
    }

    public function setVolumeSpecimen(?int $volumeSpecimen): static
    {
        $this->volumeSpecimen = $volumeSpecimen;
        return $this;
    }

    public function getTubePrelevement(): ?string
    {
        return $this->tubePrelevement;
    }

    public function setTubePrelevement(?string $tubePrelevement): static
    {
        $this->tubePrelevement = $tubePrelevement;
        return $this;
    }

    public function getConditionsConservation(): ?string
    {
        return $this->conditionsConservation;
    }

    public function setConditionsConservation(?string $conditionsConservation): static
    {
        $this->conditionsConservation = $conditionsConservation;
        return $this;
    }

    public function getDelaiResultat(): ?int
    {
        return $this->delaiResultat;
    }

    public function setDelaiResultat(?int $delaiResultat): static
    {
        $this->delaiResultat = $delaiResultat;
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

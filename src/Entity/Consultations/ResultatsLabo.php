<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'resultats_labo', indexes: [
        new ORM\Index(name: 'idx_examen', columns: ["examen_id"]),
        new ORM\Index(name: 'idx_prelevement', columns: ["prelevement_id"]),
        new ORM\Index(name: 'technicien_id', columns: ["technicien_id"]),
        new ORM\Index(name: 'validateur_id', columns: ["validateur_id"])
    ])]
class ResultatsLabo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $valeurResultat = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $uniteResultat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $valeurReferenceMin = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $valeurReferenceMax = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statutResultat = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $interpretation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateAnalyse = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Prelevements::class)]
    #[ORM\JoinColumn(name: 'prelevement_id', referencedColumnName: 'id', nullable: false)]
    private Prelevements $prelevementId;

    #[ORM\ManyToOne(targetEntity: TypesExamensLabo::class)]
    #[ORM\JoinColumn(name: 'examen_id', referencedColumnName: 'id', nullable: false)]
    private TypesExamensLabo $examenId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'technicien_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $technicienId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'validateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $validateurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValeurResultat(): ?string
    {
        return $this->valeurResultat;
    }

    public function setValeurResultat(?string $valeurResultat): static
    {
        $this->valeurResultat = $valeurResultat;
        return $this;
    }

    public function getUniteResultat(): ?string
    {
        return $this->uniteResultat;
    }

    public function setUniteResultat(?string $uniteResultat): static
    {
        $this->uniteResultat = $uniteResultat;
        return $this;
    }

    public function getValeurReferenceMin(): ?string
    {
        return $this->valeurReferenceMin;
    }

    public function setValeurReferenceMin(?string $valeurReferenceMin): static
    {
        $this->valeurReferenceMin = $valeurReferenceMin;
        return $this;
    }

    public function getValeurReferenceMax(): ?string
    {
        return $this->valeurReferenceMax;
    }

    public function setValeurReferenceMax(?string $valeurReferenceMax): static
    {
        $this->valeurReferenceMax = $valeurReferenceMax;
        return $this;
    }

    public function getStatutResultat(): ?string
    {
        return $this->statutResultat;
    }

    public function setStatutResultat(?string $statutResultat): static
    {
        $this->statutResultat = $statutResultat;
        return $this;
    }

    public function getInterpretation(): ?string
    {
        return $this->interpretation;
    }

    public function setInterpretation(?string $interpretation): static
    {
        $this->interpretation = $interpretation;
        return $this;
    }

    public function getDateAnalyse(): ?\DateTimeInterface
    {
        return $this->dateAnalyse;
    }

    public function setDateAnalyse(?\DateTimeInterface $dateAnalyse): static
    {
        $this->dateAnalyse = $dateAnalyse;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
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

    public function getPrelevementId(): Prelevements
    {
        return $this->prelevementId;
    }

    public function setPrelevementId(Prelevements $prelevementId): static
    {
        $this->prelevementId = $prelevementId;
        return $this;
    }

    public function getExamenId(): TypesExamensLabo
    {
        return $this->examenId;
    }

    public function setExamenId(TypesExamensLabo $examenId): static
    {
        $this->examenId = $examenId;
        return $this;
    }

    public function getTechnicienId(): Utilisateurs
    {
        return $this->technicienId;
    }

    public function setTechnicienId(Utilisateurs $technicienId): static
    {
        $this->technicienId = $technicienId;
        return $this;
    }

    public function getValidateurId(): Utilisateurs
    {
        return $this->validateurId;
    }

    public function setValidateurId(Utilisateurs $validateurId): static
    {
        $this->validateurId = $validateurId;
        return $this;
    }

}

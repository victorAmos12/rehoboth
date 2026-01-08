<?php

namespace App\Entity\Consultations;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_ordonnances_labo', indexes: [
        new ORM\Index(name: 'examen_id', columns: ["examen_id"]),
        new ORM\Index(name: 'ordonnance_id', columns: ["ordonnance_id"]),
        new ORM\Index(name: 'panel_id', columns: ["panel_id"])
    ])]
class LignesOrdonnancesLabo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $priorite = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: OrdonnancesLabo::class)]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'id', nullable: false)]
    private OrdonnancesLabo $ordonnanceId;

    #[ORM\ManyToOne(targetEntity: TypesExamensLabo::class)]
    #[ORM\JoinColumn(name: 'examen_id', referencedColumnName: 'id', nullable: false)]
    private TypesExamensLabo $examenId;

    #[ORM\ManyToOne(targetEntity: PanelsExamens::class)]
    #[ORM\JoinColumn(name: 'panel_id', referencedColumnName: 'id', nullable: false)]
    private PanelsExamens $panelId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): static
    {
        $this->priorite = $priorite;
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

    public function getOrdonnanceId(): OrdonnancesLabo
    {
        return $this->ordonnanceId;
    }

    public function setOrdonnanceId(OrdonnancesLabo $ordonnanceId): static
    {
        $this->ordonnanceId = $ordonnanceId;
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

    public function getPanelId(): PanelsExamens
    {
        return $this->panelId;
    }

    public function setPanelId(PanelsExamens $panelId): static
    {
        $this->panelId = $panelId;
        return $this;
    }

}

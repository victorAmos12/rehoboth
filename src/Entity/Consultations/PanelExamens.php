<?php

namespace App\Entity\Consultations;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'panel_examens', indexes: [
        new ORM\Index(name: 'examen_id', columns: ["examen_id"]),
        new ORM\Index(name: 'IDX_2AC509186F6FCB26', columns: ["panel_id"])
    ])]
class PanelExamens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $ordre = null;

    #[ORM\ManyToOne(targetEntity: PanelsExamens::class)]
    #[ORM\JoinColumn(name: 'panel_id', referencedColumnName: 'id', nullable: false)]
    private PanelsExamens $panelId;

    #[ORM\ManyToOne(targetEntity: TypesExamensLabo::class)]
    #[ORM\JoinColumn(name: 'examen_id', referencedColumnName: 'id', nullable: false)]
    private TypesExamensLabo $examenId;

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;
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

    public function getExamenId(): TypesExamensLabo
    {
        return $this->examenId;
    }

    public function setExamenId(TypesExamensLabo $examenId): static
    {
        $this->examenId = $examenId;
        return $this;
    }

}

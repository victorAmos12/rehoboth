<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'interactions_medicamenteuses', indexes: [
        new ORM\Index(name: 'medicament_2_id', columns: ["medicament_2_id"]),
        new ORM\Index(name: 'IDX_347FE00AF8F6C7D5', columns: ["medicament_1_id"])
    ])]
class InteractionsMedicamenteuses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $niveauSeverite = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $recommandation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Medicaments::class)]
    #[ORM\JoinColumn(name: 'medicament_1_id', referencedColumnName: 'id', nullable: false)]
    private Medicaments $medicament1Id;

    #[ORM\ManyToOne(targetEntity: Medicaments::class)]
    #[ORM\JoinColumn(name: 'medicament_2_id', referencedColumnName: 'id', nullable: false)]
    private Medicaments $medicament2Id;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNiveauSeverite(): ?string
    {
        return $this->niveauSeverite;
    }

    public function setNiveauSeverite(?string $niveauSeverite): static
    {
        $this->niveauSeverite = $niveauSeverite;
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

    public function getRecommandation(): ?string
    {
        return $this->recommandation;
    }

    public function setRecommandation(?string $recommandation): static
    {
        $this->recommandation = $recommandation;
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

    public function getMedicament1Id(): Medicaments
    {
        return $this->medicament1Id;
    }

    public function setMedicament1Id(Medicaments $medicament1Id): static
    {
        $this->medicament1Id = $medicament1Id;
        return $this;
    }

    public function getMedicament2Id(): Medicaments
    {
        return $this->medicament2Id;
    }

    public function setMedicament2Id(Medicaments $medicament2Id): static
    {
        $this->medicament2Id = $medicament2Id;
        return $this;
    }

}

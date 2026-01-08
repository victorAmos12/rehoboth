<?php

namespace App\Entity\Consultations;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_ordonnances_imagerie', indexes: [
        new ORM\Index(name: 'imagerie_id', columns: ["imagerie_id"]),
        new ORM\Index(name: 'ordonnance_id', columns: ["ordonnance_id"])
    ])]
class LignesOrdonnancesImagerie
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

    #[ORM\ManyToOne(targetEntity: OrdonnancesImagerie::class)]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'id', nullable: false)]
    private OrdonnancesImagerie $ordonnanceId;

    #[ORM\ManyToOne(targetEntity: TypesImagerie::class)]
    #[ORM\JoinColumn(name: 'imagerie_id', referencedColumnName: 'id', nullable: false)]
    private TypesImagerie $imagerieId;

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

    public function getOrdonnanceId(): OrdonnancesImagerie
    {
        return $this->ordonnanceId;
    }

    public function setOrdonnanceId(OrdonnancesImagerie $ordonnanceId): static
    {
        $this->ordonnanceId = $ordonnanceId;
        return $this;
    }

    public function getImagerieId(): TypesImagerie
    {
        return $this->imagerieId;
    }

    public function setImagerieId(TypesImagerie $imagerieId): static
    {
        $this->imagerieId = $imagerieId;
        return $this;
    }

}

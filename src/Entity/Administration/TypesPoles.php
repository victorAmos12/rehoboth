<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * Entité pour les types de pôles d'activité
 * 
 * Définit les catégories de pôles (Urgences, Médecine, Chirurgie, Support, etc.)
 */
#[ORM\Entity]
#[ORM\Table(name: 'types_poles', indexes: [
        new ORM\Index(name: 'idx_code', columns: ["code"])
    ])]
class TypesPoles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = true;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(targetEntity: PolesActivite::class, mappedBy: 'typePoleId')]
    private Collection $poles;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->poles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function isActif(): ?bool
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

    public function getPoles(): Collection
    {
        return $this->poles;
    }

    public function addPole(PolesActivite $pole): static
    {
        if (!$this->poles->contains($pole)) {
            $this->poles->add($pole);
            $pole->setTypePoleId($this);
        }
        return $this;
    }

    public function removePole(PolesActivite $pole): static
    {
        if ($this->poles->removeElement($pole)) {
            if ($pole->getTypePoleId() === $this) {
                $pole->setTypePoleId(null);
            }
        }
        return $this;
    }
}

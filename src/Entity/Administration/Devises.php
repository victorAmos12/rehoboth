<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'devises', indexes: [
        new ORM\Index(name: 'idx_code', columns: ["code"])
    ])]
class Devises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 3, precision: 10)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 100, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'string', length: 10, precision: 10, nullable: true)]
    private ?string $symbole = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, nullable: true)]
    private ?string $tauxChange = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $deviseReference = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->actif = true;
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

    public function getSymbole(): ?string
    {
        return $this->symbole;
    }

    public function setSymbole(?string $symbole): static
    {
        $this->symbole = $symbole;
        return $this;
    }

    public function getTauxChange(): ?string
    {
        return $this->tauxChange;
    }

    public function setTauxChange(?string $tauxChange): static
    {
        $this->tauxChange = $tauxChange;
        return $this;
    }

    public function getDeviseReference(): ?bool
    {
        return $this->deviseReference;
    }

    public function setDeviseReference(?bool $deviseReference): static
    {
        $this->deviseReference = $deviseReference;
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

    public function isDeviseReference(): ?bool
    {
        return $this->deviseReference;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

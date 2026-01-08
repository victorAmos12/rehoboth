<?php

namespace App\Entity\Administration;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'hopitaux', indexes: [
        new ORM\Index(name: 'idx_actif', columns: ["actif"]),
        new ORM\Index(name: 'idx_code', columns: ["code"])
    ])]
class Hopitaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 10, precision: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $directeurId = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeHopital = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $nombreLits = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $iconeUrl = null;

    #[ORM\Column(type: 'string', length: 7, precision: 10, nullable: true)]
    private ?string $couleurPrimaire = null;

    #[ORM\Column(type: 'string', length: 7, precision: 10, nullable: true)]
    private ?string $couleurSecondaire = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $siteWeb = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $numeroSiret = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $numeroTva = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getDirecteurId(): ?int
    {
        return $this->directeurId;
    }

    public function setDirecteurId(?int $directeurId): static
    {
        $this->directeurId = $directeurId;
        return $this;
    }

    public function getTypeHopital(): ?string
    {
        return $this->typeHopital;
    }

    public function setTypeHopital(?string $typeHopital): static
    {
        $this->typeHopital = $typeHopital;
        return $this;
    }

    public function getNombreLits(): ?int
    {
        return $this->nombreLits;
    }

    public function setNombreLits(?int $nombreLits): static
    {
        $this->nombreLits = $nombreLits;
        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;
        return $this;
    }

    public function getIconeUrl(): ?string
    {
        return $this->iconeUrl;
    }

    public function setIconeUrl(?string $iconeUrl): static
    {
        $this->iconeUrl = $iconeUrl;
        return $this;
    }

    public function getCouleurPrimaire(): ?string
    {
        return $this->couleurPrimaire;
    }

    public function setCouleurPrimaire(?string $couleurPrimaire): static
    {
        $this->couleurPrimaire = $couleurPrimaire;
        return $this;
    }

    public function getCouleurSecondaire(): ?string
    {
        return $this->couleurSecondaire;
    }

    public function setCouleurSecondaire(?string $couleurSecondaire): static
    {
        $this->couleurSecondaire = $couleurSecondaire;
        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;
        return $this;
    }

    public function getNumeroSiret(): ?string
    {
        return $this->numeroSiret;
    }

    public function setNumeroSiret(?string $numeroSiret): static
    {
        $this->numeroSiret = $numeroSiret;
        return $this;
    }

    public function getNumeroTva(): ?string
    {
        return $this->numeroTva;
    }

    public function setNumeroTva(?string $numeroTva): static
    {
        $this->numeroTva = $numeroTva;
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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

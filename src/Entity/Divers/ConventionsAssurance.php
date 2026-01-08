<?php

namespace App\Entity\Divers;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'conventions_assurance', indexes: [
        new ORM\Index(name: 'IDX_1B306058CC0FBF92', columns: ["hopital_id"])
    ])]
class ConventionsAssurance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeCouverture = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $pourcentageCouverture = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $contactPersonne = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroConvention = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxCommission = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

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

    public function getTypeCouverture(): ?string
    {
        return $this->typeCouverture;
    }

    public function setTypeCouverture(?string $typeCouverture): static
    {
        $this->typeCouverture = $typeCouverture;
        return $this;
    }

    public function getPourcentageCouverture(): ?string
    {
        return $this->pourcentageCouverture;
    }

    public function setPourcentageCouverture(?string $pourcentageCouverture): static
    {
        $this->pourcentageCouverture = $pourcentageCouverture;
        return $this;
    }

    public function getContactPersonne(): ?string
    {
        return $this->contactPersonne;
    }

    public function setContactPersonne(?string $contactPersonne): static
    {
        $this->contactPersonne = $contactPersonne;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
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

    public function getNumeroConvention(): ?string
    {
        return $this->numeroConvention;
    }

    public function setNumeroConvention(?string $numeroConvention): static
    {
        $this->numeroConvention = $numeroConvention;
        return $this;
    }

    public function getTauxCommission(): ?string
    {
        return $this->tauxCommission;
    }

    public function setTauxCommission(?string $tauxCommission): static
    {
        $this->tauxCommission = $tauxCommission;
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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

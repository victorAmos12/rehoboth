<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'services', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_type', columns: ["type_service"])
    ])]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeService = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $chefServiceId = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $nombreLits = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $logoService = null;

    #[ORM\Column(type: 'string', length: 7, precision: 10, nullable: true)]
    private ?string $couleurService = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $budgetAnnuel = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombrePersonnel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $horairesOuverture = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $niveauAccreditation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Administration\PolesActivite', inversedBy: 'services')]
    #[ORM\JoinColumn(name: 'pole_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?PolesActivite $poleId = null;

    #[ORM\ManyToOne(targetEntity: TypesServices::class, inversedBy: 'services')]
    #[ORM\JoinColumn(name: 'type_service_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TypesServices $typeServiceId = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeService(): ?string
    {
        return $this->typeService;
    }

    public function setTypeService(?string $typeService): static
    {
        $this->typeService = $typeService;
        return $this;
    }

    public function getChefServiceId(): ?int
    {
        return $this->chefServiceId;
    }

    public function setChefServiceId(?int $chefServiceId): static
    {
        $this->chefServiceId = $chefServiceId;
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

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;
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

    public function getLogoService(): ?string
    {
        return $this->logoService;
    }

    public function setLogoService(?string $logoService): static
    {
        $this->logoService = $logoService;
        return $this;
    }

    public function getCouleurService(): ?string
    {
        return $this->couleurService;
    }

    public function setCouleurService(?string $couleurService): static
    {
        $this->couleurService = $couleurService;
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

    public function getBudgetAnnuel(): ?string
    {
        return $this->budgetAnnuel;
    }

    public function setBudgetAnnuel(?string $budgetAnnuel): static
    {
        $this->budgetAnnuel = $budgetAnnuel;
        return $this;
    }

    public function getNombrePersonnel(): ?int
    {
        return $this->nombrePersonnel;
    }

    public function setNombrePersonnel(?int $nombrePersonnel): static
    {
        $this->nombrePersonnel = $nombrePersonnel;
        return $this;
    }

    public function getHorairesOuverture(): ?string
    {
        return $this->horairesOuverture;
    }

    public function setHorairesOuverture(?string $horairesOuverture): static
    {
        $this->horairesOuverture = $horairesOuverture;
        return $this;
    }

    public function getNiveauAccreditation(): ?string
    {
        return $this->niveauAccreditation;
    }

    public function setNiveauAccreditation(?string $niveauAccreditation): static
    {
        $this->niveauAccreditation = $niveauAccreditation;
        return $this;
    }

    public function getPoleId(): ?PolesActivite
    {
        return $this->poleId;
    }

    public function setPoleId(?PolesActivite $poleId): static
    {
        $this->poleId = $poleId;
        return $this;
    }

    public function getTypeServiceId(): ?TypesServices
    {
        return $this->typeServiceId;
    }

    public function setTypeServiceId(?TypesServices $typeServiceId): static
    {
        $this->typeServiceId = $typeServiceId;
        return $this;
    }

}

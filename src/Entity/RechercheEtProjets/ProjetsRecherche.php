<?php

namespace App\Entity\RechercheEtProjets;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'projets_recherche', indexes: [
        new ORM\Index(name: 'chercheur_principal_id', columns: ["chercheur_principal_id"]),
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'IDX_F4A04F61CC0FBF92', columns: ["hopital_id"])
    ])]
class ProjetsRecherche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeProjet = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $titreProjet = '';

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $descriptionProjet = null;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $budgetProjet = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $comiteEthiqueApprouve = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateApprobationEthique = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'chercheur_principal_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $chercheurPrincipalId;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: 'devise_id', referencedColumnName: 'id', nullable: false)]
    private Devises $deviseId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCodeProjet(): string
    {
        return $this->codeProjet;
    }

    public function setCodeProjet(string $codeProjet): static
    {
        $this->codeProjet = $codeProjet;
        return $this;
    }

    public function getTitreProjet(): string
    {
        return $this->titreProjet;
    }

    public function setTitreProjet(string $titreProjet): static
    {
        $this->titreProjet = $titreProjet;
        return $this;
    }

    public function getDescriptionProjet(): ?string
    {
        return $this->descriptionProjet;
    }

    public function setDescriptionProjet(?string $descriptionProjet): static
    {
        $this->descriptionProjet = $descriptionProjet;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
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

    public function getBudgetProjet(): ?string
    {
        return $this->budgetProjet;
    }

    public function setBudgetProjet(?string $budgetProjet): static
    {
        $this->budgetProjet = $budgetProjet;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getComiteEthiqueApprouve(): ?bool
    {
        return $this->comiteEthiqueApprouve;
    }

    public function setComiteEthiqueApprouve(?bool $comiteEthiqueApprouve): static
    {
        $this->comiteEthiqueApprouve = $comiteEthiqueApprouve;
        return $this;
    }

    public function getDateApprobationEthique(): ?\DateTimeInterface
    {
        return $this->dateApprobationEthique;
    }

    public function setDateApprobationEthique(?\DateTimeInterface $dateApprobationEthique): static
    {
        $this->dateApprobationEthique = $dateApprobationEthique;
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

    public function getChercheurPrincipalId(): Utilisateurs
    {
        return $this->chercheurPrincipalId;
    }

    public function setChercheurPrincipalId(Utilisateurs $chercheurPrincipalId): static
    {
        $this->chercheurPrincipalId = $chercheurPrincipalId;
        return $this;
    }

    public function getDeviseId(): Devises
    {
        return $this->deviseId;
    }

    public function setDeviseId(Devises $deviseId): static
    {
        $this->deviseId = $deviseId;
        return $this;
    }

    public function isComiteEthiqueApprouve(): ?bool
    {
        return $this->comiteEthiqueApprouve;
    }

}

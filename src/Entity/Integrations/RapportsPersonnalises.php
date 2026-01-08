<?php

namespace App\Entity\Integrations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'rapports_personnalises', indexes: [
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_utilisateur', columns: ["utilisateur_id"])
    ])]
class RapportsPersonnalises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomRapport = '';

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $descriptionRapport = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeRapport = null;

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $configurationRapport = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $frequenceGeneration = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDerniereGeneration = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNomRapport(): string
    {
        return $this->nomRapport;
    }

    public function setNomRapport(string $nomRapport): static
    {
        $this->nomRapport = $nomRapport;
        return $this;
    }

    public function getDescriptionRapport(): ?string
    {
        return $this->descriptionRapport;
    }

    public function setDescriptionRapport(?string $descriptionRapport): static
    {
        $this->descriptionRapport = $descriptionRapport;
        return $this;
    }

    public function getTypeRapport(): ?string
    {
        return $this->typeRapport;
    }

    public function setTypeRapport(?string $typeRapport): static
    {
        $this->typeRapport = $typeRapport;
        return $this;
    }

    public function getConfigurationRapport(): ?string
    {
        return $this->configurationRapport;
    }

    public function setConfigurationRapport(?string $configurationRapport): static
    {
        $this->configurationRapport = $configurationRapport;
        return $this;
    }

    public function getFrequenceGeneration(): ?string
    {
        return $this->frequenceGeneration;
    }

    public function setFrequenceGeneration(?string $frequenceGeneration): static
    {
        $this->frequenceGeneration = $frequenceGeneration;
        return $this;
    }

    public function getDateDerniereGeneration(): ?\DateTimeInterface
    {
        return $this->dateDerniereGeneration;
    }

    public function setDateDerniereGeneration(?\DateTimeInterface $dateDerniereGeneration): static
    {
        $this->dateDerniereGeneration = $dateDerniereGeneration;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

}

<?php

namespace App\Entity\ComptabiliteFacturation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'reclamations_assurance', indexes: [
        new ORM\Index(name: 'convention_id', columns: ["convention_id"]),
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'facture_id', columns: ["facture_id"]),
        new ORM\Index(name: 'IDX_C1A366F2CC0FBF92', columns: ["hopital_id"])
    ])]
class ReclamationsAssurance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroReclamation = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateReclamation;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantReclame = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifRejet = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateReponse = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantAccepte = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Factures::class)]
    #[ORM\JoinColumn(name: 'facture_id', referencedColumnName: 'id', nullable: false)]
    private Factures $factureId;

    #[ORM\ManyToOne(targetEntity: ConventionsAssurance::class)]
    #[ORM\JoinColumn(name: 'convention_id', referencedColumnName: 'id', nullable: false)]
    private ConventionsAssurance $conventionId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

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

    public function getNumeroReclamation(): string
    {
        return $this->numeroReclamation;
    }

    public function setNumeroReclamation(string $numeroReclamation): static
    {
        $this->numeroReclamation = $numeroReclamation;
        return $this;
    }

    public function getDateReclamation(): \DateTimeInterface
    {
        return $this->dateReclamation;
    }

    public function setDateReclamation(\DateTimeInterface $dateReclamation): static
    {
        $this->dateReclamation = $dateReclamation;
        return $this;
    }

    public function getMontantReclame(): ?string
    {
        return $this->montantReclame;
    }

    public function setMontantReclame(?string $montantReclame): static
    {
        $this->montantReclame = $montantReclame;
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

    public function getMotifRejet(): ?string
    {
        return $this->motifRejet;
    }

    public function setMotifRejet(?string $motifRejet): static
    {
        $this->motifRejet = $motifRejet;
        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(?\DateTimeInterface $dateReponse): static
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }

    public function getMontantAccepte(): ?string
    {
        return $this->montantAccepte;
    }

    public function setMontantAccepte(?string $montantAccepte): static
    {
        $this->montantAccepte = $montantAccepte;
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

    public function getFactureId(): Factures
    {
        return $this->factureId;
    }

    public function setFactureId(Factures $factureId): static
    {
        $this->factureId = $factureId;
        return $this;
    }

    public function getConventionId(): ConventionsAssurance
    {
        return $this->conventionId;
    }

    public function setConventionId(ConventionsAssurance $conventionId): static
    {
        $this->conventionId = $conventionId;
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

    public function getDeviseId(): Devises
    {
        return $this->deviseId;
    }

    public function setDeviseId(Devises $deviseId): static
    {
        $this->deviseId = $deviseId;
        return $this;
    }

}

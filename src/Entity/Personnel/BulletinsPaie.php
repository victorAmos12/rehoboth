<?php

namespace App\Entity\Personnel;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'bulletins_paie', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'idx_utilisateur', columns: ["utilisateur_id"]),
        new ORM\Index(name: 'IDX_7ED16AA0CC0FBF92', columns: ["hopital_id"])
    ])]
class BulletinsPaie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroBulletin = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $periodeDebut;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $periodeFin;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $salaireBrut = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $cotisationsSociales = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $impotRevenu = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $autresDeductions = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $salaireNet = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

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

    public function getNumeroBulletin(): string
    {
        return $this->numeroBulletin;
    }

    public function setNumeroBulletin(string $numeroBulletin): static
    {
        $this->numeroBulletin = $numeroBulletin;
        return $this;
    }

    public function getPeriodeDebut(): \DateTimeInterface
    {
        return $this->periodeDebut;
    }

    public function setPeriodeDebut(\DateTimeInterface $periodeDebut): static
    {
        $this->periodeDebut = $periodeDebut;
        return $this;
    }

    public function getPeriodeFin(): \DateTimeInterface
    {
        return $this->periodeFin;
    }

    public function setPeriodeFin(\DateTimeInterface $periodeFin): static
    {
        $this->periodeFin = $periodeFin;
        return $this;
    }

    public function getSalaireBrut(): ?string
    {
        return $this->salaireBrut;
    }

    public function setSalaireBrut(?string $salaireBrut): static
    {
        $this->salaireBrut = $salaireBrut;
        return $this;
    }

    public function getCotisationsSociales(): ?string
    {
        return $this->cotisationsSociales;
    }

    public function setCotisationsSociales(?string $cotisationsSociales): static
    {
        $this->cotisationsSociales = $cotisationsSociales;
        return $this;
    }

    public function getImpotRevenu(): ?string
    {
        return $this->impotRevenu;
    }

    public function setImpotRevenu(?string $impotRevenu): static
    {
        $this->impotRevenu = $impotRevenu;
        return $this;
    }

    public function getAutresDeductions(): ?string
    {
        return $this->autresDeductions;
    }

    public function setAutresDeductions(?string $autresDeductions): static
    {
        $this->autresDeductions = $autresDeductions;
        return $this;
    }

    public function getSalaireNet(): ?string
    {
        return $this->salaireNet;
    }

    public function setSalaireNet(?string $salaireNet): static
    {
        $this->salaireNet = $salaireNet;
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

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
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

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
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

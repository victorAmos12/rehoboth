<?php

namespace App\Entity\Administration;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'sauvegardes', indexes: [
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"]),
        new ORM\Index(name: 'IDX_D52A420FCC0FBF92', columns: ["hopital_id"])
    ])]
class Sauvegardes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroSauvegarde = '';

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateSauvegarde;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeSauvegarde = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $tailleSauvegarde = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $localisationSauvegarde = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeSauvegarde = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroSauvegarde(): string
    {
        return $this->numeroSauvegarde;
    }

    public function setNumeroSauvegarde(string $numeroSauvegarde): static
    {
        $this->numeroSauvegarde = $numeroSauvegarde;
        return $this;
    }

    public function getDateSauvegarde(): \DateTimeInterface
    {
        return $this->dateSauvegarde;
    }

    public function setDateSauvegarde(\DateTimeInterface $dateSauvegarde): static
    {
        $this->dateSauvegarde = $dateSauvegarde;
        return $this;
    }

    public function getTypeSauvegarde(): ?string
    {
        return $this->typeSauvegarde;
    }

    public function setTypeSauvegarde(?string $typeSauvegarde): static
    {
        $this->typeSauvegarde = $typeSauvegarde;
        return $this;
    }

    public function getTailleSauvegarde(): ?int
    {
        return $this->tailleSauvegarde;
    }

    public function setTailleSauvegarde(?int $tailleSauvegarde): static
    {
        $this->tailleSauvegarde = $tailleSauvegarde;
        return $this;
    }

    public function getLocalisationSauvegarde(): ?string
    {
        return $this->localisationSauvegarde;
    }

    public function setLocalisationSauvegarde(?string $localisationSauvegarde): static
    {
        $this->localisationSauvegarde = $localisationSauvegarde;
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

    public function getDureeSauvegarde(): ?int
    {
        return $this->dureeSauvegarde;
    }

    public function setDureeSauvegarde(?int $dureeSauvegarde): static
    {
        $this->dureeSauvegarde = $dureeSauvegarde;
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

<?php

namespace App\Entity\RechercheEtProjets;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'mesures_indicateurs', indexes: [
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_mesure"]),
        new ORM\Index(name: 'idx_indicateur', columns: ["indicateur_id"]),
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"])
    ])]
class MesuresIndicateurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $valeurMesuree = null;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateMesure;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesMesure = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: IndicateursQualite::class)]
    #[ORM\JoinColumn(name: 'indicateur_id', referencedColumnName: 'id', nullable: false)]
    private IndicateursQualite $indicateurId;

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

    public function getValeurMesuree(): ?string
    {
        return $this->valeurMesuree;
    }

    public function setValeurMesuree(?string $valeurMesuree): static
    {
        $this->valeurMesuree = $valeurMesuree;
        return $this;
    }

    public function getDateMesure(): \DateTimeInterface
    {
        return $this->dateMesure;
    }

    public function setDateMesure(\DateTimeInterface $dateMesure): static
    {
        $this->dateMesure = $dateMesure;
        return $this;
    }

    public function getNotesMesure(): ?string
    {
        return $this->notesMesure;
    }

    public function setNotesMesure(?string $notesMesure): static
    {
        $this->notesMesure = $notesMesure;
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

    public function getIndicateurId(): IndicateursQualite
    {
        return $this->indicateurId;
    }

    public function setIndicateurId(IndicateursQualite $indicateurId): static
    {
        $this->indicateurId = $indicateurId;
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

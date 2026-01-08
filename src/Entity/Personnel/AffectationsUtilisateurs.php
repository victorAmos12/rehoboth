<?php

namespace App\Entity\Personnel;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;
use App\Entity\Administration\Services;

#[ORM\Entity]
#[ORM\Table(name: 'affectations_utilisateurs', indexes: [
        new ORM\Index(name: 'service_id', columns: ["service_id"]),
        new ORM\Index(name: 'IDX_AB94D8BFFB88E14F', columns: ["utilisateur_id"])
    ])]
class AffectationsUtilisateurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $pourcentageTemps = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getPourcentageTemps(): ?string
    {
        return $this->pourcentageTemps;
    }

    public function setPourcentageTemps(?string $pourcentageTemps): static
    {
        $this->pourcentageTemps = $pourcentageTemps;
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

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function getServiceId(): Services
    {
        return $this->serviceId;
    }

    public function setServiceId(Services $serviceId): static
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

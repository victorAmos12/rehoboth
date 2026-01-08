<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'creneaux_consultation', indexes: [
        new ORM\Index(name: 'idx_date', columns: ["date_consultation"]),
        new ORM\Index(name: 'idx_medecin', columns: ["medecin_id"]),
        new ORM\Index(name: 'idx_service', columns: ["service_id"])
    ])]
class CreneauxConsultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateConsultation;

    #[ORM\Column(type: 'time', precision: 10)]
    private \DateTimeInterface $heureDebut;

    #[ORM\Column(type: 'time', precision: 10)]
    private \DateTimeInterface $heureFin;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $nombrePlaces = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $placesDisponibles = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeConsultation = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $medecinId;

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

    public function getDateConsultation(): \DateTimeInterface
    {
        return $this->dateConsultation;
    }

    public function setDateConsultation(\DateTimeInterface $dateConsultation): static
    {
        $this->dateConsultation = $dateConsultation;
        return $this;
    }

    public function getHeureDebut(): \DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): \DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(?int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;
        return $this;
    }

    public function getPlacesDisponibles(): ?int
    {
        return $this->placesDisponibles;
    }

    public function setPlacesDisponibles(?int $placesDisponibles): static
    {
        $this->placesDisponibles = $placesDisponibles;
        return $this;
    }

    public function getTypeConsultation(): ?string
    {
        return $this->typeConsultation;
    }

    public function setTypeConsultation(?string $typeConsultation): static
    {
        $this->typeConsultation = $typeConsultation;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
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

    public function getMedecinId(): Utilisateurs
    {
        return $this->medecinId;
    }

    public function setMedecinId(Utilisateurs $medecinId): static
    {
        $this->medecinId = $medecinId;
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

<?php

namespace App\Entity\Integrations;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'integrations_externes', indexes: [
        new ORM\Index(name: 'IDX_8A5A1ED4CC0FBF92', columns: ["hopital_id"])
    ])]
class IntegrationsExternes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $codeIntegration = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nomIntegration = '';

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $typeIntegration = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $urlEndpoint = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $authentificationType = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $derniereSynchronisation = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $frequenceSynchronisation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCodeIntegration(): string
    {
        return $this->codeIntegration;
    }

    public function setCodeIntegration(string $codeIntegration): static
    {
        $this->codeIntegration = $codeIntegration;
        return $this;
    }

    public function getNomIntegration(): string
    {
        return $this->nomIntegration;
    }

    public function setNomIntegration(string $nomIntegration): static
    {
        $this->nomIntegration = $nomIntegration;
        return $this;
    }

    public function getTypeIntegration(): ?string
    {
        return $this->typeIntegration;
    }

    public function setTypeIntegration(?string $typeIntegration): static
    {
        $this->typeIntegration = $typeIntegration;
        return $this;
    }

    public function getUrlEndpoint(): ?string
    {
        return $this->urlEndpoint;
    }

    public function setUrlEndpoint(?string $urlEndpoint): static
    {
        $this->urlEndpoint = $urlEndpoint;
        return $this;
    }

    public function getAuthentificationType(): ?string
    {
        return $this->authentificationType;
    }

    public function setAuthentificationType(?string $authentificationType): static
    {
        $this->authentificationType = $authentificationType;
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

    public function getDerniereSynchronisation(): ?\DateTimeInterface
    {
        return $this->derniereSynchronisation;
    }

    public function setDerniereSynchronisation(?\DateTimeInterface $derniereSynchronisation): static
    {
        $this->derniereSynchronisation = $derniereSynchronisation;
        return $this;
    }

    public function getFrequenceSynchronisation(): ?string
    {
        return $this->frequenceSynchronisation;
    }

    public function setFrequenceSynchronisation(?string $frequenceSynchronisation): static
    {
        $this->frequenceSynchronisation = $frequenceSynchronisation;
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

}

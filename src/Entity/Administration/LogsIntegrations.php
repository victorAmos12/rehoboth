<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'logs_integrations', indexes: [
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_log"]),
        new ORM\Index(name: 'idx_integration', columns: ["integration_id"])
    ])]
class LogsIntegrations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateLog;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeLog = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $messageLog = null;

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $donneesEnvoyees = null;

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $donneesRecues = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $statutReponse = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: IntegrationsExternes::class)]
    #[ORM\JoinColumn(name: 'integration_id', referencedColumnName: 'id', nullable: false)]
    private IntegrationsExternes $integrationId;

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

    public function getDateLog(): \DateTimeInterface
    {
        return $this->dateLog;
    }

    public function setDateLog(\DateTimeInterface $dateLog): static
    {
        $this->dateLog = $dateLog;
        return $this;
    }

    public function getTypeLog(): ?string
    {
        return $this->typeLog;
    }

    public function setTypeLog(?string $typeLog): static
    {
        $this->typeLog = $typeLog;
        return $this;
    }

    public function getMessageLog(): ?string
    {
        return $this->messageLog;
    }

    public function setMessageLog(?string $messageLog): static
    {
        $this->messageLog = $messageLog;
        return $this;
    }

    public function getDonneesEnvoyees(): ?string
    {
        return $this->donneesEnvoyees;
    }

    public function setDonneesEnvoyees(?string $donneesEnvoyees): static
    {
        $this->donneesEnvoyees = $donneesEnvoyees;
        return $this;
    }

    public function getDonneesRecues(): ?string
    {
        return $this->donneesRecues;
    }

    public function setDonneesRecues(?string $donneesRecues): static
    {
        $this->donneesRecues = $donneesRecues;
        return $this;
    }

    public function getStatutReponse(): ?int
    {
        return $this->statutReponse;
    }

    public function setStatutReponse(?int $statutReponse): static
    {
        $this->statutReponse = $statutReponse;
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

    public function getIntegrationId(): IntegrationsExternes
    {
        return $this->integrationId;
    }

    public function setIntegrationId(IntegrationsExternes $integrationId): static
    {
        $this->integrationId = $integrationId;
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

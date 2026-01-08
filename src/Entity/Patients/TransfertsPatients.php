<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'transferts_patients', indexes: [
        new ORM\Index(name: 'admission_id', columns: ["admission_id"]),
        new ORM\Index(name: 'lit_destination_id', columns: ["lit_destination_id"]),
        new ORM\Index(name: 'lit_origine_id', columns: ["lit_origine_id"]),
        new ORM\Index(name: 'service_destination_id', columns: ["service_destination_id"]),
        new ORM\Index(name: 'service_origine_id', columns: ["service_origine_id"]),
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"])
    ])]
class TransfertsPatients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateTransfert;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $motifTransfert = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_origine_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceOrigineId;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(name: 'service_destination_id', referencedColumnName: 'id', nullable: false)]
    private Services $serviceDestinationId;

    #[ORM\ManyToOne(targetEntity: Lits::class)]
    #[ORM\JoinColumn(name: 'lit_origine_id', referencedColumnName: 'id', nullable: false)]
    private Lits $litOrigineId;

    #[ORM\ManyToOne(targetEntity: Lits::class)]
    #[ORM\JoinColumn(name: 'lit_destination_id', referencedColumnName: 'id', nullable: false)]
    private Lits $litDestinationId;

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

    public function getDateTransfert(): \DateTimeInterface
    {
        return $this->dateTransfert;
    }

    public function setDateTransfert(\DateTimeInterface $dateTransfert): static
    {
        $this->dateTransfert = $dateTransfert;
        return $this;
    }

    public function getMotifTransfert(): ?string
    {
        return $this->motifTransfert;
    }

    public function setMotifTransfert(?string $motifTransfert): static
    {
        $this->motifTransfert = $motifTransfert;
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

    public function getAdmissionId(): Admissions
    {
        return $this->admissionId;
    }

    public function setAdmissionId(Admissions $admissionId): static
    {
        $this->admissionId = $admissionId;
        return $this;
    }

    public function getServiceOrigineId(): Services
    {
        return $this->serviceOrigineId;
    }

    public function setServiceOrigineId(Services $serviceOrigineId): static
    {
        $this->serviceOrigineId = $serviceOrigineId;
        return $this;
    }

    public function getServiceDestinationId(): Services
    {
        return $this->serviceDestinationId;
    }

    public function setServiceDestinationId(Services $serviceDestinationId): static
    {
        $this->serviceDestinationId = $serviceDestinationId;
        return $this;
    }

    public function getLitOrigineId(): Lits
    {
        return $this->litOrigineId;
    }

    public function setLitOrigineId(Lits $litOrigineId): static
    {
        $this->litOrigineId = $litOrigineId;
        return $this;
    }

    public function getLitDestinationId(): Lits
    {
        return $this->litDestinationId;
    }

    public function setLitDestinationId(Lits $litDestinationId): static
    {
        $this->litDestinationId = $litDestinationId;
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

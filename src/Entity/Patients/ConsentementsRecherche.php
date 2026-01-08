<?php

namespace App\Entity\Patients;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'consentements_recherche', indexes: [
        new ORM\Index(name: 'hopital_id', columns: ["hopital_id"]),
        new ORM\Index(name: 'patient_id', columns: ["patient_id"]),
        new ORM\Index(name: 'IDX_AA0EFB9DC18272', columns: ["projet_id"])
    ])]
class ConsentementsRecherche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateConsentement;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $consentementDonne = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $signaturePatient = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $signatureTemoin = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateRetraitConsentement = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $raisonRetrait = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: ProjetsRecherche::class)]
    #[ORM\JoinColumn(name: 'projet_id', referencedColumnName: 'id', nullable: false)]
    private ProjetsRecherche $projetId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

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

    public function getDateConsentement(): \DateTimeInterface
    {
        return $this->dateConsentement;
    }

    public function setDateConsentement(\DateTimeInterface $dateConsentement): static
    {
        $this->dateConsentement = $dateConsentement;
        return $this;
    }

    public function getConsentementDonne(): ?bool
    {
        return $this->consentementDonne;
    }

    public function setConsentementDonne(?bool $consentementDonne): static
    {
        $this->consentementDonne = $consentementDonne;
        return $this;
    }

    public function getSignaturePatient(): ?string
    {
        return $this->signaturePatient;
    }

    public function setSignaturePatient(?string $signaturePatient): static
    {
        $this->signaturePatient = $signaturePatient;
        return $this;
    }

    public function getSignatureTemoin(): ?string
    {
        return $this->signatureTemoin;
    }

    public function setSignatureTemoin(?string $signatureTemoin): static
    {
        $this->signatureTemoin = $signatureTemoin;
        return $this;
    }

    public function getDateRetraitConsentement(): ?\DateTimeInterface
    {
        return $this->dateRetraitConsentement;
    }

    public function setDateRetraitConsentement(?\DateTimeInterface $dateRetraitConsentement): static
    {
        $this->dateRetraitConsentement = $dateRetraitConsentement;
        return $this;
    }

    public function getRaisonRetrait(): ?string
    {
        return $this->raisonRetrait;
    }

    public function setRaisonRetrait(?string $raisonRetrait): static
    {
        $this->raisonRetrait = $raisonRetrait;
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

    public function getProjetId(): ProjetsRecherche
    {
        return $this->projetId;
    }

    public function setProjetId(ProjetsRecherche $projetId): static
    {
        $this->projetId = $projetId;
        return $this;
    }

    public function getPatientId(): Patients
    {
        return $this->patientId;
    }

    public function setPatientId(Patients $patientId): static
    {
        $this->patientId = $patientId;
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

    public function isConsentementDonne(): ?bool
    {
        return $this->consentementDonne;
    }

}

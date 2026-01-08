<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'administrations_medicaments', indexes: [
        new ORM\Index(name: 'idx_admission', columns: ["admission_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_administration"]),
        new ORM\Index(name: 'idx_infirmier', columns: ["infirmier_id"]),
        new ORM\Index(name: 'idx_ligne_prescription', columns: ["ligne_prescription_id"])
    ])]
class AdministrationsMedicaments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateAdministration;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $quantiteAdministree = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $uniteQuantite = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $voieAdministration = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $siteInjection = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $effetSecondaireObserve = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $descriptionEffetSecondaire = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: LignesPrescriptions::class)]
    #[ORM\JoinColumn(name: 'ligne_prescription_id', referencedColumnName: 'id', nullable: false)]
    private LignesPrescriptions $lignePrescriptionId;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'infirmier_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $infirmierId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateAdministration(): \DateTimeInterface
    {
        return $this->dateAdministration;
    }

    public function setDateAdministration(\DateTimeInterface $dateAdministration): static
    {
        $this->dateAdministration = $dateAdministration;
        return $this;
    }

    public function getQuantiteAdministree(): ?string
    {
        return $this->quantiteAdministree;
    }

    public function setQuantiteAdministree(?string $quantiteAdministree): static
    {
        $this->quantiteAdministree = $quantiteAdministree;
        return $this;
    }

    public function getUniteQuantite(): ?string
    {
        return $this->uniteQuantite;
    }

    public function setUniteQuantite(?string $uniteQuantite): static
    {
        $this->uniteQuantite = $uniteQuantite;
        return $this;
    }

    public function getVoieAdministration(): ?string
    {
        return $this->voieAdministration;
    }

    public function setVoieAdministration(?string $voieAdministration): static
    {
        $this->voieAdministration = $voieAdministration;
        return $this;
    }

    public function getSiteInjection(): ?string
    {
        return $this->siteInjection;
    }

    public function setSiteInjection(?string $siteInjection): static
    {
        $this->siteInjection = $siteInjection;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function getEffetSecondaireObserve(): ?bool
    {
        return $this->effetSecondaireObserve;
    }

    public function setEffetSecondaireObserve(?bool $effetSecondaireObserve): static
    {
        $this->effetSecondaireObserve = $effetSecondaireObserve;
        return $this;
    }

    public function getDescriptionEffetSecondaire(): ?string
    {
        return $this->descriptionEffetSecondaire;
    }

    public function setDescriptionEffetSecondaire(?string $descriptionEffetSecondaire): static
    {
        $this->descriptionEffetSecondaire = $descriptionEffetSecondaire;
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

    public function getLignePrescriptionId(): LignesPrescriptions
    {
        return $this->lignePrescriptionId;
    }

    public function setLignePrescriptionId(LignesPrescriptions $lignePrescriptionId): static
    {
        $this->lignePrescriptionId = $lignePrescriptionId;
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

    public function getInfirmierId(): Utilisateurs
    {
        return $this->infirmierId;
    }

    public function setInfirmierId(Utilisateurs $infirmierId): static
    {
        $this->infirmierId = $infirmierId;
        return $this;
    }

    public function isEffetSecondaireObserve(): ?bool
    {
        return $this->effetSecondaireObserve;
    }

}

<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'lignes_prescriptions', indexes: [
        new ORM\Index(name: 'idx_medicament', columns: ["medicament_id"]),
        new ORM\Index(name: 'idx_prescription', columns: ["prescription_id"])
    ])]
class LignesPrescriptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $quantite = '';

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $uniteQuantite = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $dosage = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $frequenceAdministration = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $voieAdministration = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $dureeTraitement = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $instructionsSpeciales = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Prescriptions::class)]
    #[ORM\JoinColumn(name: 'prescription_id', referencedColumnName: 'id', nullable: false)]
    private Prescriptions $prescriptionId;

    #[ORM\ManyToOne(targetEntity: Medicaments::class)]
    #[ORM\JoinColumn(name: 'medicament_id', referencedColumnName: 'id', nullable: false)]
    private Medicaments $medicamentId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuantite(): string
    {
        return $this->quantite;
    }

    public function setQuantite(string $quantite): static
    {
        $this->quantite = $quantite;
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

    public function getDosage(): ?string
    {
        return $this->dosage;
    }

    public function setDosage(?string $dosage): static
    {
        $this->dosage = $dosage;
        return $this;
    }

    public function getFrequenceAdministration(): ?string
    {
        return $this->frequenceAdministration;
    }

    public function setFrequenceAdministration(?string $frequenceAdministration): static
    {
        $this->frequenceAdministration = $frequenceAdministration;
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

    public function getDureeTraitement(): ?int
    {
        return $this->dureeTraitement;
    }

    public function setDureeTraitement(?int $dureeTraitement): static
    {
        $this->dureeTraitement = $dureeTraitement;
        return $this;
    }

    public function getInstructionsSpeciales(): ?string
    {
        return $this->instructionsSpeciales;
    }

    public function setInstructionsSpeciales(?string $instructionsSpeciales): static
    {
        $this->instructionsSpeciales = $instructionsSpeciales;
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

    public function getPrescriptionId(): Prescriptions
    {
        return $this->prescriptionId;
    }

    public function setPrescriptionId(Prescriptions $prescriptionId): static
    {
        $this->prescriptionId = $prescriptionId;
        return $this;
    }

    public function getMedicamentId(): Medicaments
    {
        return $this->medicamentId;
    }

    public function setMedicamentId(Medicaments $medicamentId): static
    {
        $this->medicamentId = $medicamentId;
        return $this;
    }

}

<?php

namespace App\Entity\ComptabiliteFacturation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'paiements', indexes: [
        new ORM\Index(name: 'devise_id', columns: ["devise_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_paiement"]),
        new ORM\Index(name: 'idx_facture', columns: ["facture_id"]),
        new ORM\Index(name: 'idx_patient', columns: ["patient_id"]),
        new ORM\Index(name: 'mode_paiement_id', columns: ["mode_paiement_id"]),
        new ORM\Index(name: 'IDX_E1B02E12CC0FBF92', columns: ["hopital_id"])
    ])]
class Paiements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroPaiement = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $datePaiement;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $montantPaiement = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $referencePaiement = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxPaiement = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $fraisTransaction = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $notesPaiement = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Factures::class)]
    #[ORM\JoinColumn(name: 'facture_id', referencedColumnName: 'id', nullable: false)]
    private Factures $factureId;

    #[ORM\ManyToOne(targetEntity: Patients::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private Patients $patientId;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: 'devise_id', referencedColumnName: 'id', nullable: false)]
    private Devises $deviseId;

    #[ORM\ManyToOne(targetEntity: ModesPaiement::class)]
    #[ORM\JoinColumn(name: 'mode_paiement_id', referencedColumnName: 'id', nullable: false)]
    private ModesPaiement $modePaiementId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumeroPaiement(): string
    {
        return $this->numeroPaiement;
    }

    public function setNumeroPaiement(string $numeroPaiement): static
    {
        $this->numeroPaiement = $numeroPaiement;
        return $this;
    }

    public function getDatePaiement(): \DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getMontantPaiement(): ?string
    {
        return $this->montantPaiement;
    }

    public function setMontantPaiement(?string $montantPaiement): static
    {
        $this->montantPaiement = $montantPaiement;
        return $this;
    }

    public function getReferencePaiement(): ?string
    {
        return $this->referencePaiement;
    }

    public function setReferencePaiement(?string $referencePaiement): static
    {
        $this->referencePaiement = $referencePaiement;
        return $this;
    }

    public function getTauxPaiement(): ?string
    {
        return $this->tauxPaiement;
    }

    public function setTauxPaiement(?string $tauxPaiement): static
    {
        $this->tauxPaiement = $tauxPaiement;
        return $this;
    }

    public function getFraisTransaction(): ?string
    {
        return $this->fraisTransaction;
    }

    public function setFraisTransaction(?string $fraisTransaction): static
    {
        $this->fraisTransaction = $fraisTransaction;
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

    public function getNotesPaiement(): ?string
    {
        return $this->notesPaiement;
    }

    public function setNotesPaiement(?string $notesPaiement): static
    {
        $this->notesPaiement = $notesPaiement;
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

    public function getFactureId(): Factures
    {
        return $this->factureId;
    }

    public function setFactureId(Factures $factureId): static
    {
        $this->factureId = $factureId;
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

    public function getDeviseId(): Devises
    {
        return $this->deviseId;
    }

    public function setDeviseId(Devises $deviseId): static
    {
        $this->deviseId = $deviseId;
        return $this;
    }

    public function getModePaiementId(): ModesPaiement
    {
        return $this->modePaiementId;
    }

    public function setModePaiementId(ModesPaiement $modePaiementId): static
    {
        $this->modePaiementId = $modePaiementId;
        return $this;
    }

}

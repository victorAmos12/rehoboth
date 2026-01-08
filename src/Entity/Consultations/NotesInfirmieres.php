<?php

namespace App\Entity\Consultations;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'notes_infirmieres', indexes: [
        new ORM\Index(name: 'idx_admission', columns: ["admission_id"]),
        new ORM\Index(name: 'idx_date', columns: ["date_note"]),
        new ORM\Index(name: 'idx_infirmier', columns: ["infirmier_id"])
    ])]
class NotesInfirmieres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10)]
    private \DateTimeInterface $dateNote;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeNote = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10)]
    private string $contenu = '';

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $signesVitauxTension = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $signesVitauxFrequenceCardiaque = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 1, nullable: true)]
    private ?string $signesVitauxTemperature = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $signesVitauxFrequenceRespiratoire = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $signesVitauxSaturationO2 = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $observationsGenerales = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Admissions::class)]
    #[ORM\JoinColumn(name: 'admission_id', referencedColumnName: 'id', nullable: false)]
    private Admissions $admissionId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'infirmier_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $infirmierId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateNote(): \DateTimeInterface
    {
        return $this->dateNote;
    }

    public function setDateNote(\DateTimeInterface $dateNote): static
    {
        $this->dateNote = $dateNote;
        return $this;
    }

    public function getTypeNote(): ?string
    {
        return $this->typeNote;
    }

    public function setTypeNote(?string $typeNote): static
    {
        $this->typeNote = $typeNote;
        return $this;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getSignesVitauxTension(): ?string
    {
        return $this->signesVitauxTension;
    }

    public function setSignesVitauxTension(?string $signesVitauxTension): static
    {
        $this->signesVitauxTension = $signesVitauxTension;
        return $this;
    }

    public function getSignesVitauxFrequenceCardiaque(): ?int
    {
        return $this->signesVitauxFrequenceCardiaque;
    }

    public function setSignesVitauxFrequenceCardiaque(?int $signesVitauxFrequenceCardiaque): static
    {
        $this->signesVitauxFrequenceCardiaque = $signesVitauxFrequenceCardiaque;
        return $this;
    }

    public function getSignesVitauxTemperature(): ?string
    {
        return $this->signesVitauxTemperature;
    }

    public function setSignesVitauxTemperature(?string $signesVitauxTemperature): static
    {
        $this->signesVitauxTemperature = $signesVitauxTemperature;
        return $this;
    }

    public function getSignesVitauxFrequenceRespiratoire(): ?int
    {
        return $this->signesVitauxFrequenceRespiratoire;
    }

    public function setSignesVitauxFrequenceRespiratoire(?int $signesVitauxFrequenceRespiratoire): static
    {
        $this->signesVitauxFrequenceRespiratoire = $signesVitauxFrequenceRespiratoire;
        return $this;
    }

    public function getSignesVitauxSaturationO2(): ?string
    {
        return $this->signesVitauxSaturationO2;
    }

    public function setSignesVitauxSaturationO2(?string $signesVitauxSaturationO2): static
    {
        $this->signesVitauxSaturationO2 = $signesVitauxSaturationO2;
        return $this;
    }

    public function getObservationsGenerales(): ?string
    {
        return $this->observationsGenerales;
    }

    public function setObservationsGenerales(?string $observationsGenerales): static
    {
        $this->observationsGenerales = $observationsGenerales;
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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
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

}

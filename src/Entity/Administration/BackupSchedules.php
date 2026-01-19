<?php

namespace App\Entity\Administration;

use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * BackupSchedules - Planification des sauvegardes automatiques
 * 
 * Permet de configurer des sauvegardes récurrentes
 */
#[ORM\Entity]
#[ORM\Table(name: 'backup_schedules', indexes: [
    new ORM\Index(name: 'idx_hopital_id', columns: ['hopital_id']),
    new ORM\Index(name: 'idx_utilisateur_id', columns: ['utilisateur_id']),
    new ORM\Index(name: 'idx_actif', columns: ['actif']),
    new ORM\Index(name: 'idx_prochaine_execution', columns: ['prochaine_execution']),
])]
class BackupSchedules
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    /**
     * Identifiant unique de la planification
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $scheduleId = '';

    /**
     * Type de backup: COMPLETE, INCREMENTAL, DIFFERENTIAL, SNAPSHOT
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $typeBackup = 'COMPLETE';

    /**
     * Fréquence: DAILY, WEEKLY, MONTHLY, HOURLY
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $frequency = 'DAILY';

    /**
     * Heure d'exécution (HH:MM)
     */
    #[ORM\Column(type: 'string', length: 5)]
    private string $time = '02:00';

    /**
     * Jour de la semaine pour WEEKLY (0=dimanche, 6=samedi)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $dayOfWeek = null;

    /**
     * Jour du mois pour MONTHLY (1-31)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $dayOfMonth = null;

    /**
     * Localisation du backup
     */
    #[ORM\Column(type: 'text')]
    private string $localisationBackup = '';

    /**
     * Localisation secondaire (pour règle 3-2-1)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $localisationSecondaire = null;

    /**
     * Jours de rétention
     */
    #[ORM\Column(type: 'integer')]
    private int $retentionDays = 30;

    /**
     * Actif ou non
     */
    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    /**
     * Prochaine exécution prévue
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $prochaineExecution = null;

    /**
     * Dernière exécution
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $derniereExecution = null;

    /**
     * Statut de la dernière exécution
     */
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $dernierStatut = null;

    /**
     * Message d'erreur de la dernière exécution
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $messageErreur = null;

    /**
     * Nombre d'exécutions réussies
     */
    #[ORM\Column(type: 'integer')]
    private int $executionsReussies = 0;

    /**
     * Nombre d'exécutions échouées
     */
    #[ORM\Column(type: 'integer')]
    private int $executionsEchouees = 0;

    /**
     * Notes
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Date de création
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $dateCreation;

    /**
     * Date de modification
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $dateModification;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getScheduleId(): string
    {
        return $this->scheduleId;
    }

    public function setScheduleId(string $scheduleId): static
    {
        $this->scheduleId = $scheduleId;
        return $this;
    }

    public function getTypeBackup(): string
    {
        return $this->typeBackup;
    }

    public function setTypeBackup(string $typeBackup): static
    {
        $this->typeBackup = $typeBackup;
        return $this;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function setTime(string $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(?int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getDayOfMonth(): ?int
    {
        return $this->dayOfMonth;
    }

    public function setDayOfMonth(?int $dayOfMonth): static
    {
        $this->dayOfMonth = $dayOfMonth;
        return $this;
    }

    public function getLocalisationBackup(): string
    {
        return $this->localisationBackup;
    }

    public function setLocalisationBackup(string $localisationBackup): static
    {
        $this->localisationBackup = $localisationBackup;
        return $this;
    }

    public function getLocalisationSecondaire(): ?string
    {
        return $this->localisationSecondaire;
    }

    public function setLocalisationSecondaire(?string $localisationSecondaire): static
    {
        $this->localisationSecondaire = $localisationSecondaire;
        return $this;
    }

    public function getRetentionDays(): int
    {
        return $this->retentionDays;
    }

    public function setRetentionDays(int $retentionDays): static
    {
        $this->retentionDays = $retentionDays;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getProchaineExecution(): ?DateTimeImmutable
    {
        return $this->prochaineExecution;
    }

    public function setProchaineExecution(?DateTimeImmutable $prochaineExecution): static
    {
        $this->prochaineExecution = $prochaineExecution;
        return $this;
    }

    public function getDerniereExecution(): ?DateTimeImmutable
    {
        return $this->derniereExecution;
    }

    public function setDerniereExecution(?DateTimeImmutable $derniereExecution): static
    {
        $this->derniereExecution = $derniereExecution;
        return $this;
    }

    public function getDernierStatut(): ?string
    {
        return $this->dernierStatut;
    }

    public function setDernierStatut(?string $dernierStatut): static
    {
        $this->dernierStatut = $dernierStatut;
        return $this;
    }

    public function getMessageErreur(): ?string
    {
        return $this->messageErreur;
    }

    public function setMessageErreur(?string $messageErreur): static
    {
        $this->messageErreur = $messageErreur;
        return $this;
    }

    public function getExecutionsReussies(): int
    {
        return $this->executionsReussies;
    }

    public function setExecutionsReussies(int $executionsReussies): static
    {
        $this->executionsReussies = $executionsReussies;
        return $this;
    }

    public function getExecutionsEchouees(): int
    {
        return $this->executionsEchouees;
    }

    public function setExecutionsEchouees(int $executionsEchouees): static
    {
        $this->executionsEchouees = $executionsEchouees;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): DateTimeImmutable
    {
        return $this->dateModification;
    }

    public function setDateModification(DateTimeImmutable $dateModification): static
    {
        $this->dateModification = $dateModification;
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

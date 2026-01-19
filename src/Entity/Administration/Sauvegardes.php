<?php

namespace App\Entity\Administration;

use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * Sauvegardes - Métadonnées des sauvegardes (backups)
 * 
 * Objectif: Récupérer les données après:
 * - Panne serveur
 * - Erreur humaine
 * - Corruption de données
 * - Cyberattaque (ransomware)
 * 
 * Règle 3-2-1:
 * - 3 copies
 * - 2 supports différents
 * - 1 hors site
 * 
 * @see https://nvlpubs.nist.gov/nistpubs/Legacy/SP/nistspecialpublication800-34r1.pdf
 * @see https://www.iso.org/standard/27001
 */
#[ORM\Entity]
#[ORM\Table(name: 'sauvegardes', indexes: [
    new ORM\Index(name: 'idx_date_debut', columns: ['date_debut']),
    new ORM\Index(name: 'idx_type_backup', columns: ['type_backup']),
    new ORM\Index(name: 'idx_statut', columns: ['statut']),
    new ORM\Index(name: 'idx_hopital_id', columns: ['hopital_id']),
    new ORM\Index(name: 'idx_utilisateur_id', columns: ['utilisateur_id']),
])]
class Sauvegardes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    /**
     * Identifiant unique du backup
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $backupId = '';

    /**
     * Numéro de sauvegarde (ancien champ, conservé pour compatibilité)
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $numeroSauvegarde = '';

    /**
     * Type de backup: COMPLETE, INCREMENTAL, DIFFERENTIAL, SNAPSHOT
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $typeBackup = 'COMPLETE';

    /**
     * Horodatage du début du backup (UTC)
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $dateDebut;

    /**
     * Horodatage de fin du backup (UTC)
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $dateFin = null;

    /**
     * Ancien champ dateSauvegarde (conservé pour compatibilité)
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateSauvegarde = null;

    /**
     * Statut: PENDING, IN_PROGRESS, SUCCESS, FAILED, VERIFIED, DELETED
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $statut = 'PENDING';

    /**
     * Localisation du backup (chemin, URL S3, etc.)
     */
    #[ORM\Column(type: 'text')]
    private string $localisationSauvegarde = '';

    /**
     * Localisation secondaire (pour règle 3-2-1)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $localisationSecondaire = null;

    /**
     * Taille du backup en bytes
     */
    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $tailleSauvegarde = null;

    /**
     * Durée du backup en secondes
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $dureeSauvegarde = null;

    /**
     * Checksum SHA-256 pour intégrité
     */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $checksumSha256 = null;

    /**
     * Clé de chiffrement (référence, pas la clé elle-même)
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $cleChiffrement = null;

    /**
     * Compression utilisée: NONE, GZIP, BZIP2, etc.
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $compression = null;

    /**
     * Nombre de fichiers sauvegardés
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombreFichiers = null;

    /**
     * Nombre de tables BD sauvegardées
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombreTables = null;

    /**
     * Date d'expiration du backup (politique de rétention)
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $dateExpiration = null;

    /**
     * Message d'erreur (si applicable)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $messageErreur = null;

    /**
     * Notes additionnelles
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Date de création (ancien champ, conservé pour compatibilité)
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

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

    public function getNumeroSauvegarde(): string
    {
        return $this->numeroSauvegarde;
    }

    public function setNumeroSauvegarde(string $numeroSauvegarde): static
    {
        $this->numeroSauvegarde = $numeroSauvegarde;
        return $this;
    }

    public function getDateSauvegarde(): \DateTimeInterface
    {
        return $this->dateSauvegarde;
    }

    public function setDateSauvegarde(\DateTimeInterface $dateSauvegarde): static
    {
        $this->dateSauvegarde = $dateSauvegarde;
        return $this;
    }

    public function getTypeSauvegarde(): ?string
    {
        return $this->typeSauvegarde;
    }

    public function setTypeSauvegarde(?string $typeSauvegarde): static
    {
        $this->typeSauvegarde = $typeSauvegarde;
        return $this;
    }

    public function getTailleSauvegarde(): ?int
    {
        return $this->tailleSauvegarde;
    }

    public function setTailleSauvegarde(?int $tailleSauvegarde): static
    {
        $this->tailleSauvegarde = $tailleSauvegarde;
        return $this;
    }

    public function getLocalisationSauvegarde(): ?string
    {
        return $this->localisationSauvegarde;
    }

    public function setLocalisationSauvegarde(?string $localisationSauvegarde): static
    {
        $this->localisationSauvegarde = $localisationSauvegarde;
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

    public function getDureeSauvegarde(): ?int
    {
        return $this->dureeSauvegarde;
    }

    public function setDureeSauvegarde(?int $dureeSauvegarde): static
    {
        $this->dureeSauvegarde = $dureeSauvegarde;
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

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function getBackupId(): string
    {
        return $this->backupId;
    }

    public function setBackupId(string $backupId): static
    {
        $this->backupId = $backupId;
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

    public function getDateDebut(): DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;
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

    public function getChecksumSha256(): ?string
    {
        return $this->checksumSha256;
    }

    public function setChecksumSha256(?string $checksumSha256): static
    {
        $this->checksumSha256 = $checksumSha256;
        return $this;
    }

    public function getCleChiffrement(): ?string
    {
        return $this->cleChiffrement;
    }

    public function setCleChiffrement(?string $cleChiffrement): static
    {
        $this->cleChiffrement = $cleChiffrement;
        return $this;
    }

    public function getCompression(): ?string
    {
        return $this->compression;
    }

    public function setCompression(?string $compression): static
    {
        $this->compression = $compression;
        return $this;
    }

    public function getNombreFichiers(): ?int
    {
        return $this->nombreFichiers;
    }

    public function setNombreFichiers(?int $nombreFichiers): static
    {
        $this->nombreFichiers = $nombreFichiers;
        return $this;
    }

    public function getNombreTables(): ?int
    {
        return $this->nombreTables;
    }

    public function setNombreTables(?int $nombreTables): static
    {
        $this->nombreTables = $nombreTables;
        return $this;
    }

    public function getDateExpiration(): ?DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?DateTimeImmutable $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

}

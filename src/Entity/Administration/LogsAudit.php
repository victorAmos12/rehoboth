<?php

namespace App\Entity\Administration;

use App\Entity\Personnel\Utilisateurs;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use DateTimeImmutable;

/**
 * LogsAudit - Logs techniques ET audit trail
 * 
 * Cette entité combine:
 * 1. Logs techniques: erreurs, exceptions, performance, HTTP
 * 2. Audit trail: traçabilité légale (QUI/QUOI/QUAND/OÙ)
 * 
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 * @see https://owasp.org/www-project-application-security-verification-standard/
 */
#[ORM\Entity]
#[ORM\Table(name: 'logs_audit', indexes: [
    new ORM\Index(name: 'idx_date_creation', columns: ['date_creation']),
    new ORM\Index(name: 'idx_hopital_id', columns: ['hopital_id']),
    new ORM\Index(name: 'idx_utilisateur_id', columns: ['utilisateur_id']),
    new ORM\Index(name: 'idx_type_log', columns: ['type_log']),
    new ORM\Index(name: 'idx_niveau', columns: ['niveau']),
    new ORM\Index(name: 'idx_action_type', columns: ['action_type']),
])]
class LogsAudit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    /**
     * Type de log: TECHNIQUE ou AUDIT
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $typeLog = 'TECHNIQUE';

    /**
     * Niveau (pour logs techniques): DEBUG, INFO, WARNING, ERROR, CRITICAL
     */
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $niveau = null;

    /**
     * Catégorie (pour logs techniques): APPLICATION, HTTP, DATABASE, SECURITY, PERFORMANCE, SYSTEM
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $categorie = null;

    /**
     * Module (ancien champ, conservé pour compatibilité)
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $module = null;

    /**
     * Action (ancien champ, conservé pour compatibilité)
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $action = null;

    /**
     * Type d'action (pour audit): CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $actionType = null;

    /**
     * Entité (ancien champ, conservé pour compatibilité)
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $entite = null;

    /**
     * Type d'entité (pour audit): Patient, Utilisateur, Service, etc.
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $entiteType = null;

    /**
     * ID de l'entité
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $entiteId = null;

    /**
     * Description lisible
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Message de log (pour logs techniques)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    /**
     * Contexte JSON (données non-sensibles)
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $contexte = null;

    /**
     * Ancienne valeur (avant modification)
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ancienneValeur = null;

    /**
     * Nouvelle valeur (après modification)
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $nouvelleValeur = null;

    /**
     * Stack trace (pour DEBUG/ERROR)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $stackTrace = null;

    /**
     * Adresse IP du client
     */
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $adresseIp = null;

    /**
     * User-Agent du client
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $userAgent = null;

    /**
     * Endpoint HTTP (si applicable)
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $endpoint = null;

    /**
     * Méthode HTTP (GET, POST, etc.)
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $methodeHttp = null;

    /**
     * Temps de réponse en ms
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tempsReponseMs = null;

    /**
     * Code HTTP de réponse
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $codeHttp = null;

    /**
     * Statut: SUCCESS, FAILURE, PARTIAL
     */
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $statut = null;

    /**
     * Message d'erreur (si applicable)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $messageErreur = null;

    /**
     * Signature HMAC pour intégrité (audit)
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $signature = null;

    /**
     * Horodatage UTC
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $dateCreation;

    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private Utilisateurs $utilisateurId;

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

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): static
    {
        $this->module = $module;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getEntite(): ?string
    {
        return $this->entite;
    }

    public function setEntite(?string $entite): static
    {
        $this->entite = $entite;
        return $this;
    }

    public function getEntiteId(): ?int
    {
        return $this->entiteId;
    }

    public function setEntiteId(?int $entiteId): static
    {
        $this->entiteId = $entiteId;
        return $this;
    }

    public function getAncienneValeur(): ?string
    {
        return $this->ancienneValeur;
    }

    public function setAncienneValeur(?string $ancienneValeur): static
    {
        $this->ancienneValeur = $ancienneValeur;
        return $this;
    }

    public function getNouvelleValeur(): ?string
    {
        return $this->nouvelleValeur;
    }

    public function setNouvelleValeur(?string $nouvelleValeur): static
    {
        $this->nouvelleValeur = $nouvelleValeur;
        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
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

    public function getUtilisateurId(): Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(Utilisateurs $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;
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

    public function getTypeLog(): string
    {
        return $this->typeLog;
    }

    public function setTypeLog(string $typeLog): static
    {
        $this->typeLog = $typeLog;
        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(?string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(?string $actionType): static
    {
        $this->actionType = $actionType;
        return $this;
    }

    public function getEntiteType(): ?string
    {
        return $this->entiteType;
    }

    public function setEntiteType(?string $entiteType): static
    {
        $this->entiteType = $entiteType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getContexte(): ?array
    {
        return $this->contexte;
    }

    public function setContexte(?array $contexte): static
    {
        $this->contexte = $contexte;
        return $this;
    }

    public function getStackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function setStackTrace(?string $stackTrace): static
    {
        $this->stackTrace = $stackTrace;
        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getMethodeHttp(): ?string
    {
        return $this->methodeHttp;
    }

    public function setMethodeHttp(?string $methodeHttp): static
    {
        $this->methodeHttp = $methodeHttp;
        return $this;
    }

    public function getTempsReponseMs(): ?int
    {
        return $this->tempsReponseMs;
    }

    public function setTempsReponseMs(?int $tempsReponseMs): static
    {
        $this->tempsReponseMs = $tempsReponseMs;
        return $this;
    }

    public function getCodeHttp(): ?int
    {
        return $this->codeHttp;
    }

    public function setCodeHttp(?int $codeHttp): static
    {
        $this->codeHttp = $codeHttp;
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

    public function getMessageErreur(): ?string
    {
        return $this->messageErreur;
    }

    public function setMessageErreur(?string $messageErreur): static
    {
        $this->messageErreur = $messageErreur;
        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;
        return $this;
    }

    public function getAncienneValeurArray(): ?array
    {
        return $this->ancienneValeur;
    }

    public function setAncienneValeurArray(?array $ancienneValeur): static
    {
        $this->ancienneValeur = $ancienneValeur;
        return $this;
    }

    public function getNouvelleValeurArray(): ?array
    {
        return $this->nouvelleValeur;
    }

    public function setNouvelleValeurArray(?array $nouvelleValeur): static
    {
        $this->nouvelleValeur = $nouvelleValeur;
        return $this;
    }

}

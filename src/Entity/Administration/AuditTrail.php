<?php

namespace App\Entity\Administration;

use App\Entity\Personnel\Utilisateurs;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * AuditTrail - Audit immuable pour conformité légale
 * 
 * Table de traçabilité IMMUABLE pour la conformité RGPD, ISO 27001 et OWASP.
 * Enregistre QUI a fait QUOI, QUAND, OÙ, COMMENT et POURQUOI.
 * 
 * ⚠️ RÈGLES STRICTES:
 * - 🚫 AUCUNE SUPPRESSION (immuable)
 * - ✅ INSERTS SEULEMENT
 * - 🔒 Signature HMAC pour intégrité
 * - 📝 Valeurs ancien/nouveau en JSON
 * 
 * @see https://www.iso.org/standard/27001
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 * @see https://owasp.org/www-project-application-security-verification-standard/
 */
#[ORM\Entity]
#[ORM\Table(
    name: 'audit_trail',
    indexes: [
        new ORM\Index(name: 'idx_audit_date', columns: ['date_action']),
        new ORM\Index(name: 'idx_audit_utilisateur', columns: ['utilisateur_id']),
        new ORM\Index(name: 'idx_audit_hopital', columns: ['hopital_id']),
        new ORM\Index(name: 'idx_audit_entite', columns: ['entite_type', 'entite_id']),
        new ORM\Index(name: 'idx_audit_action', columns: ['action_type']),
    ]
)]
#[ORM\UniqueConstraint(name: 'UNIQ_AUDIT_SIGNATURE', columns: ['id'])]
class AuditTrail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    /**
     * Utilisateur ayant effectué l'action
     * 
     * FK vers la table utilisateurs
     * NULL si action système
     */
    #[ORM\ManyToOne(targetEntity: Utilisateurs::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Utilisateurs $utilisateurId = null;

    /**
     * Hôpital concerné
     * 
     * FK vers la table hopitaux
     */
    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    private ?Hopitaux $hopitalId = null;

    /**
     * Type d'action effectuée
     * 
     * Valeurs: CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $actionType = 'UPDATE';

    /**
     * Type d'entité modifiée
     * 
     * Exemples: Patient, Utilisateur, Service, Produit, Contrat, etc.
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $entiteType = '';

    /**
     * ID de l'entité modifiée
     * 
     * Permet de retrouver l'entité spécifique
     */
    #[ORM\Column(type: 'integer')]
    private int $entiteId = 0;

    /**
     * Description lisible de l'action
     * 
     * Exemple: "Patient Jean Dupont créé"
     * Exemple: "Modification de l'adresse du patient"
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Valeur AVANT la modification (en JSON)
     * 
     * NULL pour CREATE
     * JSON avec tous les champs modifiés
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ancienneValeur = null;

    /**
     * Valeur APRÈS la modification (en JSON)
     * 
     * NULL pour DELETE
     * JSON avec tous les champs de l'entité
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $nouvelleValeur = null;

    /**
     * Statut de l'action
     * 
     * Valeurs: SUCCESS, FAILURE, PARTIAL
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $statut = 'SUCCESS';

    /**
     * Message d'erreur (si statut != SUCCESS)
     * 
     * Contient l'exception ou code d'erreur
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $messageErreur = null;

    /**
     * Adresse IP source
     * 
     * Permet de tracer la source de l'action
     */
    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $adresseIp = null;

    /**
     * User-Agent (navigateur/client)
     * 
     * Permet de tracer le client utilisé
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    /**
     * Signature HMAC-SHA256 pour intégrité
     * 
     * Calcul: HMAC-SHA256(utilisateur_id | hopital_id | action_type | entite_type | entite_id | date_action, SECRET_KEY)
     * Permet de détecter toute modification de l'audit trail
     */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $signature = null;

    /**
     * Date/heure de l'action (timezone UTC)
     * 
     * Immuable - défini à la création
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $dateAction = null;

    /**
     * Date/heure de création du log (timezone UTC)
     * 
     * Identique à dateAction en règle générale
     * Immuable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
        $now = new DateTimeImmutable();
        $this->dateAction = $now;
        $this->dateCreation = $now;
    }

    // ============================================================================
    // GETTERS & SETTERS
    // ============================================================================

    public function getId(): int
    {
        return $this->id;
    }

    public function getUtilisateurId(): ?Utilisateurs
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(?Utilisateurs $utilisateurId): self
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function getHopitalId(): ?Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(?Hopitaux $hopitalId): self
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;
        return $this;
    }

    public function getEntiteType(): string
    {
        return $this->entiteType;
    }

    public function setEntiteType(string $entiteType): self
    {
        $this->entiteType = $entiteType;
        return $this;
    }

    public function getEntiteId(): int
    {
        return $this->entiteId;
    }

    public function setEntiteId(int $entiteId): self
    {
        $this->entiteId = $entiteId;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAncienneValeur(): ?array
    {
        return $this->ancienneValeur;
    }

    public function setAncienneValeur(?array $ancienneValeur): self
    {
        $this->ancienneValeur = $ancienneValeur;
        return $this;
    }

    public function getNouvelleValeur(): ?array
    {
        return $this->nouvelleValeur;
    }

    public function setNouvelleValeur(?array $nouvelleValeur): self
    {
        $this->nouvelleValeur = $nouvelleValeur;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMessageErreur(): ?string
    {
        return $this->messageErreur;
    }

    public function setMessageErreur(?string $messageErreur): self
    {
        $this->messageErreur = $messageErreur;
        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): self
    {
        $this->adresseIp = $adresseIp;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;
        return $this;
    }

    public function getDateAction(): ?DateTimeImmutable
    {
        return $this->dateAction;
    }

    public function setDateAction(?DateTimeImmutable $dateAction): self
    {
        $this->dateAction = $dateAction;
        return $this;
    }

    public function getDateCreation(): ?DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
}

<?php

namespace App\Entity\Personnel;

use App\Entity\Administration\Hopitaux;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateurs', indexes: [
        new ORM\Index(name: 'idx_actif', columns: ["actif"]),
        new ORM\Index(name: 'idx_email', columns: ["email"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_login', columns: ["login"]),
        new ORM\Index(name: 'idx_profil', columns: ["profil_id"]),
        new ORM\Index(name: 'idx_role', columns: ["role_id"]),
        new ORM\Index(name: 'specialite_id', columns: ["specialite_id"])
    ])]
class Utilisateurs implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $prenom = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $email = '';

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10)]
    private string $login = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $motDePasse = '';

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroLicence = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $numeroOrdre = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateEmbauche = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $photoProfil = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $signatureNumerique = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 10, precision: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(type: 'string', length: 1, precision: 10, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $adressePhysique = null;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $validite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $numeroIdentite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeIdentite = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephoneUrgence = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $contactUrgenceNom = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $compteVerrouille = null;

    #[ORM\Column(type: 'integer', precision: 10, nullable: true)]
    private ?int $nombreTentativesConnexion = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateDernierChangementMdp = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $mdpTemporaire = null;

    #[ORM\Column(name: 'authentification_2fa', type: 'boolean', precision: 10, nullable: true)]
    private ?bool $authentification2fa = null;

    #[ORM\Column(name: 'secret_2fa', type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $secret2fa = null;

    #[ORM\Column(name: 'pin_2fa', type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $pin2fa = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $derniereConnexion = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false)]
    private Roles $roleId;

    #[ORM\ManyToOne(targetEntity: ProfilsUtilisateurs::class)]
    #[ORM\JoinColumn(name: 'profil_id', referencedColumnName: 'id', nullable: false)]
    private ProfilsUtilisateurs $profilId;

    #[ORM\ManyToOne(targetEntity: Specialites::class)]
    #[ORM\JoinColumn(name: 'specialite_id', referencedColumnName: 'id', nullable: true)]
    private ?Specialites $specialiteId = null;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
        $this->dateModification = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    /**
     * Implémentation de PasswordAuthenticatedUserInterface
     * Retourne le mot de passe hashé
     */
    public function getPassword(): string
    {
        return $this->getMotDePasse();
    }

    public function getNumeroLicence(): ?string
    {
        return $this->numeroLicence;
    }

    public function setNumeroLicence(?string $numeroLicence): static
    {
        $this->numeroLicence = $numeroLicence;
        return $this;
    }

    public function getNumeroOrdre(): ?string
    {
        return $this->numeroOrdre;
    }

    public function setNumeroOrdre(?string $numeroOrdre): static
    {
        $this->numeroOrdre = $numeroOrdre;
        return $this;
    }

    public function getDateEmbauche(): ?\DateTimeInterface
    {
        return $this->dateEmbauche;
    }

    public function setDateEmbauche(?\DateTimeInterface $dateEmbauche): static
    {
        $this->dateEmbauche = $dateEmbauche;
        return $this;
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): static
    {
        $this->photoProfil = $photoProfil;
        return $this;
    }

    public function getSignatureNumerique(): ?string
    {
        return $this->signatureNumerique;
    }

    public function setSignatureNumerique(?string $signatureNumerique): static
    {
        $this->signatureNumerique = $signatureNumerique;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function getAdressePhysique(): ?string
    {
        return $this->adressePhysique;
    }

    public function setAdressePhysique(?string $adressePhysique): static
    {
        $this->adressePhysique = $adressePhysique;
        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;
        return $this;
    }

    public function getValidite(): ?string
    {
        return $this->validite;
    }

    public function setValidite(?string $validite): static
    {
        $this->validite = $validite;
        return $this;
    }

    public function getNumeroIdentite(): ?string
    {
        return $this->numeroIdentite;
    }

    public function setNumeroIdentite(?string $numeroIdentite): static
    {
        $this->numeroIdentite = $numeroIdentite;
        return $this;
    }

    public function getTypeIdentite(): ?string
    {
        return $this->typeIdentite;
    }

    public function setTypeIdentite(?string $typeIdentite): static
    {
        $this->typeIdentite = $typeIdentite;
        return $this;
    }

    public function getTelephoneUrgence(): ?string
    {
        return $this->telephoneUrgence;
    }

    public function setTelephoneUrgence(?string $telephoneUrgence): static
    {
        $this->telephoneUrgence = $telephoneUrgence;
        return $this;
    }

    public function getContactUrgenceNom(): ?string
    {
        return $this->contactUrgenceNom;
    }

    public function setContactUrgenceNom(?string $contactUrgenceNom): static
    {
        $this->contactUrgenceNom = $contactUrgenceNom;
        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getCompteVerrouille(): ?bool
    {
        return $this->compteVerrouille;
    }

    public function setCompteVerrouille(?bool $compteVerrouille): static
    {
        $this->compteVerrouille = $compteVerrouille;
        return $this;
    }

    public function getNombreTentativesConnexion(): ?int
    {
        return $this->nombreTentativesConnexion;
    }

    public function setNombreTentativesConnexion(?int $nombreTentativesConnexion): static
    {
        $this->nombreTentativesConnexion = $nombreTentativesConnexion;
        return $this;
    }

    public function getDateDernierChangementMdp(): ?\DateTimeInterface
    {
        return $this->dateDernierChangementMdp;
    }

    public function setDateDernierChangementMdp(?\DateTimeInterface $dateDernierChangementMdp): static
    {
        $this->dateDernierChangementMdp = $dateDernierChangementMdp;
        return $this;
    }

    public function getMdpTemporaire(): ?bool
    {
        return $this->mdpTemporaire;
    }

    public function setMdpTemporaire(?bool $mdpTemporaire): static
    {
        $this->mdpTemporaire = $mdpTemporaire;
        return $this;
    }

    public function getAuthentification2fa(): ?bool
    {
        return $this->authentification2fa;
    }

    public function setAuthentification2fa(?bool $authentification2fa): static
    {
        $this->authentification2fa = $authentification2fa;
        return $this;
    }

    public function getDerniereConnexion(): ?\DateTimeInterface
    {
        return $this->derniereConnexion;
    }

    public function setDerniereConnexion(?\DateTimeInterface $derniereConnexion): static
    {
        $this->derniereConnexion = $derniereConnexion;
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

    public function getHopitalId(): Hopitaux
    {
        return $this->hopitalId;
    }

    public function setHopitalId(Hopitaux $hopitalId): static
    {
        $this->hopitalId = $hopitalId;
        return $this;
    }

    public function getRoleId(): Roles
    {
        return $this->roleId;
    }

    public function setRoleId(Roles $roleId): static
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function getProfilId(): ProfilsUtilisateurs
    {
        return $this->profilId;
    }

    public function setProfilId(ProfilsUtilisateurs $profilId): static
    {
        $this->profilId = $profilId;
        return $this;
    }

    public function getSpecialiteId(): ?Specialites
    {
        return $this->specialiteId;
    }

    public function setSpecialiteId(?Specialites $specialiteId): static
    {
        $this->specialiteId = $specialiteId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function isCompteVerrouille(): ?bool
    {
        return $this->compteVerrouille;
    }

    public function isMdpTemporaire(): ?bool
    {
        return $this->mdpTemporaire;
    }

    public function isAuthentification2fa(): ?bool
    {
        return $this->authentification2fa;
    }

    public function getSecret2fa(): ?string
    {
        return $this->secret2fa;
    }

    public function setSecret2fa(?string $secret2fa): static
    {
        $this->secret2fa = $secret2fa;
        return $this;
    }

    public function getPin2fa(): ?string
    {
        return $this->pin2fa;
    }

    public function setPin2fa(?string $pin2fa): static
    {
        $this->pin2fa = $pin2fa;
        return $this;
    }

}

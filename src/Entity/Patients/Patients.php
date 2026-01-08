<?php

namespace App\Entity\Patients;

use App\Entity\Administration\Hopitaux;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'patients', indexes: [
        new ORM\Index(name: 'idx_date_naissance', columns: ["date_naissance"]),
        new ORM\Index(name: 'idx_hopital', columns: ["hopital_id"]),
        new ORM\Index(name: 'idx_nom_prenom', columns: ["nom", "prenom"]),
        new ORM\Index(name: 'idx_numero_identite', columns: ["numero_identite"])
    ])]
class Patients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 50, precision: 10)]
    private string $numeroDossier = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $nom = '';

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $prenom = '';

    #[ORM\Column(type: 'date', precision: 10)]
    private \DateTimeInterface $dateNaissance;

    #[ORM\Column(type: 'string', length: 1, precision: 10, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $numeroIdentite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $typeIdentite = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 10, precision: 10, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $contactUrgenceNom = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $contactUrgenceTelephone = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $contactUrgenceLien = null;

    #[ORM\Column(type: 'string', length: 5, precision: 10, nullable: true)]
    private ?string $groupeSanguin = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $antecedentsMedicaux = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $antecedentsChirurgicaux = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $medicamentsActuels = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statutCivil = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $languePreference = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $photoPatient = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    // Antécédents familiaux
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliauxPere = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliauxMere = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliauxEnfants = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliauxEpouse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $antecedentsFamiliauxAutres = null;

    // Vaccinations
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $historiqueVaccinations = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDerniereVaccination = null;

    // Autres informations critiques
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $habitudesVie = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $facteursRisque = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observationsGenerales = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'date_derniere_mise_a_jour_dossier')]
    private ?\DateTimeInterface $dateDerniereMiseAJourDossier = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

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

    public function getNumeroDossier(): string
    {
        return $this->numeroDossier;
    }

    public function setNumeroDossier(string $numeroDossier): static
    {
        $this->numeroDossier = $numeroDossier;
        return $this;
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

    public function getDateNaissance(): \DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
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

    public function getContactUrgenceTelephone(): ?string
    {
        return $this->contactUrgenceTelephone;
    }

    public function setContactUrgenceTelephone(?string $contactUrgenceTelephone): static
    {
        $this->contactUrgenceTelephone = $contactUrgenceTelephone;
        return $this;
    }

    public function getContactUrgenceLien(): ?string
    {
        return $this->contactUrgenceLien;
    }

    public function setContactUrgenceLien(?string $contactUrgenceLien): static
    {
        $this->contactUrgenceLien = $contactUrgenceLien;
        return $this;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin(?string $groupeSanguin): static
    {
        $this->groupeSanguin = $groupeSanguin;
        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedentsMedicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedentsMedicaux): static
    {
        $this->antecedentsMedicaux = $antecedentsMedicaux;
        return $this;
    }

    public function getAntecedentsChirurgicaux(): ?string
    {
        return $this->antecedentsChirurgicaux;
    }

    public function setAntecedentsChirurgicaux(?string $antecedentsChirurgicaux): static
    {
        $this->antecedentsChirurgicaux = $antecedentsChirurgicaux;
        return $this;
    }

    public function getMedicamentsActuels(): ?string
    {
        return $this->medicamentsActuels;
    }

    public function setMedicamentsActuels(?string $medicamentsActuels): static
    {
        $this->medicamentsActuels = $medicamentsActuels;
        return $this;
    }

    public function getStatutCivil(): ?string
    {
        return $this->statutCivil;
    }

    public function setStatutCivil(?string $statutCivil): static
    {
        $this->statutCivil = $statutCivil;
        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;
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

    public function getLanguePreference(): ?string
    {
        return $this->languePreference;
    }

    public function setLanguePreference(?string $languePreference): static
    {
        $this->languePreference = $languePreference;
        return $this;
    }

    public function getPhotoPatient(): ?string
    {
        return $this->photoPatient;
    }

    public function setPhotoPatient(?string $photoPatient): static
    {
        $this->photoPatient = $photoPatient;
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    // Antécédents familiaux - Père
    public function getAntecedentsFamiliauxPere(): ?string
    {
        return $this->antecedentsFamiliauxPere;
    }

    public function setAntecedentsFamiliauxPere(?string $antecedentsFamiliauxPere): static
    {
        $this->antecedentsFamiliauxPere = $antecedentsFamiliauxPere;
        return $this;
    }

    // Antécédents familiaux - Mère
    public function getAntecedentsFamiliauxMere(): ?string
    {
        return $this->antecedentsFamiliauxMere;
    }

    public function setAntecedentsFamiliauxMere(?string $antecedentsFamiliauxMere): static
    {
        $this->antecedentsFamiliauxMere = $antecedentsFamiliauxMere;
        return $this;
    }

    // Antécédents familiaux - Enfants
    public function getAntecedentsFamiliauxEnfants(): ?string
    {
        return $this->antecedentsFamiliauxEnfants;
    }

    public function setAntecedentsFamiliauxEnfants(?string $antecedentsFamiliauxEnfants): static
    {
        $this->antecedentsFamiliauxEnfants = $antecedentsFamiliauxEnfants;
        return $this;
    }

    // Antécédents familiaux - Épouse/Conjoint
    public function getAntecedentsFamiliauxEpouse(): ?string
    {
        return $this->antecedentsFamiliauxEpouse;
    }

    public function setAntecedentsFamiliauxEpouse(?string $antecedentsFamiliauxEpouse): static
    {
        $this->antecedentsFamiliauxEpouse = $antecedentsFamiliauxEpouse;
        return $this;
    }

    // Antécédents familiaux - Autres
    public function getAntecedentsFamiliauxAutres(): ?string
    {
        return $this->antecedentsFamiliauxAutres;
    }

    public function setAntecedentsFamiliauxAutres(?string $antecedentsFamiliauxAutres): static
    {
        $this->antecedentsFamiliauxAutres = $antecedentsFamiliauxAutres;
        return $this;
    }

    // Historique des vaccinations
    public function getHistoriqueVaccinations(): ?string
    {
        return $this->historiqueVaccinations;
    }

    public function setHistoriqueVaccinations(?string $historiqueVaccinations): static
    {
        $this->historiqueVaccinations = $historiqueVaccinations;
        return $this;
    }

    // Date dernière vaccination
    public function getDateDerniereVaccination(): ?\DateTimeInterface
    {
        return $this->dateDerniereVaccination;
    }

    public function setDateDerniereVaccination(?\DateTimeInterface $dateDerniereVaccination): static
    {
        $this->dateDerniereVaccination = $dateDerniereVaccination;
        return $this;
    }

    // Habitudes de vie
    public function getHabitudesVie(): ?string
    {
        return $this->habitudesVie;
    }

    public function setHabitudesVie(?string $habitudesVie): static
    {
        $this->habitudesVie = $habitudesVie;
        return $this;
    }

    // Facteurs de risque
    public function getFacteursRisque(): ?string
    {
        return $this->facteursRisque;
    }

    public function setFacteursRisque(?string $facteursRisque): static
    {
        $this->facteursRisque = $facteursRisque;
        return $this;
    }

    // Observations générales
    public function getObservationsGenerales(): ?string
    {
        return $this->observationsGenerales;
    }

    public function setObservationsGenerales(?string $observationsGenerales): static
    {
        $this->observationsGenerales = $observationsGenerales;
        return $this;
    }

    // Date dernière mise à jour du dossier
    public function getDateDerniereMiseAJourDossier(): ?\DateTimeInterface
    {
        return $this->dateDerniereMiseAJourDossier;
    }

    public function setDateDerniereMiseAJourDossier(?\DateTimeInterface $dateDerniereMiseAJourDossier): static
    {
        $this->dateDerniereMiseAJourDossier = $dateDerniereMiseAJourDossier;
        return $this;
    }

}

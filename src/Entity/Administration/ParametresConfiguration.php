<?php

namespace App\Entity\Administration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'parametres_configuration', indexes: [
        new ORM\Index(name: 'devise_defaut_id', columns: ["devise_defaut_id"]),
        new ORM\Index(name: 'mode_paiement_defaut_id', columns: ["mode_paiement_defaut_id"]),
        new ORM\Index(name: 'IDX_9052176ACC0FBF92', columns: ["hopital_id"])
    ])]
class ParametresConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 255, precision: 10)]
    private string $cle = '';

    #[ORM\Column(type: 'text', precision: 10, nullable: true)]
    private ?string $valeur = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $iconeUrl = null;

    #[ORM\Column(type: 'string', length: 7, precision: 10, nullable: true)]
    private ?string $couleurPrimaire = null;

    #[ORM\Column(type: 'string', length: 7, precision: 10, nullable: true)]
    private ?string $couleurSecondaire = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxPaiementDefaut = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxTvaDefaut = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $emailExpediteur = null;

    #[ORM\Column(type: 'string', length: 255, precision: 10, nullable: true)]
    private ?string $emailSupport = null;

    #[ORM\Column(type: 'string', length: 20, precision: 10, nullable: true)]
    private ?string $telephoneSupport = null;

    #[ORM\Column(type: 'string', length: 500, precision: 10, nullable: true)]
    private ?string $urlLogoEmail = null;

    #[ORM\Column(type: 'text', length: 65535, precision: 10, nullable: true)]
    private ?string $signatureEmail = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(targetEntity: Hopitaux::class)]
    #[ORM\JoinColumn(name: 'hopital_id', referencedColumnName: 'id', nullable: false)]
    private Hopitaux $hopitalId;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: 'devise_defaut_id', referencedColumnName: 'id', nullable: false)]
    private Devises $deviseDefautId;

    #[ORM\ManyToOne(targetEntity: ModesPaiement::class)]
    #[ORM\JoinColumn(name: 'mode_paiement_defaut_id', referencedColumnName: 'id', nullable: false)]
    private ModesPaiement $modePaiementDefautId;

    public function __construct()
    {
        $this->dateModification = new DateTimeImmutable();
        $this->actif = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCle(): string
    {
        return $this->cle;
    }

    public function setCle(string $cle): static
    {
        $this->cle = $cle;
        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(?string $valeur): static
    {
        $this->valeur = $valeur;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;
        return $this;
    }

    public function getIconeUrl(): ?string
    {
        return $this->iconeUrl;
    }

    public function setIconeUrl(?string $iconeUrl): static
    {
        $this->iconeUrl = $iconeUrl;
        return $this;
    }

    public function getCouleurPrimaire(): ?string
    {
        return $this->couleurPrimaire;
    }

    public function setCouleurPrimaire(?string $couleurPrimaire): static
    {
        $this->couleurPrimaire = $couleurPrimaire;
        return $this;
    }

    public function getCouleurSecondaire(): ?string
    {
        return $this->couleurSecondaire;
    }

    public function setCouleurSecondaire(?string $couleurSecondaire): static
    {
        $this->couleurSecondaire = $couleurSecondaire;
        return $this;
    }

    public function getTauxPaiementDefaut(): ?string
    {
        return $this->tauxPaiementDefaut;
    }

    public function setTauxPaiementDefaut(?string $tauxPaiementDefaut): static
    {
        $this->tauxPaiementDefaut = $tauxPaiementDefaut;
        return $this;
    }

    public function getTauxTvaDefaut(): ?string
    {
        return $this->tauxTvaDefaut;
    }

    public function setTauxTvaDefaut(?string $tauxTvaDefaut): static
    {
        $this->tauxTvaDefaut = $tauxTvaDefaut;
        return $this;
    }

    public function getEmailExpediteur(): ?string
    {
        return $this->emailExpediteur;
    }

    public function setEmailExpediteur(?string $emailExpediteur): static
    {
        $this->emailExpediteur = $emailExpediteur;
        return $this;
    }

    public function getEmailSupport(): ?string
    {
        return $this->emailSupport;
    }

    public function setEmailSupport(?string $emailSupport): static
    {
        $this->emailSupport = $emailSupport;
        return $this;
    }

    public function getTelephoneSupport(): ?string
    {
        return $this->telephoneSupport;
    }

    public function setTelephoneSupport(?string $telephoneSupport): static
    {
        $this->telephoneSupport = $telephoneSupport;
        return $this;
    }

    public function getUrlLogoEmail(): ?string
    {
        return $this->urlLogoEmail;
    }

    public function setUrlLogoEmail(?string $urlLogoEmail): static
    {
        $this->urlLogoEmail = $urlLogoEmail;
        return $this;
    }

    public function getSignatureEmail(): ?string
    {
        return $this->signatureEmail;
    }

    public function setSignatureEmail(?string $signatureEmail): static
    {
        $this->signatureEmail = $signatureEmail;
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

    public function getDeviseDefautId(): Devises
    {
        return $this->deviseDefautId;
    }

    public function setDeviseDefautId(Devises $deviseDefautId): static
    {
        $this->deviseDefautId = $deviseDefautId;
        return $this;
    }

    public function getModePaiementDefautId(): ModesPaiement
    {
        return $this->modePaiementDefautId;
    }

    public function setModePaiementDefautId(ModesPaiement $modePaiementDefautId): static
    {
        $this->modePaiementDefautId = $modePaiementDefautId;
        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

}

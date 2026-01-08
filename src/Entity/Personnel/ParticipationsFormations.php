<?php

namespace App\Entity\Personnel;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'participations_formations', indexes: [
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"]),
        new ORM\Index(name: 'IDX_643F96125200282E', columns: ["formation_id"])
    ])]
class ParticipationsFormations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'date', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateParticipation = null;

    #[ORM\Column(type: 'string', length: 50, precision: 10, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $noteEvaluation = null;

    #[ORM\Column(type: 'boolean', precision: 10, nullable: true)]
    private ?bool $certificatObtenu = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Formations::class)]
    #[ORM\JoinColumn(name: 'formation_id', referencedColumnName: 'id', nullable: false)]
    private Formations $formationId;

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

    public function getDateParticipation(): ?\DateTimeInterface
    {
        return $this->dateParticipation;
    }

    public function setDateParticipation(?\DateTimeInterface $dateParticipation): static
    {
        $this->dateParticipation = $dateParticipation;
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

    public function getNoteEvaluation(): ?string
    {
        return $this->noteEvaluation;
    }

    public function setNoteEvaluation(?string $noteEvaluation): static
    {
        $this->noteEvaluation = $noteEvaluation;
        return $this;
    }

    public function getCertificatObtenu(): ?bool
    {
        return $this->certificatObtenu;
    }

    public function setCertificatObtenu(?bool $certificatObtenu): static
    {
        $this->certificatObtenu = $certificatObtenu;
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

    public function getFormationId(): Formations
    {
        return $this->formationId;
    }

    public function setFormationId(Formations $formationId): static
    {
        $this->formationId = $formationId;
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

    public function isCertificatObtenu(): ?bool
    {
        return $this->certificatObtenu;
    }

}

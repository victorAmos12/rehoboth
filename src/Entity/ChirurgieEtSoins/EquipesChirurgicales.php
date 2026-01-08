<?php

namespace App\Entity\ChirurgieEtSoins;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'equipes_chirurgicales', indexes: [
        new ORM\Index(name: 'utilisateur_id', columns: ["utilisateur_id"]),
        new ORM\Index(name: 'IDX_A19F9ABF3D865311', columns: ["planning_id"])
    ])]
class EquipesChirurgicales
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 100, precision: 10, nullable: true)]
    private ?string $roleEquipe = null;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: PlanningOperatoire::class)]
    #[ORM\JoinColumn(name: 'planning_id', referencedColumnName: 'id', nullable: false)]
    private PlanningOperatoire $planningId;

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

    public function getRoleEquipe(): ?string
    {
        return $this->roleEquipe;
    }

    public function setRoleEquipe(?string $roleEquipe): static
    {
        $this->roleEquipe = $roleEquipe;
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

    public function getPlanningId(): PlanningOperatoire
    {
        return $this->planningId;
    }

    public function setPlanningId(PlanningOperatoire $planningId): static
    {
        $this->planningId = $planningId;
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

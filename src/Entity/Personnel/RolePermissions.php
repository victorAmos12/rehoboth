<?php

namespace App\Entity\Personnel;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'role_permissions', indexes: [
        new ORM\Index(name: 'permission_id', columns: ["permission_id"]),
        new ORM\Index(name: 'IDX_1FBA94E6D60322AC', columns: ["role_id"])
    ])]
class RolePermissions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', precision: 10)]
    private int $id = 0;

    #[ORM\Column(type: 'datetime', precision: 10, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false)]
    private Roles $roleId;

    #[ORM\ManyToOne(targetEntity: Permissions::class)]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id', nullable: false)]
    private Permissions $permissionId;

    public function __construct()
    {
        $this->dateCreation = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getRoleId(): Roles
    {
        return $this->roleId;
    }

    public function setRoleId(Roles $roleId): static
    {
        $this->roleId = $roleId;
        return $this;
    }

    public function getPermissionId(): Permissions
    {
        return $this->permissionId;
    }

    public function setPermissionId(Permissions $permissionId): static
    {
        $this->permissionId = $permissionId;
        return $this;
    }

}

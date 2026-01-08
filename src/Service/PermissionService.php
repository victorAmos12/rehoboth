<?php

namespace App\Service;

use App\Entity\Personnel\Utilisateurs;
use App\Entity\Personnel\Roles;
use App\Entity\Personnel\Permissions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service de gestion des permissions et du contrôle d'accès
 * 
 * Fournit des méthodes pour vérifier les permissions et les rôles
 */
class PermissionService
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    /**
     * Vérifie si l'utilisateur actuel a un rôle spécifique
     * 
     * @param string $role Code du rôle (ex: 'ADMIN', 'MEDECIN')
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof Utilisateurs) {
            return false;
        }

        return $user->getRoleId()->getCode() === $role;
    }

    /**
     * Vérifie si l'utilisateur actuel a l'un des rôles spécifiés
     * 
     * @param array $roles Codes des rôles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof Utilisateurs) {
            return false;
        }

        return in_array($user->getRoleId()->getCode(), $roles);
    }

    /**
     * Vérifie si l'utilisateur actuel a une permission spécifique
     * 
     * @param string $permissionCode Code de la permission
     * @return bool
     */
    public function hasPermission(string $permissionCode): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof Utilisateurs) {
            return false;
        }

        $role = $user->getRoleId();
        if (!$role) {
            return false;
        }

        $permission = $this->entityManager->getRepository(Permissions::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.roles', 'r')
            ->where('p.code = :code')
            ->andWhere('r.id = :roleId')
            ->setParameter('code', $permissionCode)
            ->setParameter('roleId', $role->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return $permission !== null;
    }

    /**
     * Vérifie si l'utilisateur actuel a l'une des permissions spécifiées
     * 
     * @param array $permissionCodes Codes des permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if ($this->hasPermission($code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si l'utilisateur actuel a toutes les permissions spécifiées
     * 
     * @param array $permissionCodes Codes des permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissionCodes): bool
    {
        foreach ($permissionCodes as $code) {
            if (!$this->hasPermission($code)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Récupère toutes les permissions de l'utilisateur actuel
     * 
     * @return array Codes des permissions
     */
    public function getUserPermissions(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof Utilisateurs) {
            return [];
        }

        $role = $user->getRoleId();
        if (!$role) {
            return [];
        }

        $permissions = $this->entityManager->getRepository(Permissions::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.roles', 'r')
            ->where('r.id = :roleId')
            ->setParameter('roleId', $role->getId())
            ->getQuery()
            ->getResult();

        return array_map(fn($p) => $p->getCode(), $permissions);
    }

    /**
     * Récupère le rôle de l'utilisateur actuel
     * 
     * @return Roles|null
     */
    public function getUserRole(): ?Roles
    {
        $user = $this->security->getUser();
        if (!$user instanceof Utilisateurs) {
            return null;
        }

        return $user->getRoleId();
    }

    /**
     * Récupère l'utilisateur actuel
     * 
     * @return Utilisateurs|null
     */
    public function getCurrentUser(): ?Utilisateurs
    {
        $user = $this->security->getUser();
        return $user instanceof Utilisateurs ? $user : null;
    }

    /**
     * Vérifie l'accès et lève une exception si non autorisé
     * 
     * @param string $role Code du rôle requis
     * @throws AccessDeniedException
     */
    public function requireRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            throw new AccessDeniedException("Accès refusé. Rôle requis: $role");
        }
    }

    /**
     * Vérifie l'accès et lève une exception si non autorisé (l'un des rôles)
     * 
     * @param array $roles Codes des rôles
     * @throws AccessDeniedException
     */
    public function requireAnyRole(array $roles): void
    {
        if (!$this->hasAnyRole($roles)) {
            throw new AccessDeniedException("Accès refusé. Rôles requis: " . implode(', ', $roles));
        }
    }

    /**
     * Vérifie l'accès et lève une exception si non autorisé
     * 
     * @param string $permissionCode Code de la permission
     * @throws AccessDeniedException
     */
    public function requirePermission(string $permissionCode): void
    {
        if (!$this->hasPermission($permissionCode)) {
            throw new AccessDeniedException("Accès refusé. Permission requise: $permissionCode");
        }
    }

    /**
     * Vérifie l'accès et lève une exception si non autorisé (l'une des permissions)
     * 
     * @param array $permissionCodes Codes des permissions
     * @throws AccessDeniedException
     */
    public function requireAnyPermission(array $permissionCodes): void
    {
        if (!$this->hasAnyPermission($permissionCodes)) {
            throw new AccessDeniedException("Accès refusé. Permissions requises: " . implode(', ', $permissionCodes));
        }
    }

    /**
     * Vérifie l'accès et lève une exception si non autorisé (toutes les permissions)
     * 
     * @param array $permissionCodes Codes des permissions
     * @throws AccessDeniedException
     */
    public function requireAllPermissions(array $permissionCodes): void
    {
        if (!$this->hasAllPermissions($permissionCodes)) {
            throw new AccessDeniedException("Accès refusé. Permissions requises: " . implode(', ', $permissionCodes));
        }
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Vérifie si l'utilisateur est RH
     * 
     * @return bool
     */
    public function isRH(): bool
    {
        return $this->hasRole('RH');
    }

    /**
     * Vérifie si l'utilisateur est médecin
     * 
     * @return bool
     */
    public function isMedecin(): bool
    {
        return $this->hasRole('MEDECIN');
    }

    /**
     * Vérifie si l'utilisateur est infirmier
     * 
     * @return bool
     */
    public function isInfirmier(): bool
    {
        return $this->hasRole('INFIRMIER');
    }

    /**
     * Vérifie si l'utilisateur est pharmacien
     * 
     * @return bool
     */
    public function isPharmacien(): bool
    {
        return $this->hasRole('PHARMACIEN');
    }

    /**
     * Vérifie si l'utilisateur est réceptionniste
     * 
     * @return bool
     */
    public function isReceptionniste(): bool
    {
        return $this->hasRole('RECEPTIONNISTE');
    }
}

<?php

namespace App\Service;

use App\Entity\Administration\Services;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion des permissions pour les cartes de services
 * 
 * Détermine quels utilisateurs peuvent accéder à quelles cartes de services
 * et quel niveau de détail ils peuvent voir
 */
class ServiceCardPermissionService
{
    // Niveaux de détail
    public const DETAIL_NONE = 0;           // Pas d'accès
    public const DETAIL_BASIC = 1;          // Informations basiques
    public const DETAIL_INTERMEDIATE = 2;   // Informations intermédiaires
    public const DETAIL_FULL = 3;           // Toutes les informations

    // Rôles spéciaux
    private const ADMIN_ROLES = ['ADMIN', 'SUPER_ADMIN', 'ADMINISTRATEUR'];
    private const MANAGER_ROLES = ['MANAGER', 'CHEF_SERVICE', 'DIRECTEUR'];
    private const STAFF_ROLES = ['MEDECIN', 'INFIRMIER', 'PERSONNEL_MEDICAL'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PermissionService $permissionService,
    ) {
    }

    /**
     * Vérifie si un utilisateur peut voir une carte de service
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param Services $service Le service
     * @return bool True si l'utilisateur peut voir la carte
     */
    public function canViewServiceCard(Utilisateurs $utilisateur, Services $service): bool
    {
        // L'utilisateur doit être actif
        if (!$utilisateur->getActif()) {
            return false;
        }

        // Le service doit être actif
        if (!$service->getActif()) {
            // Sauf si l'utilisateur est admin
            if (!$this->isAdmin($utilisateur)) {
                return false;
            }
        }

        // Vérifier l'hôpital
        if ($utilisateur->getHopitalId()->getId() !== $service->getHopitalId()->getId()) {
            // Sauf si l'utilisateur est admin
            if (!$this->isAdmin($utilisateur)) {
                return false;
            }
        }

        // Vérifier les permissions spécifiques
        return $this->hasServicePermission($utilisateur, $service);
    }

    /**
     * Détermine le niveau de détail que l'utilisateur peut voir
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param Services $service Le service
     * @return int Niveau de détail (0-3)
     */
    public function getDetailLevel(Utilisateurs $utilisateur, Services $service): int
    {
        // Pas d'accès
        if (!$this->canViewServiceCard($utilisateur, $service)) {
            return self::DETAIL_NONE;
        }

        $roleCode = $utilisateur->getRoleId()->getCode();

        // Admin: accès complet
        if ($this->isAdmin($utilisateur)) {
            return self::DETAIL_FULL;
        }

        // Chef de service: accès complet à son service
        if ($this->isChefService($utilisateur, $service)) {
            return self::DETAIL_FULL;
        }

        // Manager/Directeur: accès intermédiaire
        if ($this->isManager($utilisateur)) {
            return self::DETAIL_INTERMEDIATE;
        }

        // Personnel médical: accès basique
        if ($this->isStaff($utilisateur)) {
            return self::DETAIL_BASIC;
        }

        // Autres: accès basique
        return self::DETAIL_BASIC;
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique pour le service
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param Services $service Le service
     * @return bool True si l'utilisateur a la permission
     */
    private function hasServicePermission(Utilisateurs $utilisateur, Services $service): bool
    {
        // Admin: accès à tous les services
        if ($this->isAdmin($utilisateur)) {
            return true;
        }

        // Vérifier si l'utilisateur est affecté au service
        if ($this->isAffectedToService($utilisateur, $service)) {
            return true;
        }

        // Vérifier les permissions via le PermissionService
        if ($this->permissionService->hasPermission($utilisateur, 'view_all_services')) {
            return true;
        }

        // Vérifier les permissions spécifiques au service
        $permissionCode = 'view_service_' . $service->getCode();
        if ($this->permissionService->hasPermission($utilisateur, $permissionCode)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur est affecté à un service
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param Services $service Le service
     * @return bool True si l'utilisateur est affecté
     */
    private function isAffectedToService(Utilisateurs $utilisateur, Services $service): bool
    {
        $affectations = $this->entityManager
            ->getRepository('App:Personnel\AffectationsUtilisateurs')
            ->findBy([
                'utilisateurId' => $utilisateur,
                'serviceId' => $service,
                'actif' => true,
            ]);

        return !empty($affectations);
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @return bool True si admin
     */
    private function isAdmin(Utilisateurs $utilisateur): bool
    {
        $roleCode = $utilisateur->getRoleId()->getCode();
        return in_array($roleCode, self::ADMIN_ROLES);
    }

    /**
     * Vérifie si l'utilisateur est manager/directeur
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @return bool True si manager
     */
    private function isManager(Utilisateurs $utilisateur): bool
    {
        $roleCode = $utilisateur->getRoleId()->getCode();
        return in_array($roleCode, self::MANAGER_ROLES);
    }

    /**
     * Vérifie si l'utilisateur est personnel médical
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @return bool True si personnel médical
     */
    private function isStaff(Utilisateurs $utilisateur): bool
    {
        $roleCode = $utilisateur->getRoleId()->getCode();
        return in_array($roleCode, self::STAFF_ROLES);
    }

    /**
     * Vérifie si l'utilisateur est chef de service
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param Services $service Le service
     * @return bool True si chef du service
     */
    private function isChefService(Utilisateurs $utilisateur, Services $service): bool
    {
        $roleCode = $utilisateur->getRoleId()->getCode();
        
        if ($roleCode !== 'CHEF_SERVICE') {
            return false;
        }

        // Vérifier si l'utilisateur est chef de ce service
        return $service->getChefServiceId() === $utilisateur->getId();
    }

    /**
     * Récupère tous les services accessibles par un utilisateur
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @return array Liste des services accessibles
     */
    public function getAccessibleServices(Utilisateurs $utilisateur): array
    {
        $servicesRepository = $this->entityManager->getRepository(Services::class);

        // Admin: tous les services
        if ($this->isAdmin($utilisateur)) {
            return $servicesRepository->findBy(['actif' => true]);
        }

        // Récupérer les services du même hôpital
        $services = $servicesRepository->findBy([
            'hopitalId' => $utilisateur->getHopitalId(),
            'actif' => true,
        ]);

        // Filtrer selon les permissions
        return array_filter(
            $services,
            fn(Services $service) => $this->hasServicePermission($utilisateur, $service)
        );
    }

    /**
     * Récupère les services affectés à un utilisateur
     * 
     * @param Utilisateurs $utilisateur L'utilisateur
     * @return array Liste des services affectés
     */
    public function getAffectedServices(Utilisateurs $utilisateur): array
    {
        $affectationsRepository = $this->entityManager
            ->getRepository('App:Personnel\AffectationsUtilisateurs');

        $affectations = $affectationsRepository->findBy([
            'utilisateurId' => $utilisateur,
            'actif' => true,
        ]);

        return array_map(
            fn($affectation) => $affectation->getServiceId(),
            $affectations
        );
    }
}

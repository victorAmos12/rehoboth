<?php

namespace App\Service;

use App\Entity\Administration\Services;
use App\Entity\Personnel\Utilisateurs;
use Knp\Snappy\Pdf;
use Knp\Snappy\Image;
use Twig\Environment;
use Exception;

/**
 * Service de génération de cartes de services
 * 
 * Gère la génération de cartes professionnelles pour les services hospitaliers
 * avec support pour PDF et images, en respectant les permissions utilisateur
 */
class ServiceCardGeneratorService
{
    public function __construct(
        private Pdf $pdf,
        private Image $image,
        private Environment $twig,
        private ServiceCardPermissionService $permissionService,
    ) {
    }

    /**
     * Génère une carte de service au format PDF
     * 
     * @param Services $service Le service à représenter
     * @param Utilisateurs $utilisateur L'utilisateur qui demande la carte
     * @param array $options Options de génération (format, couleurs, etc.)
     * @return string Contenu PDF en binaire
     * @throws Exception Si l'utilisateur n'a pas les permissions
     */
    public function generateServiceCardPdf(
        Services $service,
        Utilisateurs $utilisateur,
        array $options = []
    ): string {
        // Vérifier les permissions
        if (!$this->permissionService->canViewServiceCard($utilisateur, $service)) {
            throw new Exception('Vous n\'avez pas les permissions pour accéder à cette carte de service');
        }

        // Préparer les données
        $data = $this->prepareServiceData($service, $utilisateur, $options);

        // Générer le HTML (ISO ID-1 recto/verso)
        $html = $this->twig->render('service_card/service_card_iso_id1.html.twig', $data);

        // Générer le PDF avec Snappy au format carte bancaire (ISO ID-1)
        return $this->pdf->getOutputFromHtml($html, [
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            // on laisse le template gérer @page size
            'disable-smart-shrinking' => true,
            'dpi' => 300,
            'print-media-type' => true,
            'enable-local-file-access' => true,
        ]);
    }

    /**
     * Génère une carte de service au format image (PNG/JPG)
     * 
     * @param Services $service Le service à représenter
     * @param Utilisateurs $utilisateur L'utilisateur qui demande la carte
     * @param string $format Format de l'image (png, jpg)
     * @param array $options Options de génération
     * @return string Contenu image en binaire
     * @throws Exception Si l'utilisateur n'a pas les permissions
     */
    public function generateServiceCardImage(
        Services $service,
        Utilisateurs $utilisateur,
        string $format = 'png',
        array $options = []
    ): string {
        // Vérifier les permissions
        if (!$this->permissionService->canViewServiceCard($utilisateur, $service)) {
            throw new Exception('Vous n\'avez pas les permissions pour accéder à cette carte de service');
        }

        // Préparer les données
        $data = $this->prepareServiceData($service, $utilisateur, $options);

        // Générer le HTML
        $html = $this->twig->render('service_card/image_card.html.twig', $data);

        // Générer l'image avec Snappy
        $imageOptions = [
            'width' => 1200,
            'height' => 800,
            'quality' => 95,
            'enable-local-file-access' => true,
        ];

        if ($format === 'jpg') {
            $imageOptions['format'] = 'jpg';
        }

        return $this->image->getOutputFromHtml($html, $imageOptions);
    }

    /**
     * Génère plusieurs cartes de services en un seul PDF
     * 
     * @param array $services Liste des services
     * @param Utilisateurs $utilisateur L'utilisateur qui demande les cartes
     * @param array $options Options de génération
     * @return string Contenu PDF en binaire
     */
    public function generateMultipleServiceCardsPdf(
        array $services,
        Utilisateurs $utilisateur,
        array $options = []
    ): string {
        // Filtrer les services selon les permissions
        $accessibleServices = array_filter(
            $services,
            fn(Services $service) => $this->permissionService->canViewServiceCard($utilisateur, $service)
        );

        if (empty($accessibleServices)) {
            throw new Exception('Vous n\'avez accès à aucune carte de service');
        }

        // Préparer les données pour tous les services
        $servicesData = array_map(
            fn(Services $service) => $this->prepareServiceData($service, $utilisateur, $options),
            $accessibleServices
        );

        // Générer le HTML multi-pages
        $html = $this->twig->render('service_card/pdf_cards_multiple.html.twig', [
            'services' => $servicesData,
            'user' => $utilisateur,
            'generatedAt' => new \DateTime(),
        ]);

        // Générer le PDF
        return $this->pdf->getOutputFromHtml($html, [
            'margin-top' => 10,
            'margin-right' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'page-size' => 'A4',
            'orientation' => 'Portrait',
            'dpi' => 300,
            'enable-local-file-access' => true,
        ]);
    }

    /**
     * Prépare les données d'un service pour la génération de carte
     * 
     * @param Services $service Le service
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param array $options Options supplémentaires
     * @return array Données formatées
     */
    private function prepareServiceData(
        Services $service,
        Utilisateurs $utilisateur,
        array $options = []
    ): array {
        // Déterminer le niveau de détail selon les permissions
        $detailLevel = $this->permissionService->getDetailLevel($utilisateur, $service);

        // Backgrounds recto/verso - Support pour mode moderne avec couleurs
        $designMode = $options['design_mode'] ?? 'classic';
        if ($designMode === 'modern') {
            // Mode moderne: utiliser des couleurs CSS au lieu d'images
            $frontBgPath = 'modern-gradient'; // Valeur spéciale pour indiquer au template d'utiliser CSS
            $backBgPath = 'modern-solid';     // Valeur spéciale pour indiquer au template d'utiliser CSS
        } else {
            // Mode classique: utiliser les images spécifiées ou par défaut
            $frontBgPath = ($options['front_bg_path'] ?? null) ?: '/PXL_20260111_142524160.MP.jpg';
            $backBgPath  = ($options['back_bg_path'] ?? null) ?: '/PXL_20260111_142536686.jpg';
        }

        // Photo profil: chemin relatif stocké en DB (/uploads/...), ou null
        $photoPath = $utilisateur->getPhotoProfil();

        $data = [
            // Variables attendues par le template ISO
            'photo_url' => $photoPath,
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'login' => $utilisateur->getLogin(),
                'email' => $utilisateur->getEmail(),
                'telephone' => $utilisateur->getTelephone(),
            ],
            'hopital_nom' => $service->getHopitalId()->getNom(),
            'hopital_logo' => $service->getHopitalId()->getLogoUrl(),
            'role_nom' => $utilisateur->getRoleId()->getNom(),
            'service_nom' => $service->getNom(),
            'service_color' => $service->getCouleurService() ?? '#2980B9',
            'specialite' => $utilisateur->getSpecialiteId() ? $utilisateur->getSpecialiteId()->getNom() : '',
            'nationalite' => $utilisateur->getNationalite() ?? '',
            'adresse_physique' => $utilisateur->getAdressePhysique() ?? '',
            'date_livraison' => $utilisateur->getDateLivraison() ? $utilisateur->getDateLivraison()->format('d/m/Y') : date('d/m/Y'),
            'validite' => $utilisateur->getValidite() ?? '1 an',

            // Données existantes (compat)
            'service' => [
                'id' => $service->getId(),
                'code' => $service->getCode(),
                'nom' => $service->getNom(),
                'typeService' => $service->getTypeService(),
                'hopital' => $service->getHopitalId()->getNom(),
                'couleur' => $service->getCouleurService() ?? '#2980B9',
                'logo' => $service->getLogoService(),
                'logoService' => $service->getLogoService(),
                'actif' => $service->getActif(),
            ],
            'user' => [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'role' => $utilisateur->getRoleId()->getNom(),
                'email' => $utilisateur->getEmail(),
                'photo_profil' => $photoPath,
            ],
            'detailLevel' => $detailLevel,
            'generatedAt' => new \DateTime(),
            'options' => $options,
        ];

        // Ajouter les informations selon le niveau de détail
        if ($detailLevel >= ServiceCardPermissionService::DETAIL_BASIC) {
            $data['service']['description'] = $service->getDescription();
            $data['service']['localisation'] = $service->getLocalisation();
            $data['service']['telephone'] = $service->getTelephone();
        }

        if ($detailLevel >= ServiceCardPermissionService::DETAIL_INTERMEDIATE) {
            $data['service']['nombreLits'] = $service->getNombreLits();
            $data['service']['email'] = $service->getEmail();
            $data['service']['chefServiceId'] = $service->getChefServiceId();
        }

        if ($detailLevel >= ServiceCardPermissionService::DETAIL_FULL) {
            $data['service']['dateCreation'] = $service->getDateCreation();
            $data['service']['hopitalId'] = $service->getHopitalId()->getId();
        }

        return $data;
    }

    /**
     * Génère une carte de service au format HTML (pour aperçu)
     * 
     * @param Services $service Le service
     * @param Utilisateurs $utilisateur L'utilisateur
     * @param array $options Options de génération
     * @return string HTML de la carte
     */
    public function generateServiceCardHtml(
        Services $service,
        Utilisateurs $utilisateur,
        array $options = []
    ): string {
        // Vérifier les permissions
        if (!$this->permissionService->canViewServiceCard($utilisateur, $service)) {
            throw new Exception('Vous n\'avez pas les permissions pour accéder à cette carte de service');
        }

        // Préparer les données
        $data = $this->prepareServiceData($service, $utilisateur, $options);

        // Générer et retourner le HTML (aperçu ISO ID-1)
        return $this->twig->render('service_card/service_card_iso_id1.html.twig', $data);
    }
}

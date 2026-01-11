<?php

namespace App\Controller\Api\PatientController;

use App\Entity\Patients\Patients;
use App\Entity\Patients\Admissions;
use App\Entity\Patients\DossiersMedicaux;
use App\Entity\Patients\AssurancesPatients;
use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Services;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TCPDF;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion des patients
 * 
 * Gère:
 * - CRUD complet des patients (Create, Read, Update, Delete)
 * - Recherche et filtrage avancés
 * - Génération de documents PDF (fiche patient, dossier médical)
 * - Gestion des admissions
 * - Gestion des assurances
 * - Export de données
 * - Statistiques patients
 * 
 * Tous les endpoints retournent du JSON sauf les téléchargements PDF
 */
#[Route('/api/patients', name: 'api_patients_')]
class PatientController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     * 
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param ValidatorInterface $validator Validateur Symfony
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Récupère la liste de tous les patients avec pagination et filtrage
     * GET /api/patients
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            // Récupérer les paramètres de pagination et filtrage
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $hopitalId = $request->query->get('hopital_id');
            $actif = $request->query->get('actif');
            $sexe = $request->query->get('sexe');
            $sort = $request->query->get('sort', 'dateCreation');
            $order = strtoupper($request->query->get('order', 'DESC'));

            // Valider l'ordre de tri
            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            // Construire la requête
            $queryBuilder = $this->entityManager->getRepository(Patients::class)
                ->createQueryBuilder('p')
                ->leftJoin('p.hopitalId', 'h')
                ->addSelect('h');

            // Appliquer les filtres
            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('p.nom', ':search'),
                        $queryBuilder->expr()->like('p.prenom', ':search'),
                        $queryBuilder->expr()->like('p.numeroDossier', ':search'),
                        $queryBuilder->expr()->like('p.email', ':search'),
                        $queryBuilder->expr()->like('p.telephone', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($hopitalId) {
                $queryBuilder->andWhere('p.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('p.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            if ($sexe) {
                $queryBuilder->andWhere('p.sexe = :sexe')
                    ->setParameter('sexe', $sexe);
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer le tri et la pagination
            $queryBuilder->orderBy('p.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $patients = $queryBuilder->getQuery()->getResult();

            // Formater les données
            $data = array_map(function (Patients $patient) {
                return $this->formatPatientData($patient);
            }, $patients);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des patients: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails complets d'un patient
     * GET /api/patients/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($id);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            // Admissions = base de l'historique administratif/médical (hospitalisations)
            $admissions = $this->entityManager->getRepository(Admissions::class)
                ->findBy(['patientId' => $patient], ['dateAdmission' => 'DESC']);

            // TODO SIH: consultations, analyses/labo, imagerie, prescriptions, factures, paiements, rdv,
            // documents, assurances, logs d'audit...
            // Ces modules existent dans le projet mais ne sont pas encore reliés au patient dans ce contrôleur.

            $data = [
                'identite' => $this->formatPatientData($patient, true),
                'informations_personnelles' => [
                    'adresse' => $patient->getAdresse(),
                    'ville' => $patient->getVille(),
                    'code_postal' => $patient->getCodePostal(),
                    'telephone' => $patient->getTelephone(),
                    'email' => $patient->getEmail(),
                    'contact_urgence' => [
                        'nom' => $patient->getContactUrgenceNom(),
                        'telephone' => $patient->getContactUrgenceTelephone(),
                        'lien' => $patient->getContactUrgenceLien(),
                    ],
                ],
                'informations_medicales' => [
                    'groupe_sanguin' => $patient->getGroupeSanguin(),
                    'allergies' => $patient->getAllergies(),
                    'antecedents_medicaux' => $patient->getAntecedentsMedicaux(),
                    'antecedents_chirurgicaux' => $patient->getAntecedentsChirurgicaux(),
                    'medicaments_actuels' => $patient->getMedicamentsActuels(),
                    // Ces listes seront alimentées quand on raccordera les entités correspondantes:
                    'consultations' => [],
                    'demandes_analyses' => [],
                    'imagerie' => [],
                    'prescriptions' => [],
                ],
                'informations_administratives' => [
                    'hopital' => [
                        'id' => $patient->getHopitalId()->getId(),
                        'nom' => $patient->getHopitalId()->getNom(),
                    ],
                    'assurances' => [],
                    'rendez_vous' => [],
                    'factures' => [],
                    'paiements' => [],
                    'admissions' => array_map(function (Admissions $admission) {
                        return [
                            'id' => $admission->getId(),
                            'numeroAdmission' => $admission->getNumeroAdmission(),
                            'dateAdmission' => $admission->getDateAdmission()->format('c'),
                            'dateSortie' => $admission->getDateSortie()?->format('c'),
                            'statut' => $admission->getStatut(),
                            'typeAdmission' => $admission->getTypeAdmission(),
                            'motifAdmission' => $admission->getMotifAdmission(),
                            'diagnosticPrincipal' => $admission->getDiagnosticPrincipal(),
                        ];
                    }, $admissions),
                ],
                'documents' => [
                    // TODO SIH: upload/download, certificats, pièces (CNI, assurance, consentements)
                    'fichiers' => [],
                    'certificats' => [],
                ],
                'historique' => [
                    'date_creation' => $patient->getDateCreation()?->format('c'),
                    'date_modification' => $patient->getDateModification()?->format('c'),
                    // TODO SIH: historique détaillé des modifications (audit trail)
                    'modifications' => [],
                ],
            ];

            return $this->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du patient: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouveau patient
     * POST /api/patients
     * 
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les données requises
            $requiredFields = ['nom', 'prenom', 'dateNaissance', 'hopital_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '$field' est requis",
                    ], 400);
                }
            }

            // Vérifier que le numéro de dossier est unique
            // $existingPatient = $this->entityManager->getRepository(Patients::class)
            //     ->findOneBy(['numeroDossier' => $data['numeroDossier']]);

            // if ($existingPatient) {
            //     return $this->json([
            //         'success' => false,
            //         'error' => 'Un patient avec ce numéro de dossier existe déjà',
            //     ], 409);
            // }


            // Récupérer l'hôpital
            $hopital = $this->entityManager->getRepository(Hopitaux::class)
                ->find($data['hopital_id']);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            // Créer un numéro de dossier unique si non fourni
            if (empty($data['numeroDossier'])) {

                // Compter le nombre de patienrs existants pour générer un numéro unique
                $count = $this->entityManager->getRepository(Patients::class)->count([]);

                $data['numeroDossier'] = 'PAT-' .  date('Y') . '-' . str_pad(
                    $count+1, 6, '0', STR_PAD_LEFT
                );
            }

            // Créer le patient
            $patient = new Patients();
            $patient->setNumeroDossier($data['numeroDossier']);
            $patient->setNom($data['nom']);
            $patient->setPrenom($data['prenom']);
            $patient->setDateNaissance(new \DateTime($data['dateNaissance']));
            $patient->setHopitalId($hopital);

            // Définir les champs optionnels
            if (isset($data['sexe'])) $patient->setSexe($data['sexe']);
            if (isset($data['numeroIdentite'])) $patient->setNumeroIdentite($data['numeroIdentite']);
            if (isset($data['typeIdentite'])) $patient->setTypeIdentite($data['typeIdentite']);
            if (isset($data['adresse'])) $patient->setAdresse($data['adresse']);
            if (isset($data['ville'])) $patient->setVille($data['ville']);
            if (isset($data['codePostal'])) $patient->setCodePostal($data['codePostal']);
            if (isset($data['telephone'])) $patient->setTelephone($data['telephone']);
            if (isset($data['email'])) $patient->setEmail($data['email']);
            if (isset($data['contactUrgenceNom'])) $patient->setContactUrgenceNom($data['contactUrgenceNom']);
            if (isset($data['contactUrgenceTelephone'])) $patient->setContactUrgenceTelephone($data['contactUrgenceTelephone']);
            if (isset($data['contactUrgenceLien'])) $patient->setContactUrgenceLien($data['contactUrgenceLien']);
            if (isset($data['groupeSanguin'])) $patient->setGroupeSanguin($data['groupeSanguin']);
            if (isset($data['allergies'])) $patient->setAllergies($data['allergies']);
            if (isset($data['antecedentsMedicaux'])) $patient->setAntecedentsMedicaux($data['antecedentsMedicaux']);
            if (isset($data['antecedentsChirurgicaux'])) $patient->setAntecedentsChirurgicaux($data['antecedentsChirurgicaux']);
            if (isset($data['medicamentsActuels'])) $patient->setMedicamentsActuels($data['medicamentsActuels']);
            if (isset($data['statutCivil'])) $patient->setStatutCivil($data['statutCivil']);
            if (isset($data['profession'])) $patient->setProfession($data['profession']);
            if (isset($data['nationalite'])) $patient->setNationalite($data['nationalite']);
            if (isset($data['languePreference'])) $patient->setLanguePreference($data['languePreference']);

            // Valider l'entité
            $errors = $this->validator->validate($patient);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            // Persister et flush
            $this->entityManager->persist($patient);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Patient créé avec succès',
                'data' => $this->formatPatientData($patient),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création du patient: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un patient existant
     * PUT /api/patients/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($id);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['nom'])) $patient->setNom($data['nom']);
            if (isset($data['prenom'])) $patient->setPrenom($data['prenom']);
            if (isset($data['dateNaissance'])) $patient->setDateNaissance(new \DateTime($data['dateNaissance']));
            if (isset($data['sexe'])) $patient->setSexe($data['sexe']);
            if (isset($data['numeroIdentite'])) $patient->setNumeroIdentite($data['numeroIdentite']);
            if (isset($data['typeIdentite'])) $patient->setTypeIdentite($data['typeIdentite']);
            if (isset($data['adresse'])) $patient->setAdresse($data['adresse']);
            if (isset($data['ville'])) $patient->setVille($data['ville']);
            if (isset($data['codePostal'])) $patient->setCodePostal($data['codePostal']);
            if (isset($data['telephone'])) $patient->setTelephone($data['telephone']);
            if (isset($data['email'])) $patient->setEmail($data['email']);
            if (isset($data['contactUrgenceNom'])) $patient->setContactUrgenceNom($data['contactUrgenceNom']);
            if (isset($data['contactUrgenceTelephone'])) $patient->setContactUrgenceTelephone($data['contactUrgenceTelephone']);
            if (isset($data['contactUrgenceLien'])) $patient->setContactUrgenceLien($data['contactUrgenceLien']);
            if (isset($data['groupeSanguin'])) $patient->setGroupeSanguin($data['groupeSanguin']);
            if (isset($data['allergies'])) $patient->setAllergies($data['allergies']);
            if (isset($data['antecedentsMedicaux'])) $patient->setAntecedentsMedicaux($data['antecedentsMedicaux']);
            if (isset($data['antecedentsChirurgicaux'])) $patient->setAntecedentsChirurgicaux($data['antecedentsChirurgicaux']);
            if (isset($data['medicamentsActuels'])) $patient->setMedicamentsActuels($data['medicamentsActuels']);
            if (isset($data['statutCivil'])) $patient->setStatutCivil($data['statutCivil']);
            if (isset($data['profession'])) $patient->setProfession($data['profession']);
            if (isset($data['nationalite'])) $patient->setNationalite($data['nationalite']);
            if (isset($data['languePreference'])) $patient->setLanguePreference($data['languePreference']);
            if (isset($data['actif'])) $patient->setActif($data['actif']);

            // Mettre à jour la date de modification
            $patient->setDateModification(new DateTimeImmutable());

            // Valider l'entité
            $errors = $this->validator->validate($patient);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Patient mis à jour avec succès',
                'data' => $this->formatPatientData($patient),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du patient: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un patient (soft delete - marque comme inactif)
     * DELETE /api/patients/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($id);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            // Soft delete - marquer comme inactif
            $patient->setActif(false);
            $patient->setDateModification(new DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Patient supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression du patient: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Génère et télécharge une fiche patient en PDF
     * GET /api/patients/{id}/export/pdf
     * 
     * Response: Fichier PDF
     */
    #[Route('/{id}/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(int $id): Response|JsonResponse
    {
        // NOTE: l'URL correcte est /api/patients/{id}/export/pdf
        // (par ex: /api/patients/1/export/pdf)
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($id);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            $pdf = $this->generateModernPatientPdf($patient);
            $pdfContent = $pdf->Output('', 'S');

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="fiche_patient_' . $patient->getNumeroDossier() . '_' . date('Y-m-d') . '.pdf"'
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la génération du PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Génère et télécharge un dossier médical complet en PDF
     * GET /api/patients/{id}/export/dossier-medical
     * 
     * Response: Fichier PDF
     */
    #[Route('/{id}/export/dossier-medical', name: 'export_dossier_medical', methods: ['GET'])]
    public function exportDossierMedical(int $id): \Symfony\Component\HttpFoundation\Response|JsonResponse
    {
        // NOTE: l'URL correcte est /api/patients/{id}/export/dossier-medical
        // (par ex: /api/patients/1/export/dossier-medical)
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($id);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            // Récupérer les admissions
            $admissions = $this->entityManager->getRepository(Admissions::class)
                ->findBy(['patientId' => $patient], ['dateAdmission' => 'DESC']);

            // Créer le PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator('Rehoboth Hospital');
            $pdf->SetAuthor('Rehoboth Hospital');
            $pdf->SetTitle('Dossier Médical - ' . $patient->getNom() . ' ' . $patient->getPrenom());
            $pdf->SetSubject('Dossier Médical');
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            // Page 1 - Informations patient
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'DOSSIER MÉDICAL COMPLET', 0, 1, 'C');

            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 5, 'Hôpital: ' . $patient->getHopitalId()->getNom(), 0, 1);
            $pdf->Cell(0, 5, 'Date d\'édition: ' . (new \DateTime())->format('d/m/Y H:i'), 0, 1);
            $pdf->Ln(5);

            // Informations patient
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'PATIENT', 0, 1);
            $pdf->SetFont('helvetica', '', 10);

            $this->addPdfRow($pdf, 'Numéro de dossier:', $patient->getNumeroDossier());
            $this->addPdfRow($pdf, 'Nom complet:', $patient->getNom() . ' ' . $patient->getPrenom());
            $this->addPdfRow($pdf, 'Date de naissance:', $patient->getDateNaissance()->format('d/m/Y'));
            $this->addPdfRow($pdf, 'Âge:', $this->calculateAge($patient->getDateNaissance()) . ' ans');
            $this->addPdfRow($pdf, 'Groupe sanguin:', $patient->getGroupeSanguin() ?? 'N/A');

            $pdf->Ln(5);

            // Historique des admissions
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'HISTORIQUE DES ADMISSIONS', 0, 1);
            $pdf->SetFont('helvetica', '', 9);

            if (!empty($admissions)) {
                foreach ($admissions as $admission) {
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Admission #' . $admission->getNumeroAdmission(), 0, 1);

                    $pdf->SetFont('helvetica', '', 9);
                    $this->addPdfRow($pdf, 'Date d\'admission:', $admission->getDateAdmission()->format('d/m/Y H:i'));
                    if ($admission->getDateSortie()) {
                        $this->addPdfRow($pdf, 'Date de sortie:', $admission->getDateSortie()->format('d/m/Y H:i'));
                    }
                    $this->addPdfRow($pdf, 'Statut:', $admission->getStatut() ?? 'N/A');
                    $this->addPdfRow($pdf, 'Type d\'admission:', $admission->getTypeAdmission() ?? 'N/A');
                    $this->addPdfRow($pdf, 'Motif:', $admission->getMotifAdmission() ?? 'N/A');
                    $this->addPdfRow($pdf, 'Diagnostic principal:', $admission->getDiagnosticPrincipal() ?? 'N/A');

                    $pdf->Ln(3);
                }
            } else {
                $pdf->MultiCell(0, 5, 'Aucune admission trouvée.');
            }

            $pdfContent = $pdf->Output('', 'S');

            $response = new \Symfony\Component\HttpFoundation\Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="dossier_medical_' . $patient->getNumeroDossier() . '.pdf"'
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la génération du dossier médical: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporte les patients en CSV
     * GET /api/patients/export/csv
     */
    #[Route('/export/csv', name: 'export_csv', methods: ['GET'])]
    public function exportCsv(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $hopitalId = $request->query->get('hopital_id');
            $actif = $request->query->get('actif');

            // Construire la requête
            $queryBuilder = $this->entityManager->getRepository(Patients::class)
                ->createQueryBuilder('p');

            if ($hopitalId) {
                $queryBuilder->andWhere('p.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('p.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            $patients = $queryBuilder->getQuery()->getResult();

            // Créer le fichier CSV
            $filename = 'patients_export_' . time() . '.csv';
            $filepath = sys_get_temp_dir() . '/' . $filename;
            $file = fopen($filepath, 'w');

            // En-têtes
            fputcsv($file, [
                'ID',
                'Numéro de dossier',
                'Nom',
                'Prénom',
                'Date de naissance',
                'Sexe',
                'Email',
                'Téléphone',
                'Groupe sanguin',
                'Ville',
                'Hôpital',
                'Actif',
                'Date de création',
            ], ';');

            // Données
            foreach ($patients as $patient) {
                fputcsv($file, [
                    $patient->getId(),
                    $patient->getNumeroDossier(),
                    $patient->getNom(),
                    $patient->getPrenom(),
                    $patient->getDateNaissance()->format('d/m/Y'),
                    $patient->getSexe() === 'M' ? 'Masculin' : 'Féminin',
                    $patient->getEmail() ?? '',
                    $patient->getTelephone() ?? '',
                    $patient->getGroupeSanguin() ?? '',
                    $patient->getVille() ?? '',
                    $patient->getHopitalId()->getNom(),
                    $patient->getActif() ? 'Oui' : 'Non',
                    $patient->getDateCreation()?->format('d/m/Y H:i') ?? '',
                ], ';');
            }

            fclose($file);

            // Retourner le fichier
            $response = new BinaryFileResponse($filepath);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export CSV: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des patients
     * GET /api/patients/stats
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        try {
            $repository = $this->entityManager->getRepository(Patients::class);

            // Total
            $total = count($repository->findAll());

            // Actifs/Inactifs
            $actifs = count($repository->findBy(['actif' => true]));
            $inactifs = $total - $actifs;

            // Par sexe
            $parSexe = [];
            foreach (['M', 'F'] as $sexe) {
                $parSexe[$sexe] = count($repository->findBy(['sexe' => $sexe]));
            }

            // Par groupe sanguin
            $queryBuilder = $this->entityManager->getRepository(Patients::class)
                ->createQueryBuilder('p')
                ->select('p.groupeSanguin, COUNT(p.id) as count')
                ->groupBy('p.groupeSanguin')
                ->getQuery()
                ->getResult();

            $parGroupeSanguin = [];
            foreach ($queryBuilder as $row) {
                if ($row['groupeSanguin']) {
                    $parGroupeSanguin[$row['groupeSanguin']] = (int)$row['count'];
                }
            }

            // Âge moyen
            $patients = $repository->findAll();
            $agesMoyens = 0;
            if (!empty($patients)) {
                $totalAge = 0;
                foreach ($patients as $patient) {
                    $totalAge += $this->calculateAge($patient->getDateNaissance());
                }
                $agesMoyens = round($totalAge / count($patients), 1);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'actifs' => $actifs,
                    'inactifs' => $inactifs,
                    'parSexe' => $parSexe,
                    'parGroupeSanguin' => $parGroupeSanguin,
                    'agesMoyens' => $agesMoyens,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recherche avancée de patients
     * POST /api/patients/search
     */
    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $page = max(1, (int)($data['page'] ?? 1));
            $limit = min(100, max(1, (int)($data['limit'] ?? 20)));

            // Construire la requête
            $queryBuilder = $this->entityManager->getRepository(Patients::class)
                ->createQueryBuilder('p')
                ->leftJoin('p.hopitalId', 'h')
                ->addSelect('h');

            // Appliquer les filtres
            if (isset($data['nom']) && !empty($data['nom'])) {
                $queryBuilder->andWhere('p.nom LIKE :nom')
                    ->setParameter('nom', '%' . $data['nom'] . '%');
            }

            if (isset($data['prenom']) && !empty($data['prenom'])) {
                $queryBuilder->andWhere('p.prenom LIKE :prenom')
                    ->setParameter('prenom', '%' . $data['prenom'] . '%');
            }

            if (isset($data['dateNaissanceMin'])) {
                $queryBuilder->andWhere('p.dateNaissance >= :dateMin')
                    ->setParameter('dateMin', new \DateTime($data['dateNaissanceMin']));
            }

            if (isset($data['dateNaissanceMax'])) {
                $queryBuilder->andWhere('p.dateNaissance <= :dateMax')
                    ->setParameter('dateMax', new \DateTime($data['dateNaissanceMax']));
            }

            if (isset($data['sexe']) && !empty($data['sexe'])) {
                $queryBuilder->andWhere('p.sexe = :sexe')
                    ->setParameter('sexe', $data['sexe']);
            }

            if (isset($data['groupeSanguin']) && !empty($data['groupeSanguin'])) {
                $queryBuilder->andWhere('p.groupeSanguin = :groupeSanguin')
                    ->setParameter('groupeSanguin', $data['groupeSanguin']);
            }

            if (isset($data['ville']) && !empty($data['ville'])) {
                $queryBuilder->andWhere('p.ville LIKE :ville')
                    ->setParameter('ville', '%' . $data['ville'] . '%');
            }

            if (isset($data['hopital_id'])) {
                $queryBuilder->andWhere('p.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $data['hopital_id']);
            }

            if (isset($data['actif'])) {
                $queryBuilder->andWhere('p.actif = :actif')
                    ->setParameter('actif', filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('p.dateCreation', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $patients = $queryBuilder->getQuery()->getResult();

            // Formater les données
            $data = array_map(function (Patients $patient) {
                return $this->formatPatientData($patient);
            }, $patients);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les patients d'un hôpital spécifique
     * GET /api/patients/hopital/{hopitalId}
     */
    #[Route('/hopital/{hopitalId}', name: 'by_hopital', methods: ['GET'])]
    public function byHopital(int $hopitalId, Request $request): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($hopitalId);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(Patients::class)
                ->createQueryBuilder('p')
                ->where('p.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId)
                ->orderBy('p.dateCreation', 'DESC');

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $patients = $queryBuilder->getQuery()->getResult();

            // Formater les données
            $data = array_map(function (Patients $patient) {
                return $this->formatPatientData($patient);
            }, $patients);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'hopital' => $hopital->getNom(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des patients: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate les données d'un patient pour la réponse JSON
     * 
     * @param Patients $patient Patient à formater
     * @param bool $detailed Si true, inclut tous les détails
     * @return array Données formatées
     */
    private function formatPatientData(Patients $patient, bool $detailed = false): array
    {
        $data = [
            'id' => $patient->getId(),
            'numeroDossier' => $patient->getNumeroDossier(),
            'nom' => $patient->getNom(),
            'prenom' => $patient->getPrenom(),
            'dateNaissance' => $patient->getDateNaissance()->format('Y-m-d'),
            'sexe' => $patient->getSexe(),
            'email' => $patient->getEmail(),
            'telephone' => $patient->getTelephone(),
            'groupeSanguin' => $patient->getGroupeSanguin(),
            'actif' => $patient->getActif(),
            'hopital' => $patient->getHopitalId()->getNom(),
            'dateCreation' => $patient->getDateCreation()?->format('c'),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'numeroIdentite' => $patient->getNumeroIdentite(),
                'typeIdentite' => $patient->getTypeIdentite(),
                'adresse' => $patient->getAdresse(),
                'ville' => $patient->getVille(),
                'codePostal' => $patient->getCodePostal(),
                'contactUrgenceNom' => $patient->getContactUrgenceNom(),
                'contactUrgenceTelephone' => $patient->getContactUrgenceTelephone(),
                'contactUrgenceLien' => $patient->getContactUrgenceLien(),
                'allergies' => $patient->getAllergies(),
                'antecedentsMedicaux' => $patient->getAntecedentsMedicaux(),
                'antecedentsChirurgicaux' => $patient->getAntecedentsChirurgicaux(),
                'medicamentsActuels' => $patient->getMedicamentsActuels(),
                'statutCivil' => $patient->getStatutCivil(),
                'profession' => $patient->getProfession(),
                'nationalite' => $patient->getNationalite(),
                'languePreference' => $patient->getLanguePreference(),
                'photoPatient' => $patient->getPhotoPatient(),
                'dateModification' => $patient->getDateModification()?->format('c'),
            ]);
        }

        return $data;
    }

    /**
     * Ajoute une ligne au PDF avec label et valeur
     * 
     * @param TCPDF $pdf Instance TCPDF
     * @param string $label Label de la ligne
     * @param string $value Valeur de la ligne
     */
    private function addPdfRow(TCPDF $pdf, string $label, string $value): void
    {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 5, $label, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $value, 0, 1);
    }

    /**
     * Calcule l'âge à partir de la date de naissance
     * 
     * @param \DateTimeInterface $dateNaissance Date de naissance
     * @return int Âge en années
     */
    private function calculateAge(\DateTimeInterface $dateNaissance): int
    {
        $today = new \DateTime();
        $age = $today->diff($dateNaissance)->y;
        return $age;
    }

    /**
     * Génère un PDF moderne et professionnel pour la fiche patient
     * Design hospitalier avec logo, en-têtes, pieds de page, couleurs personnalisées
     * 
     * @param Patients $patient Patient à documenter
     * @return TCPDF Instance PDF configurée
     */
    private function generateModernPatientPdf(Patients $patient): TCPDF
    {
        // Récupérer les données de l'hôpital
        $hopital = $patient->getHopitalId();
        $logoPath = null;
        $couleurPrimaire = [0, 102, 204]; // Bleu par défaut
        
        // Convertir le chemin du logo et vérifier s'il existe
        if ($hopital->getLogoUrl()) {
            // Normaliser le chemin (remplacer les backslashes par des slashes)
            $logoRelative = str_replace('\\', '/', $hopital->getLogoUrl());
            // Obtenir le chemin absolu complet
            $logoPath = realpath($this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $logoRelative);
            
            // Si le chemin n'existe pas ou est invalide, utiliser le logo par défaut
            if (!$logoPath || !file_exists($logoPath)) {
                $defaultLogo = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'image_rehoboth.png';
                $logoPath = realpath($defaultLogo);
            }
        }
        
        // Extraire la couleur primaire si disponible (format #RRGGBB)
        if ($hopital->getCouleurPrimaire()) {
            $couleur = $hopital->getCouleurPrimaire();
            if (preg_match('/^#?([0-9A-Fa-f]{6})$/', $couleur, $matches)) {
                $hex = $matches[1];
                $couleurPrimaire = [
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2))
                ];
            }
        }
        
        // Créer une classe TCPDF personnalisée pour les en-têtes/pieds de page
        $pdf = new class extends TCPDF {
            public $hospitalName = '';
            public $patientNumber = '';
            public $logoPath = null;
            public $couleurPrimaire = [0, 102, 204];
            
            public function Header(): void
            {
                // Couleur de fond (couleur primaire de l'hôpital)
                $this->SetFillColor($this->couleurPrimaire[0], $this->couleurPrimaire[1], $this->couleurPrimaire[2]);
                $this->SetTextColor(255, 255, 255); // Blanc
                
                // En-tête avec bande colorée - plus compact
                $this->Rect(0, 0, 210, 22, 'F');
                
                // Logo si disponible
                $logoDisplayed = false;
                if ($this->logoPath && is_file($this->logoPath) && file_exists($this->logoPath)) {
                    try {
                        $this->Image($this->logoPath, 10, 2, 18, 18, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                        $logoDisplayed = true;
                    } catch (\Exception $e) {
                        // Si le logo ne peut pas être chargé, continuer sans
                        error_log('Erreur lors du chargement du logo PDF: ' . $e->getMessage());
                    }
                }
                
                // Nom hôpital
                $logoOffset = $logoDisplayed ? 32 : 10;
                $this->SetFont('helvetica', 'B', 16);
                $this->SetXY($logoOffset, 6);
                $this->Cell(100, 8, $this->hospitalName, 0, 0, 'L');
                
                // Numéro de dossier à droite
                $this->SetFont('helvetica', 'B', 9);
                $this->SetTextColor(220, 220, 220);
                $this->SetXY(130, 5);
                $this->Cell(70, 5, 'Dossier Patient', 0, 1, 'R');
                $this->SetXY(130, 11);
                $this->Cell(70, 6, $this->patientNumber, 0, 0, 'R');
                
                // Ligne de séparation
                $this->SetDrawColor($this->couleurPrimaire[0], $this->couleurPrimaire[1], $this->couleurPrimaire[2]);
                $this->SetLineWidth(0.8);
                $this->Line(0, 22, 210, 22);
                
                // Réinitialiser les couleurs
                $this->SetTextColor(0, 0, 0);
            }
            
            public function Footer(): void
            {
                // Position à 15mm du bas
                $this->SetY(-15);
                
                // Ligne de séparation
                $this->SetDrawColor(200, 200, 200);
                $this->SetLineWidth(0.3);
                $this->Line(15, $this->GetY(), 195, $this->GetY());
                
                // Pied de page
                $this->SetFont('helvetica', '', 8);
                $this->SetTextColor(100, 100, 100);
                
                // Texte gauche
                $this->SetXY(15, -12);
                $this->Cell(0, 5, 'Document confidentiel - Réservé au personnel médical', 0, 0, 'L');
                
                // Numéro de page à droite
                $this->SetXY(15, -12);
                $this->Cell(0, 5, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
            }
        };
        
        // Configuration du PDF
        $pdf->SetCreator('Rehoboth Hospital');
        $pdf->SetAuthor('Rehoboth Hospital');
        $pdf->SetTitle('Fiche Patient - ' . $patient->getNom() . ' ' . $patient->getPrenom());
        $pdf->SetSubject('Fiche Patient');
        $pdf->SetDefaultMonospacedFont('courier');
        
        // Marges
        $pdf->SetMargins(12, 26, 12);
        $pdf->SetAutoPageBreak(true, 18);
        
        // Passer les données à la classe
        $pdf->hospitalName = $hopital->getNom();
        $pdf->patientNumber = $patient->getNumeroDossier();
        $pdf->logoPath = $logoPath;
        $pdf->couleurPrimaire = $couleurPrimaire;
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Section de résumé - Information clé du patient
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(0, 4, 'NOM: ' . strtoupper($patient->getNom() . ' ' . $patient->getPrenom()) . '  •  DOB: ' . $patient->getDateNaissance()->format('d/m/Y') . '  •  SEXE: ' . ($patient->getSexe() === 'M' ? 'M' : 'F'), 0, 'L');
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(12, $pdf->GetY() + 1, 198, $pdf->GetY() + 1);
        $pdf->Ln(3);
        
        // ===== SECTION 1: INFORMATIONS PERSONNELLES =====
        $this->addModernSection($pdf, 'IDENTITE PERSONNELLE', $couleurPrimaire, [
            'Numero de dossier' => $patient->getNumeroDossier(),
            'Age' => $this->calculateAge($patient->getDateNaissance()) . ' ans',
            'Nationalite' => $patient->getNationalite() ?? 'N/A',
            'Profession' => $patient->getProfession() ?? 'N/A',
            'Statut civil' => $patient->getStatutCivil() ?? 'N/A',
        ], true);
        
        // ===== SECTION 2: COORDONNÉES =====
        $this->addModernSection($pdf, 'COORDONNEES DE CONTACT', $couleurPrimaire, [
            'Adresse' => $patient->getAdresse() ?? 'N/A',
            'Ville/Code postal' => ($patient->getVille() ?? 'N/A') . ' ' . ($patient->getCodePostal() ?? ''),
            'Telephone' => $patient->getTelephone() ?? 'N/A',
            'Email' => $patient->getEmail() ?? 'N/A',
        ], true);
        
        // ===== SECTION 3: INFORMATIONS MÉDICALES =====
        $this->addModernSection($pdf, 'DONNEES MEDICALES ESSENTIELLES', $couleurPrimaire, [
            'Groupe sanguin' => $patient->getGroupeSanguin() ?? 'N/A',
            'Allergies' => $patient->getAllergies() ?? 'Aucune',
        ], true);
        
        // Antécédents médicaux et chirurgicaux
        $antecedentData = [];
        if ($patient->getAntecedentsMedicaux()) {
            $antecedentData['Antecedents medicaux'] = $patient->getAntecedentsMedicaux();
        }
        if ($patient->getAntecedentsChirurgicaux()) {
            $antecedentData['Antecedents chirurgicaux'] = $patient->getAntecedentsChirurgicaux();
        }
        if ($patient->getMedicamentsActuels()) {
            $antecedentData['Medicaments actuels'] = $patient->getMedicamentsActuels();
        }
        
        if (!empty($antecedentData)) {
            $this->addModernSection($pdf, 'HISTORIQUE MEDICAL', $couleurPrimaire, $antecedentData, false);
        }
        
        // ===== SECTION 4: CONTACT D'URGENCE =====
        $this->addModernSection($pdf, 'CONTACT D URGENCE', $couleurPrimaire, [
            'Nom' => $patient->getContactUrgenceNom() ?? 'N/A',
            'Lien de parenté' => $patient->getContactUrgenceLien() ?? 'N/A',
            'Telephone' => $patient->getContactUrgenceTelephone() ?? 'N/A',
        ], true);
        
        // ===== SECTION 5: INFORMATIONS ADMINISTRATIVES =====
        $this->addModernSection($pdf, 'INFORMATIONS ADMINISTRATIVES', $couleurPrimaire, [
            'Hopital' => $hopital->getNom(),
            'Statut' => $patient->getActif() ? 'ACTIF' : 'INACTIF',
            'Date de creation' => $patient->getDateCreation()?->format('d/m/Y') ?? 'N/A',
            'Derniere modification' => $patient->getDateModification()?->format('d/m/Y') ?? 'N/A',
        ], true);
        
        // Pied de document
        $pdf->Ln(5);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 4, 'Document genere le ' . (new \DateTime())->format('d/m/Y a H:i:s') . ' | Version numerique authentique', 0, 1, 'C');
        
        return $pdf;
    }
    
    /**
     * Ajoute une section moderne au PDF avec titre et données
     * 
     * @param TCPDF $pdf Instance TCPDF
     * @param string $title Titre de la section
     * @param array $couleurPrimaire Couleur primaire RGB [R, G, B]
     * @param array $data Données clé => valeur
     * @param bool $twoColumn Afficher en deux colonnes si possible
     */
    private function addModernSection(TCPDF $pdf, string $title, array $couleurPrimaire, array $data, bool $twoColumn = true): void
    {
        // Titre de section avec fond basé sur la couleur primaire
        $bgColor = [
            min(255, $couleurPrimaire[0] + 220),
            min(255, $couleurPrimaire[1] + 220),
            min(255, $couleurPrimaire[2] + 220)
        ];
        $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
        $pdf->SetDrawColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
        $pdf->SetLineWidth(0.8);
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
        $pdf->Cell(0, 6, ' ' . $title, 0, 1, 'L', true);
        
        // Contenu de la section
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        $itemCount = count($data);
        $dataArray = array_values($data);
        $labelArray = array_keys($data);
        
        if ($twoColumn && $itemCount > 2) {
            // Mode deux colonnes
            $startX = $pdf->GetX();
            $startY = $pdf->GetY();
            $rowHeight = 6;
            $leftWidth = 93;
            
            for ($i = 0; $i < $itemCount; $i++) {
                $row = floor($i / 2);
                $isSecondColumn = ($i % 2 == 1);
                
                // Positionnement
                if ($isSecondColumn) {
                    $pdf->SetXY($startX + $leftWidth, $startY + ($row * $rowHeight));
                } else {
                    if ($i > 0) {
                        $pdf->SetXY($startX, $startY + ($row * $rowHeight));
                    }
                }
                
                // Fond alternant
                $bgFill = ($row % 2 == 0) ? [247, 249, 251] : [255, 255, 255];
                $pdf->SetFillColor($bgFill[0], $bgFill[1], $bgFill[2]);
                $pdf->SetDrawColor(220, 220, 220);
                
                // Label en couleur primaire
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
                $pdf->Cell(34, $rowHeight, $labelArray[$i] . ':', 0, 0, 'L', true, '', 1);
                
                // Valeur
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(40, 40, 40);
                $valeurTronquee = substr($dataArray[$i], 0, 45);
                $pdf->Cell(59, $rowHeight, $valeurTronquee, 0, 1, 'L', true, '', 1);
            }
        } else {
            // Mode une colonne (pour textes longs)
            $rowIndex = 0;
            foreach ($data as $label => $value) {
                // Fond alternant
                $bgFill = ($rowIndex % 2 == 0) ? [247, 249, 251] : [255, 255, 255];
                $pdf->SetFillColor($bgFill[0], $bgFill[1], $bgFill[2]);
                $pdf->SetDrawColor(220, 220, 220);
                
                // Label en couleur primaire
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
                $pdf->Cell(40, 5, $label . ':', 0, 0, 'L', true, '', 1);
                
                // Valeur avec MultiCell
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(40, 40, 40);
                $pdf->SetX(52);
                $pdf->MultiCell(146, 4, $value, 1, 'L', true);
                
                $rowIndex++;
            }
        }
        
        $pdf->Ln(2);
    }
}

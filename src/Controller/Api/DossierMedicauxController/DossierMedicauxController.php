<?php

namespace App\Controller\Api\DossierMedicauxController;

use App\Entity\Patients\Patients;
use App\Entity\Patients\Admissions;
use App\Entity\Patients\DossiersMedicaux;
use App\Entity\Patients\AssurancesPatients;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use App\Entity\Consultations\Consultations;
use App\Entity\Consultations\Prescriptions;
use App\Entity\Consultations\ResultatsLabo;
use App\Entity\Consultations\ExamensImagerie;
use App\Entity\Consultations\OrdonnancesLabo;
use App\Entity\Consultations\OrdonnancesImagerie;
use App\Entity\Consultations\DistributionsPharmacie;
use App\Entity\Consultations\NotesInfirmieres;
use App\Entity\ComptabiliteFacturation\Factures;
use App\Entity\ComptabiliteFacturation\Paiements;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TCPDF;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion des dossiers médicaux
 * 
 * Gère:
 * - CRUD complet des dossiers médicaux
 * - Récupération des informations critiques du patient (consultations, prescriptions, analyses, imagerie)
 * - Historique médical complet
 * - Génération de documents PDF du dossier médical
 * - Gestion des notes infirmières et observations
 * - Suivi des traitements et prescriptions
 * - Historique des factures et paiements
 * 
 * Tous les endpoints retournent du JSON sauf les téléchargements PDF
 */
#[Route('/api/dossiers-medicaux', name: 'api_dossiers_medicaux_')]
class DossierMedicauxController extends AbstractController
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
     * Récupère la liste de tous les dossiers médicaux avec pagination et filtrage
     * GET /api/dossiers-medicaux
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $hopitalId = $request->query->get('hopital_id');
            $statut = $request->query->get('statut');
            $sort = $request->query->get('sort', 'dateOuverture');
            $order = strtoupper($request->query->get('order', 'DESC'));

            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            $queryBuilder = $this->entityManager->getRepository(DossiersMedicaux::class)
                ->createQueryBuilder('dm')
                ->leftJoin('dm.patientId', 'p')
                ->leftJoin('dm.hopitalId', 'h')
                ->leftJoin('dm.medecinReferentId', 'mr')
                ->addSelect('p', 'h', 'mr');

            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('dm.numeroDme', ':search'),
                        $queryBuilder->expr()->like('p.nom', ':search'),
                        $queryBuilder->expr()->like('p.prenom', ':search'),
                        $queryBuilder->expr()->like('p.numeroDossier', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($hopitalId) {
                $queryBuilder->andWhere('dm.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($statut) {
                $queryBuilder->andWhere('dm.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            $queryBuilder->orderBy('dm.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $dossiers = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (DossiersMedicaux $dossier) {
                return $this->formatDossierData($dossier);
            }, $dossiers);

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
                'error' => 'Erreur lors de la récupération des dossiers médicaux: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails complets d'un dossier médical avec toutes les informations critiques
     * GET /api/dossiers-medicaux/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $patient = $dossier->getPatientId();

            // Récupérer toutes les données critiques du patient
            $admissions = $this->entityManager->getRepository(Admissions::class)
                ->findBy(['patientId' => $patient], ['dateAdmission' => 'DESC']);

            $consultations = $this->entityManager->getRepository(Consultations::class)
                ->findBy(['patientId' => $patient], ['dateConsultation' => 'DESC']);

            $prescriptions = $this->entityManager->getRepository(Prescriptions::class)
                ->findBy(['patientId' => $patient], ['datePrescription' => 'DESC']);

            $resultatsLabo = $this->entityManager->getRepository(ResultatsLabo::class)
                ->findBy(['patientId' => $patient], ['dateResultat' => 'DESC']);

            $examensImagerie = $this->entityManager->getRepository(ExamensImagerie::class)
                ->findBy(['patientId' => $patient], ['dateExamen' => 'DESC']);

            $notesInfirmieres = $this->entityManager->getRepository(NotesInfirmieres::class)
                ->findBy(['patientId' => $patient], ['dateNote' => 'DESC']);

            $factures = $this->entityManager->getRepository(Factures::class)
                ->findBy(['patientId' => $patient], ['dateFacture' => 'DESC']);

            $paiements = $this->entityManager->getRepository(Paiements::class)
                ->findBy(['patientId' => $patient], ['datePaiement' => 'DESC']);

            $assurances = $this->entityManager->getRepository(AssurancesPatients::class)
                ->findBy(['patientId' => $patient]);

            $medecinReferent = $dossier->getMedecinReferentId();

            $data = [
                'dossier' => [
                    'id' => $dossier->getId(),
                    'numeroDme' => $dossier->getNumeroDme(),
                    'dateOuverture' => $dossier->getDateOuverture()->format('c'),
                    'dateFermeture' => $dossier->getDateFermeture()?->format('c'),
                    'statut' => $dossier->getStatut(),
                    'notesGenerales' => $dossier->getNotesGenerales(),
                    'dateCreation' => $dossier->getDateCreation()?->format('c'),
                    'dateModification' => $dossier->getDateModification()?->format('c'),
                ],
                'patient' => [
                    'id' => $patient->getId(),
                    'numeroDossier' => $patient->getNumeroDossier(),
                    'nom' => $patient->getNom(),
                    'prenom' => $patient->getPrenom(),
                    'dateNaissance' => $patient->getDateNaissance()->format('Y-m-d'),
                    'age' => $this->calculateAge($patient->getDateNaissance()),
                    'sexe' => $patient->getSexe(),
                    'groupeSanguin' => $patient->getGroupeSanguin(),
                    'allergies' => $patient->getAllergies(),
                    'antecedentsMedicaux' => $patient->getAntecedentsMedicaux(),
                    'antecedentsChirurgicaux' => $patient->getAntecedentsChirurgicaux(),
                    'medicamentsActuels' => $patient->getMedicamentsActuels(),
                ],
                'medecin_referent' => [
                    'id' => $medecinReferent->getId(),
                    'nom' => $medecinReferent->getNom(),
                    'prenom' => $medecinReferent->getPrenom(),
                    'specialite' => $medecinReferent->getSpecialiteId()?->getLibelle() ?? 'N/A',
                    'email' => $medecinReferent->getEmail(),
                    'telephone' => $medecinReferent->getTelephone(),
                ],
                'hopital' => [
                    'id' => $dossier->getHopitalId()->getId(),
                    'nom' => $dossier->getHopitalId()->getNom(),
                ],
                'informations_medicales' => [
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
                            'diagnosticsSecondaires' => $admission->getDiagnosticsSecondaires(),
                        ];
                    }, $admissions),
                    'consultations' => array_map(function (Consultations $consultation) {
                        return [
                            'id' => $consultation->getId(),
                            'dateConsultation' => $consultation->getDateConsultation()->format('c'),
                            'motif' => $consultation->getMotif(),
                            'diagnostic' => $consultation->getDiagnostic(),
                            'observations' => $consultation->getObservations(),
                            'medecin' => $consultation->getMedecinId()?->getNom() . ' ' . $consultation->getMedecinId()?->getPrenom(),
                        ];
                    }, $consultations),
                    'prescriptions' => array_map(function (Prescriptions $prescription) {
                        return [
                            'id' => $prescription->getId(),
                            'datePrescription' => $prescription->getDatePrescription()->format('c'),
                            'dateDebut' => $prescription->getDateDebut()?->format('c'),
                            'dateFin' => $prescription->getDateFin()?->format('c'),
                            'medicament' => $prescription->getMedicamentId()?->getNom() ?? 'N/A',
                            'dosage' => $prescription->getDosage(),
                            'frequence' => $prescription->getFrequence(),
                            'duree' => $prescription->getDuree(),
                            'statut' => $prescription->getStatut(),
                            'notes' => $prescription->getNotes(),
                        ];
                    }, $prescriptions),
                    'resultats_labo' => array_map(function (ResultatsLabo $resultat) {
                        return [
                            'id' => $resultat->getId(),
                            'dateResultat' => $resultat->getDateResultat()->format('c'),
                            'typeExamen' => $resultat->getTypeExamen(),
                            'valeur' => $resultat->getValeur(),
                            'unite' => $resultat->getUnite(),
                            'valeurNormale' => $resultat->getValeurNormale(),
                            'interpretation' => $resultat->getInterpretation(),
                            'statut' => $resultat->getStatut(),
                        ];
                    }, $resultatsLabo),
                    'examens_imagerie' => array_map(function (ExamensImagerie $examen) {
                        return [
                            'id' => $examen->getId(),
                            'dateExamen' => $examen->getDateExamen()->format('c'),
                            'typeImagerie' => $examen->getTypeImagerie(),
                            'zone' => $examen->getZone(),
                            'description' => $examen->getDescription(),
                            'conclusions' => $examen->getConclusions(),
                            'statut' => $examen->getStatut(),
                        ];
                    }, $examensImagerie),
                    'notes_infirmieres' => array_map(function (NotesInfirmieres $note) {
                        return [
                            'id' => $note->getId(),
                            'dateNote' => $note->getDateNote()->format('c'),
                            'contenu' => $note->getContenu(),
                            'infirmier' => $note->getInfirmierIdId()?->getNom() . ' ' . $note->getInfirmierIdId()?->getPrenom(),
                        ];
                    }, $notesInfirmieres),
                ],
                'informations_administratives' => [
                    'assurances' => array_map(function (AssurancesPatients $assurance) {
                        return [
                            'id' => $assurance->getId(),
                            'numeroPolice' => $assurance->getNumeroPolice(),
                            'nomAssurance' => $assurance->getNomAssurance(),
                            'dateDebut' => $assurance->getDateDebut()?->format('c'),
                            'dateFin' => $assurance->getDateFin()?->format('c'),
                            'statut' => $assurance->getStatut(),
                        ];
                    }, $assurances),
                    'factures' => array_map(function (Factures $facture) {
                        return [
                            'id' => $facture->getId(),
                            'numeroFacture' => $facture->getNumeroFacture(),
                            'dateFacture' => $facture->getDateFacture()->format('c'),
                            'montantTotal' => $facture->getMontantTotal(),
                            'montantPaye' => $facture->getMontantPaye(),
                            'statut' => $facture->getStatut(),
                        ];
                    }, $factures),
                    'paiements' => array_map(function (Paiements $paiement) {
                        return [
                            'id' => $paiement->getId(),
                            'datePaiement' => $paiement->getDatePaiement()->format('c'),
                            'montant' => $paiement->getMontant(),
                            'modePaiement' => $paiement->getModePaiement(),
                            'statut' => $paiement->getStatut(),
                        ];
                    }, $paiements),
                ],
                'statistiques' => [
                    'nombre_consultations' => count($consultations),
                    'nombre_prescriptions' => count($prescriptions),
                    'nombre_resultats_labo' => count($resultatsLabo),
                    'nombre_examens_imagerie' => count($examensImagerie),
                    'nombre_admissions' => count($admissions),
                    'nombre_notes_infirmieres' => count($notesInfirmieres),
                    'montant_total_factures' => array_sum(array_map(fn($f) => $f->getMontantTotal(), $factures)),
                    'montant_total_paye' => array_sum(array_map(fn($p) => $p->getMontant(), $paiements)),
                ],
            ];

            return $this->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du dossier médical: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouveau dossier médical
     * POST /api/dossiers-medicaux
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $requiredFields = ['numeroDme', 'patient_id', 'hopital_id', 'medecin_referent_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '$field' est requis",
                    ], 400);
                }
            }

            // Vérifier l'unicité du numéro DME
            $existingDossier = $this->entityManager->getRepository(DossiersMedicaux::class)
                ->findOneBy(['numeroDme' => $data['numeroDme']]);

            if ($existingDossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Un dossier médical avec ce numéro existe déjà',
                ], 409);
            }

            // Récupérer les entités liées
            $patient = $this->entityManager->getRepository(Patients::class)->find($data['patient_id']);
            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($data['hopital_id']);
            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $medecinReferent = $this->entityManager->getRepository(Utilisateurs::class)->find($data['medecin_referent_id']);
            if (!$medecinReferent) {
                return $this->json([
                    'success' => false,
                    'error' => 'Médecin référent non trouvé',
                ], 404);
            }

            // Créer le dossier
            $dossier = new DossiersMedicaux();
            $dossier->setNumeroDme($data['numeroDme']);
            $dossier->setPatientId($patient);
            $dossier->setHopitalId($hopital);
            $dossier->setMedecinReferentId($medecinReferent);
            $dossier->setDateOuverture(new \DateTime($data['dateOuverture'] ?? 'now'));
            
            if (isset($data['statut'])) $dossier->setStatut($data['statut']);
            if (isset($data['notesGenerales'])) $dossier->setNotesGenerales($data['notesGenerales']);

            // Valider
            $errors = $this->validator->validate($dossier);
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

            $this->entityManager->persist($dossier);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Dossier médical créé avec succès',
                'data' => $this->formatDossierData($dossier),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création du dossier médical: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un dossier médical
     * PUT /api/dossiers-medicaux/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['statut'])) $dossier->setStatut($data['statut']);
            if (isset($data['notesGenerales'])) $dossier->setNotesGenerales($data['notesGenerales']);
            if (isset($data['dateFermeture'])) $dossier->setDateFermeture(new \DateTime($data['dateFermeture']));

            if (isset($data['medecin_referent_id'])) {
                $medecinReferent = $this->entityManager->getRepository(Utilisateurs::class)->find($data['medecin_referent_id']);
                if (!$medecinReferent) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Médecin référent non trouvé',
                    ], 404);
                }
                $dossier->setMedecinReferentId($medecinReferent);
            }

            $dossier->setDateModification(new DateTimeImmutable());

            $errors = $this->validator->validate($dossier);
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
                'message' => 'Dossier médical mis à jour avec succès',
                'data' => $this->formatDossierData($dossier),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du dossier médical: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un dossier médical (soft delete - marque comme fermé)
     * DELETE /api/dossiers-medicaux/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            // Soft delete - marquer comme fermé
            $dossier->setStatut('FERME');
            $dossier->setDateFermeture(new \DateTime());
            $dossier->setDateModification(new DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Dossier médical supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression du dossier médical: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère le dossier médical d'un patient spécifique
     * GET /api/dossiers-medicaux/patient/{patientId}
     */
    #[Route('/patient/{patientId}', name: 'by_patient', methods: ['GET'])]
    public function byPatient(int $patientId, Request $request): JsonResponse
    {
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($patientId);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(DossiersMedicaux::class)
                ->createQueryBuilder('dm')
                ->where('dm.patientId = :patientId')
                ->setParameter('patientId', $patientId)
                ->leftJoin('dm.medecinReferentId', 'mr')
                ->addSelect('mr')
                ->orderBy('dm.dateOuverture', 'DESC');

            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $dossiers = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (DossiersMedicaux $dossier) {
                return $this->formatDossierData($dossier);
            }, $dossiers);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'patient' => [
                    'id' => $patient->getId(),
                    'nom' => $patient->getNom(),
                    'prenom' => $patient->getPrenom(),
                    'numeroDossier' => $patient->getNumeroDossier(),
                ],
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
                'error' => 'Erreur lors de la récupération des dossiers médicaux: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère le dossier médical actif d'un patient
     * GET /api/dossiers-medicaux/patient/{patientId}/actif
     */
    #[Route('/patient/{patientId}/actif', name: 'active_by_patient', methods: ['GET'])]
    public function activeByPatient(int $patientId): JsonResponse
    {
        try {
            $patient = $this->entityManager->getRepository(Patients::class)->find($patientId);

            if (!$patient) {
                return $this->json([
                    'success' => false,
                    'error' => 'Patient non trouvé',
                ], 404);
            }

            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)
                ->findOneBy(['patientId' => $patient, 'statut' => 'ACTIF'], ['dateOuverture' => 'DESC']);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Aucun dossier médical actif trouvé pour ce patient',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatDossierData($dossier),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du dossier médical actif: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les dossiers médicaux d'un hôpital
     * GET /api/dossiers-medicaux/hopital/{hopitalId}
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
            $statut = $request->query->get('statut');

            $queryBuilder = $this->entityManager->getRepository(DossiersMedicaux::class)
                ->createQueryBuilder('dm')
                ->where('dm.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId)
                ->leftJoin('dm.patientId', 'p')
                ->addSelect('p')
                ->orderBy('dm.dateOuverture', 'DESC');

            if ($statut) {
                $queryBuilder->andWhere('dm.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $dossiers = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (DossiersMedicaux $dossier) {
                return $this->formatDossierData($dossier);
            }, $dossiers);

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
                'error' => 'Erreur lors de la récupération des dossiers médicaux: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporte le dossier médical complet en PDF
     * GET /api/dossiers-medicaux/{id}/export/pdf
     */
    #[Route('/{id}/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(int $id): Response|JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $pdf = $this->generateCompleteMedicalFilePdf($dossier);
            $pdfContent = $pdf->Output('', 'S');

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="dossier_medical_' . $dossier->getNumeroDme() . '_' . date('Y-m-d') . '.pdf"'
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
     * Formate les données d'un dossier médical pour la réponse JSON
     */
    private function formatDossierData(DossiersMedicaux $dossier): array
    {
        $patient = $dossier->getPatientId();
        $medecinReferent = $dossier->getMedecinReferentId();

        return [
            'id' => $dossier->getId(),
            'numeroDme' => $dossier->getNumeroDme(),
            'dateOuverture' => $dossier->getDateOuverture()->format('Y-m-d'),
            'dateFermeture' => $dossier->getDateFermeture()?->format('Y-m-d'),
            'statut' => $dossier->getStatut(),
            'patient' => [
                'id' => $patient->getId(),
                'nom' => $patient->getNom(),
                'prenom' => $patient->getPrenom(),
                'numeroDossier' => $patient->getNumeroDossier(),
            ],
            'medecin_referent' => [
                'id' => $medecinReferent->getId(),
                'nom' => $medecinReferent->getNom(),
                'prenom' => $medecinReferent->getPrenom(),
            ],
            'hopital' => [
                'id' => $dossier->getHopitalId()->getId(),
                'nom' => $dossier->getHopitalId()->getNom(),
            ],
            'dateCreation' => $dossier->getDateCreation()?->format('c'),
            'dateModification' => $dossier->getDateModification()?->format('c'),
        ];
    }

    /**
     * Calcule l'âge à partir de la date de naissance
     */
    private function calculateAge(\DateTimeInterface $dateNaissance): int
    {
        $today = new \DateTime();
        $age = $today->diff($dateNaissance)->y;
        return $age;
    }

    /**
     * Génère un PDF complet du dossier médical avec toutes les informations critiques
     */
    private function generateCompleteMedicalFilePdf(DossiersMedicaux $dossier): TCPDF
    {
        $patient = $dossier->getPatientId();
        $hopital = $dossier->getHopitalId();
        $medecinReferent = $dossier->getMedecinReferentId();

        // Récupérer les données
        $admissions = $this->entityManager->getRepository(Admissions::class)
            ->findBy(['patientId' => $patient], ['dateAdmission' => 'DESC']);

        $consultations = $this->entityManager->getRepository(Consultations::class)
            ->findBy(['patientId' => $patient], ['dateConsultation' => 'DESC']);

        $prescriptions = $this->entityManager->getRepository(Prescriptions::class)
            ->findBy(['patientId' => $patient], ['datePrescription' => 'DESC']);

        $resultatsLabo = $this->entityManager->getRepository(ResultatsLabo::class)
            ->findBy(['patientId' => $patient], ['dateResultat' => 'DESC']);

        $examensImagerie = $this->entityManager->getRepository(ExamensImagerie::class)
            ->findBy(['patientId' => $patient], ['dateExamen' => 'DESC']);

        $notesInfirmieres = $this->entityManager->getRepository(NotesInfirmieres::class)
            ->findBy(['patientId' => $patient], ['dateNote' => 'DESC']);

        // Créer le PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Rehoboth Hospital');
        $pdf->SetAuthor('Rehoboth Hospital');
        $pdf->SetTitle('Dossier Médical Complet - ' . $patient->getNom() . ' ' . $patient->getPrenom());
        $pdf->SetSubject('Dossier Médical Complet');
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // PAGE 1 - EN-TÊTE ET INFORMATIONS PATIENT
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'DOSSIER MÉDICAL COMPLET', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Hôpital: ' . $hopital->getNom(), 0, 1);
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
        $this->addPdfRow($pdf, 'Sexe:', $patient->getSexe() === 'M' ? 'Masculin' : 'Féminin');
        $this->addPdfRow($pdf, 'Groupe sanguin:', $patient->getGroupeSanguin() ?? 'N/A');
        $this->addPdfRow($pdf, 'Allergies:', $patient->getAllergies() ?? 'Aucune');

        $pdf->Ln(5);

        // Médecin référent
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'MÉDECIN RÉFÉRENT', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $this->addPdfRow($pdf, 'Nom:', $medecinReferent->getNom() . ' ' . $medecinReferent->getPrenom());
        $this->addPdfRow($pdf, 'Email:', $medecinReferent->getEmail() ?? 'N/A');
        $this->addPdfRow($pdf, 'Téléphone:', $medecinReferent->getTelephone() ?? 'N/A');

        $pdf->Ln(5);

        // Informations du dossier
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DOSSIER MÉDICAL', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $this->addPdfRow($pdf, 'Numéro DME:', $dossier->getNumeroDme());
        $this->addPdfRow($pdf, 'Date d\'ouverture:', $dossier->getDateOuverture()->format('d/m/Y'));
        $this->addPdfRow($pdf, 'Statut:', $dossier->getStatut() ?? 'ACTIF');

        if ($dossier->getNotesGenerales()) {
            $pdf->Ln(3);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, 'Notes générales:', 0, 1);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 4, $dossier->getNotesGenerales());
        }

        // PAGE 2+ - HISTORIQUE MÉDICAL
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'HISTORIQUE MÉDICAL', 0, 1);
        $pdf->Ln(3);

        // Admissions
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'ADMISSIONS', 0, 1);
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
                $this->addPdfRow($pdf, 'Motif:', $admission->getMotifAdmission() ?? 'N/A');
                $this->addPdfRow($pdf, 'Diagnostic principal:', $admission->getDiagnosticPrincipal() ?? 'N/A');

                $pdf->Ln(2);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucune admission trouvée.');
        }

        // Consultations
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'CONSULTATIONS', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        if (!empty($consultations)) {
            foreach (array_slice($consultations, 0, 5) as $consultation) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, 'Consultation du ' . $consultation->getDateConsultation()->format('d/m/Y'), 0, 1);

                $pdf->SetFont('helvetica', '', 9);
                $this->addPdfRow($pdf, 'Motif:', $consultation->getMotif() ?? 'N/A');
                $this->addPdfRow($pdf, 'Diagnostic:', $consultation->getDiagnostic() ?? 'N/A');

                $pdf->Ln(2);
            }
            if (count($consultations) > 5) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(0, 4, '... et ' . (count($consultations) - 5) . ' autres consultations', 0, 1);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucune consultation trouvée.');
        }

        // Prescriptions
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'PRESCRIPTIONS ACTIVES', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        if (!empty($prescriptions)) {
            foreach (array_slice($prescriptions, 0, 5) as $prescription) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, $prescription->getMedicamentId()?->getNom() ?? 'Médicament', 0, 1);

                $pdf->SetFont('helvetica', '', 9);
                $this->addPdfRow($pdf, 'Dosage:', $prescription->getDosage() ?? 'N/A');
                $this->addPdfRow($pdf, 'Fréquence:', $prescription->getFrequence() ?? 'N/A');
                $this->addPdfRow($pdf, 'Statut:', $prescription->getStatut() ?? 'N/A');

                $pdf->Ln(2);
            }
            if (count($prescriptions) > 5) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(0, 4, '... et ' . (count($prescriptions) - 5) . ' autres prescriptions', 0, 1);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucune prescription trouvée.');
        }

        // Résultats de laboratoire
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'RÉSULTATS DE LABORATOIRE', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        if (!empty($resultatsLabo)) {
            foreach (array_slice($resultatsLabo, 0, 5) as $resultat) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, $resultat->getTypeExamen() . ' - ' . $resultat->getDateResultat()->format('d/m/Y'), 0, 1);

                $pdf->SetFont('helvetica', '', 9);
                $this->addPdfRow($pdf, 'Valeur:', $resultat->getValeur() . ' ' . ($resultat->getUnite() ?? ''));
                $this->addPdfRow($pdf, 'Interprétation:', $resultat->getInterpretation() ?? 'N/A');

                $pdf->Ln(2);
            }
            if (count($resultatsLabo) > 5) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(0, 4, '... et ' . (count($resultatsLabo) - 5) . ' autres résultats', 0, 1);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucun résultat de laboratoire trouvé.');
        }

        // Examens d'imagerie
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'EXAMENS D\'IMAGERIE', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        if (!empty($examensImagerie)) {
            foreach (array_slice($examensImagerie, 0, 5) as $examen) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, $examen->getTypeImagerie() . ' - ' . $examen->getDateExamen()->format('d/m/Y'), 0, 1);

                $pdf->SetFont('helvetica', '', 9);
                $this->addPdfRow($pdf, 'Zone:', $examen->getZone() ?? 'N/A');
                $this->addPdfRow($pdf, 'Conclusions:', $examen->getConclusions() ?? 'N/A');

                $pdf->Ln(2);
            }
            if (count($examensImagerie) > 5) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(0, 4, '... et ' . (count($examensImagerie) - 5) . ' autres examens', 0, 1);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucun examen d\'imagerie trouvé.');
        }

        // Notes infirmières
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'NOTES INFIRMIÈRES', 0, 1);
        $pdf->SetFont('helvetica', '', 9);

        if (!empty($notesInfirmieres)) {
            foreach (array_slice($notesInfirmieres, 0, 3) as $note) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(0, 6, 'Note du ' . $note->getDateNote()->format('d/m/Y H:i'), 0, 1);

                $pdf->SetFont('helvetica', '', 9);
                $pdf->MultiCell(0, 4, $note->getContenu() ?? 'N/A');

                $pdf->Ln(2);
            }
            if (count($notesInfirmieres) > 3) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(0, 4, '... et ' . (count($notesInfirmieres) - 3) . ' autres notes', 0, 1);
            }
        } else {
            $pdf->MultiCell(0, 5, 'Aucune note infirmière trouvée.');
        }

        // Pied de document
        $pdf->Ln(5);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 4, 'Document confidentiel - Réservé au personnel médical | Généré le ' . (new \DateTime())->format('d/m/Y à H:i:s'), 0, 1, 'C');

        return $pdf;
    }

    /**
     * Ajoute ou met à jour les antécédents familiaux du patient
     * POST /api/dossiers-medicaux/{id}/antecedents-familiaux
     */
    #[Route('/{id}/antecedents-familiaux', name: 'add_antecedents_familiaux', methods: ['POST'])]
    public function addAntecedentsFamiliaux(int $id, Request $request): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $patient = $dossier->getPatientId();
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les antécédents familiaux
            if (isset($data['pere'])) $patient->setAntecedentsFamiliauxPere($data['pere']);
            if (isset($data['mere'])) $patient->setAntecedentsFamiliauxMere($data['mere']);
            if (isset($data['enfants'])) $patient->setAntecedentsFamiliauxEnfants($data['enfants']);
            if (isset($data['epouse'])) $patient->setAntecedentsFamiliauxEpouse($data['epouse']);
            if (isset($data['autres'])) $patient->setAntecedentsFamiliauxAutres($data['autres']);

            $patient->setDateDerniereMiseAJourDossier(new \DateTime());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Antécédents familiaux ajoutés avec succès',
                'data' => [
                    'pere' => $patient->getAntecedentsFamiliauxPere(),
                    'mere' => $patient->getAntecedentsFamiliauxMere(),
                    'enfants' => $patient->getAntecedentsFamiliauxEnfants(),
                    'epouse' => $patient->getAntecedentsFamiliauxEpouse(),
                    'autres' => $patient->getAntecedentsFamiliauxAutres(),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'ajout des antécédents familiaux: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajoute ou met à jour l'historique des vaccinations
     * POST /api/dossiers-medicaux/{id}/vaccinations
     */
    #[Route('/{id}/vaccinations', name: 'add_vaccinations', methods: ['POST'])]
    public function addVaccinations(int $id, Request $request): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $patient = $dossier->getPatientId();
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les vaccinations
            if (isset($data['historique'])) {
                $patient->setHistoriqueVaccinations(json_encode($data['historique']));
            }

            if (isset($data['date_derniere_vaccination'])) {
                $patient->setDateDerniereVaccination(new \DateTime($data['date_derniere_vaccination']));
            }

            $patient->setDateDerniereMiseAJourDossier(new \DateTime());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Historique des vaccinations mis à jour avec succès',
                'data' => [
                    'historique' => json_decode($patient->getHistoriqueVaccinations(), true),
                    'date_derniere_vaccination' => $patient->getDateDerniereVaccination()?->format('Y-m-d'),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour des vaccinations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajoute ou met à jour les informations critiques du patient
     * POST /api/dossiers-medicaux/{id}/informations-critiques
     */
    #[Route('/{id}/informations-critiques', name: 'add_informations_critiques', methods: ['POST'])]
    public function addInformationsCritiques(int $id, Request $request): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouv��',
                ], 404);
            }

            $patient = $dossier->getPatientId();
            $data = json_decode($request->getContent(), true);

            // Mettre à jour les informations critiques
            if (isset($data['habitudes_vie'])) $patient->setHabitudesVie($data['habitudes_vie']);
            if (isset($data['facteurs_risque'])) $patient->setFacteursRisque($data['facteurs_risque']);
            if (isset($data['observations_generales'])) $patient->setObservationsGenerales($data['observations_generales']);

            $patient->setDateDerniereMiseAJourDossier(new \DateTime());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Informations critiques mises à jour avec succès',
                'data' => [
                    'habitudes_vie' => $patient->getHabitudesVie(),
                    'facteurs_risque' => $patient->getFacteursRisque(),
                    'observations_generales' => $patient->getObservationsGenerales(),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour des informations critiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère toutes les informations supplémentaires du dossier médical
     * GET /api/dossiers-medicaux/{id}/informations-supplementaires
     */
    #[Route('/{id}/informations-supplementaires', name: 'get_informations_supplementaires', methods: ['GET'])]
    public function getInformationsSupplementaires(int $id): JsonResponse
    {
        try {
            $dossier = $this->entityManager->getRepository(DossiersMedicaux::class)->find($id);

            if (!$dossier) {
                return $this->json([
                    'success' => false,
                    'error' => 'Dossier médical non trouvé',
                ], 404);
            }

            $patient = $dossier->getPatientId();

            $data = [
                'antecedents_familiaux' => [
                    'pere' => $patient->getAntecedentsFamiliauxPere(),
                    'mere' => $patient->getAntecedentsFamiliauxMere(),
                    'enfants' => $patient->getAntecedentsFamiliauxEnfants(),
                    'epouse' => $patient->getAntecedentsFamiliauxEpouse(),
                    'autres' => $patient->getAntecedentsFamiliauxAutres(),
                ],
                'vaccinations' => [
                    'historique' => json_decode($patient->getHistoriqueVaccinations() ?? '[]', true),
                    'date_derniere_vaccination' => $patient->getDateDerniereVaccination()?->format('Y-m-d'),
                ],
                'informations_critiques' => [
                    'habitudes_vie' => $patient->getHabitudesVie(),
                    'facteurs_risque' => $patient->getFacteursRisque(),
                    'observations_generales' => $patient->getObservationsGenerales(),
                ],
                'date_derniere_mise_a_jour' => $patient->getDateDerniereMiseAJourDossier()?->format('c'),
            ];

            return $this->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des informations supplémentaires: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajoute une ligne au PDF avec label et valeur
     */
    private function addPdfRow(TCPDF $pdf, string $label, string $value): void
    {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 5, $label, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $value, 0, 1);
    }
}

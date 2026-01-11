<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Patients\Lits;
use App\Entity\Administration\Services;
use App\Entity\Administration\Hopitaux;
use App\Service\LitTicketGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des lits hospitaliers
 * 
 * Endpoints:
 * - GET /api/lits - Lister tous les lits
 * - GET /api/lits/{id} - Récupérer un lit
 * - POST /api/lits - Créer un lit
 * - PUT /api/lits/{id} - Modifier un lit
 * - DELETE /api/lits/{id} - Supprimer un lit
 * - GET /api/lits/service/{serviceId} - Lister les lits d'un service
 * - GET /api/lits/hopital/{hopitalId} - Lister les lits d'un hôpital
 * - GET /api/lits/statut/{statut} - Lister les lits par statut
 */
#[Route('/api/lits', name: 'api_lits_')]
class LitsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister tous les lits
     * GET /api/lits
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $serviceId = $request->query->getInt('service_id', 0);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $statut = $request->query->get('statut', null);

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $queryBuilder = $litsRepo->createQueryBuilder('l');

            if ($serviceId > 0) {
                $queryBuilder->where('l.serviceId = :serviceId')
                    ->setParameter('serviceId', $serviceId);
            }

            if ($hopitalId > 0) {
                $queryBuilder->andWhere('l.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($statut !== null) {
                $queryBuilder->andWhere('l.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $lits = $queryBuilder
                ->orderBy('l.numeroLit', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($lits as $lit) {
                $data[] = $this->serializeLit($lit);
            }

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des lits: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un lit par ID
     * GET /api/lits/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $lit = $this->entityManager->getRepository(Lits::class)->find($id);

            if (!$lit) {
                return $this->json([
                    'success' => false,
                    'error' => 'Lit non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeLit($lit),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer un nouveau lit
     * POST /api/lits
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des champs requis
            $requiredFields = ['numero_lit', 'chambre_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            // Vérifier que la chambre existe
            $chambreRepo = $this->entityManager->getRepository(\App\Entity\Patients\Chambres::class);
            $chambre = $chambreRepo->find($data['chambre_id']);
            if (!$chambre) {
                return $this->json([
                    'success' => false,
                    'error' => 'Chambre non trouvée',
                ], 404);
            }

            $lit = new Lits();
            $lit->setNumeroLit($data['numero_lit']);
            $lit->setChambreId($chambre);
            $lit->setServiceId($chambre->getServiceId());
            $lit->setHopitalId($chambre->getHopitalId());
            $lit->setTypeLit($data['type_lit'] ?? null);
            $lit->setEtage($data['etage'] ?? null);
            $lit->setStatut($data['statut'] ?? 'disponible');
            $lit->setDateDerniereMaintenance($data['date_derniere_maintenance'] ?? null);

            $this->entityManager->persist($lit);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Lit créé avec succès',
                'data' => $this->serializeLit($lit),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier un lit
     * PUT /api/lits/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $lit = $this->entityManager->getRepository(Lits::class)->find($id);

            if (!$lit) {
                return $this->json([
                    'success' => false,
                    'error' => 'Lit non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['numero_lit'])) {
                $lit->setNumeroLit($data['numero_lit']);
            }
            if (isset($data['type_lit'])) {
                $lit->setTypeLit($data['type_lit']);
            }
            if (isset($data['etage'])) {
                $lit->setEtage($data['etage']);
            }
            if (isset($data['statut'])) {
                $lit->setStatut($data['statut']);
            }
            if (isset($data['date_derniere_maintenance'])) {
                $lit->setDateDerniereMaintenance($data['date_derniere_maintenance']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Lit modifié avec succès',
                'data' => $this->serializeLit($lit),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un lit
     * DELETE /api/lits/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $lit = $this->entityManager->getRepository(Lits::class)->find($id);

            if (!$lit) {
                return $this->json([
                    'success' => false,
                    'error' => 'Lit non trouvé',
                ], 404);
            }

            $this->entityManager->remove($lit);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Lit supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les lits d'un service
     * GET /api/lits/service/{serviceId}
     */
    #[Route('/service/{serviceId}', name: 'by_service', methods: ['GET'])]
    public function byService(int $serviceId, Request $request): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $queryBuilder = $litsRepo->createQueryBuilder('l')
                ->where('l.serviceId = :serviceId')
                ->setParameter('serviceId', $serviceId);

            $total = count($queryBuilder->getQuery()->getResult());

            $lits = $queryBuilder
                ->orderBy('l.numeroLit', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($lits as $lit) {
                $data[] = $this->serializeLit($lit);
            }

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les lits d'un hôpital
     * GET /api/lits/hopital/{hopitalId}
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

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $queryBuilder = $litsRepo->createQueryBuilder('l')
                ->where('l.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $lits = $queryBuilder
                ->orderBy('l.numeroLit', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($lits as $lit) {
                $data[] = $this->serializeLit($lit);
            }

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les lits par statut
     * GET /api/lits/statut/{statut}
     */
    #[Route('/statut/{statut}', name: 'by_statut', methods: ['GET'])]
    public function byStatut(string $statut, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $queryBuilder = $litsRepo->createQueryBuilder('l')
                ->where('l.statut = :statut')
                ->setParameter('statut', $statut);

            $total = count($queryBuilder->getQuery()->getResult());

            $lits = $queryBuilder
                ->orderBy('l.numeroLit', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($lits as $lit) {
                $data[] = $this->serializeLit($lit);
            }

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Télécharger le ticket d'un lit en PDF
     * GET /api/lits/{id}/ticket/download
     */
    #[Route('/{id}/ticket/download', name: 'ticket_download', methods: ['GET'])]
    public function downloadTicket(int $id, LitTicketGeneratorService $ticketGenerator): Response
    {
        try {
            $lit = $this->entityManager->getRepository(Lits::class)->find($id);

            if (!$lit) {
                return new Response('Lit non trouvé', 404);
            }

            $pdfContent = $ticketGenerator->generateTicket($lit, true);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="ticket_lit_' . $lit->getNumeroLit() . '.pdf"',
                    'Content-Length' => strlen($pdfContent),
                ]
            );

        } catch (Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher le ticket d'un lit en PDF (pour impression directe)
     * GET /api/lits/{id}/ticket/print
     */
    #[Route('/{id}/ticket/print', name: 'ticket_print', methods: ['GET'])]
    public function printTicket(int $id, LitTicketGeneratorService $ticketGenerator): Response
    {
        try {
            $lit = $this->entityManager->getRepository(Lits::class)->find($id);

            if (!$lit) {
                return new Response('Lit non trouvé', 404);
            }

            $pdfContent = $ticketGenerator->generateTicket($lit, false);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="ticket_lit_' . $lit->getNumeroLit() . '.pdf"',
                ]
            );

        } catch (Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Télécharger les tickets de plusieurs lits en PDF
     * POST /api/lits/tickets/download-multiple
     * 
     * Body JSON:
     * {
     *   "lit_ids": [1, 2, 3, 4]
     * }
     */
    #[Route('/tickets/download-multiple', name: 'tickets_download_multiple', methods: ['POST'])]
    public function downloadMultipleTickets(Request $request, LitTicketGeneratorService $ticketGenerator): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['lit_ids']) || !is_array($data['lit_ids'])) {
                return new Response('Le champ "lit_ids" est requis et doit être un tableau', 400);
            }

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $lits = [];

            foreach ($data['lit_ids'] as $litId) {
                $lit = $litsRepo->find($litId);
                if ($lit) {
                    $lits[] = $lit;
                }
            }

            if (empty($lits)) {
                return new Response('Aucun lit trouvé', 404);
            }

            $pdfContent = $ticketGenerator->generateMultipleTickets($lits);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="tickets_lits_' . date('Y-m-d_H-i-s') . '.pdf"',
                    'Content-Length' => strlen($pdfContent),
                ]
            );

        } catch (Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher les tickets de plusieurs lits en PDF (pour impression directe)
     * POST /api/lits/tickets/print-multiple
     * 
     * Body JSON:
     * {
     *   "lit_ids": [1, 2, 3, 4]
     * }
     */
    #[Route('/tickets/print-multiple', name: 'tickets_print_multiple', methods: ['POST'])]
    public function printMultipleTickets(Request $request, LitTicketGeneratorService $ticketGenerator): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['lit_ids']) || !is_array($data['lit_ids'])) {
                return new Response('Le champ "lit_ids" est requis et doit être un tableau', 400);
            }

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $lits = [];

            foreach ($data['lit_ids'] as $litId) {
                $lit = $litsRepo->find($litId);
                if ($lit) {
                    $lits[] = $lit;
                }
            }

            if (empty($lits)) {
                return new Response('Aucun lit trouvé', 404);
            }

            $pdfContent = $ticketGenerator->generateMultipleTickets($lits);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="tickets_lits_' . date('Y-m-d_H-i-s') . '.pdf"',
                ]
            );

        } catch (Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Télécharger les tickets de tous les lits d'un service
     * GET /api/lits/service/{serviceId}/tickets/download
     */
    #[Route('/service/{serviceId}/tickets/download', name: 'service_tickets_download', methods: ['GET'])]
    public function downloadServiceTickets(int $serviceId, LitTicketGeneratorService $ticketGenerator): Response
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);

            if (!$service) {
                return new Response('Service non trouvé', 404);
            }

            $litsRepo = $this->entityManager->getRepository(Lits::class);
            $lits = $litsRepo->findBy(['serviceId' => $serviceId]);

            if (empty($lits)) {
                return new Response('Aucun lit trouvé pour ce service', 404);
            }

            $pdfContent = $ticketGenerator->generateMultipleTickets($lits);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="tickets_service_' . $service->getCode() . '_' . date('Y-m-d') . '.pdf"',
                    'Content-Length' => strlen($pdfContent),
                ]
            );

        } catch (Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sérialiser un lit pour la réponse JSON
     */
    private function serializeLit(Lits $lit): array
    {
        return [
            'id' => $lit->getId(),
            'numero_lit' => $lit->getNumeroLit(),
            'type_lit' => $lit->getTypeLit(),
            'etage' => $lit->getEtage(),
            'statut' => $lit->getStatut(),
            'date_derniere_maintenance' => $lit->getDateDerniereMaintenance()?->format('Y-m-d'),
            'date_creation' => $lit->getDateCreation()?->format('Y-m-d H:i:s'),
            'service_id' => $lit->getServiceId()->getId(),
            'hopital_id' => $lit->getHopitalId()->getId(),
            'chambre_id' => $lit->getChambreId()->getId(),
            'chambre_numero' => $lit->getChambreId()->getNumeroChambre(),
        ];
    }
}

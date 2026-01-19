<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\AuditTrail;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoints d'audit immuable
 * 
 * 🚫 AUCUN DELETE
 * 👉 L'audit est IMMUABLE
 * ✅ Lecture seule (GET)
 * ✅ Traçabilité légale: QUI/QUOI/QUAND/OÙ
 * 
 * @see https://owasp.org/www-project-application-security-verification-standard/
 * @see https://www.iso.org/standard/27001
 */
#[Route('/api/administrations/audit', name: 'api_audit_')]
class AuditTrailController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JwtService $jwtService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $actionType = $request->query->get('actionType');
            $entiteType = $request->query->get('entiteType');
            $entiteId = $request->query->get('entiteId');
            $utilisateurId = $request->query->get('utilisateurId');
            $statut = $request->query->get('statut');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(500, max(1, $request->query->getInt('limit', 100)));
            $offset = ($page - 1) * $limit;

            $qb = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->leftJoin('a.hopitalId', 'h')
                ->addSelect('u', 'h')
                ->orderBy('a.dateAction', 'DESC');

            if ($hopitalId > 0) {
                $qb->andWhere('h.id = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }
            if ($actionType) {
                $qb->andWhere('a.actionType = :actionType')->setParameter('actionType', $actionType);
            }
            if ($entiteType) {
                $qb->andWhere('a.entiteType = :entiteType')->setParameter('entiteType', $entiteType);
            }
            if ($entiteId) {
                $qb->andWhere('a.entiteId = :entiteId')->setParameter('entiteId', (int) $entiteId);
            }
            if ($utilisateurId) {
                $qb->andWhere('u.id = :utilisateurId')->setParameter('utilisateurId', (int) $utilisateurId);
            }
            if ($statut) {
                $qb->andWhere('a.statut = :statut')->setParameter('statut', $statut);
            }

            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('a.dateAction >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }
            if ($dateTo) {
                $dtTo = (\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo) ?: new \DateTimeImmutable($dateTo))
                    ->setTime(23, 59, 59);
                $qb->andWhere('a.dateAction <= :dateTo')->setParameter('dateTo', $dtTo);
            }

            $countQb = clone $qb;
            $total = (int) $countQb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (AuditTrail $audit): array {
                $u = $audit->getUtilisateurId();
                $h = $audit->getHopitalId();

                return [
                    'id' => $audit->getId(),
                    'actionType' => $audit->getActionType(),
                    'entiteType' => $audit->getEntiteType(),
                    'entiteId' => $audit->getEntiteId(),
                    'description' => $audit->getDescription(),
                    'statut' => $audit->getStatut(),
                    'messageErreur' => $audit->getMessageErreur(),
                    'adresseIp' => $audit->getAdresseIp(),
                    'dateAction' => $audit->getDateAction()->format('c'),
                    'utilisateur' => [
                        'id' => $u->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                        'username' => method_exists($u, 'getUsername') ? $u->getUsername() : null,
                        'email' => method_exists($u, 'getEmail') ? $u->getEmail() : null,
                    ],
                    'hopital' => [
                        'id' => $h->getId(),
                        'nom' => $h->getNom(),
                    ],
                ];
            }, $items);

            return $this->json([
                'success' => true,
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $audit = $this->entityManager->getRepository(AuditTrail::class)->find($id);
            if (!$audit) {
                return $this->json(['success' => false, 'error' => 'Audit non trouvé'], 404);
            }

            $u = $audit->getUtilisateurId();
            $h = $audit->getHopitalId();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $audit->getId(),
                    'actionType' => $audit->getActionType(),
                    'entiteType' => $audit->getEntiteType(),
                    'entiteId' => $audit->getEntiteId(),
                    'description' => $audit->getDescription(),
                    'ancienneValeur' => $audit->getAncienneValeur(),
                    'nouvelleValeur' => $audit->getNouvelleValeur(),
                    'statut' => $audit->getStatut(),
                    'messageErreur' => $audit->getMessageErreur(),
                    'adresseIp' => $audit->getAdresseIp(),
                    'userAgent' => $audit->getUserAgent(),
                    'signature' => $audit->getSignature(),
                    'dateAction' => $audit->getDateAction()->format('c'),
                    'utilisateur' => [
                        'id' => $u->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                        'username' => method_exists($u, 'getUsername') ? $u->getUsername() : null,
                        'email' => method_exists($u, 'getEmail') ? $u->getEmail() : null,
                    ],
                    'hopital' => [
                        'id' => $h->getId(),
                        'nom' => $h->getNom(),
                    ],
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/entite/{entiteType}/{entiteId}', name: 'entity_history', methods: ['GET'])]
    public function entityHistory(string $entiteType, int $entiteId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 50)));

            $audits = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->addSelect('u')
                ->where('a.entiteType = :entiteType')
                ->andWhere('a.entiteId = :entiteId')
                ->setParameter('entiteType', $entiteType)
                ->setParameter('entiteId', $entiteId)
                ->orderBy('a.dateAction', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (AuditTrail $audit): array {
                $u = $audit->getUtilisateurId();

                return [
                    'id' => $audit->getId(),
                    'actionType' => $audit->getActionType(),
                    'description' => $audit->getDescription(),
                    'ancienneValeur' => $audit->getAncienneValeur(),
                    'nouvelleValeur' => $audit->getNouvelleValeur(),
                    'statut' => $audit->getStatut(),
                    'dateAction' => $audit->getDateAction()->format('c'),
                    'utilisateur' => [
                        'id' => $u->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                    ],
                ];
            }, $audits);

            return $this->json([
                'success' => true,
                'entiteType' => $entiteType,
                'entiteId' => $entiteId,
                'total' => count($data),
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/utilisateur/{utilisateurId}', name: 'user_history', methods: ['GET'])]
    public function userHistory(int $utilisateurId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 50)));

            $audits = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->where('a.utilisateurId = :utilisateur')
                ->setParameter('utilisateur', $utilisateurId)
                ->orderBy('a.dateAction', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (AuditTrail $audit): array {
                return [
                    'id' => $audit->getId(),
                    'actionType' => $audit->getActionType(),
                    'entiteType' => $audit->getEntiteType(),
                    'entiteId' => $audit->getEntiteId(),
                    'description' => $audit->getDescription(),
                    'statut' => $audit->getStatut(),
                    'dateAction' => $audit->getDateAction()->format('c'),
                ];
            }, $audits);

            return $this->json([
                'success' => true,
                'utilisateurId' => $utilisateurId,
                'total' => count($data),
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    private function getAuthenticatedUser(Request $request): ?Utilisateurs
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        $userId = $this->verifyToken($token);
        if (!$userId) {
            return null;
        }

        return $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
    }

    private function verifyToken(string $token): ?int
    {
        try {
            $payload = $this->jwtService->validateToken($token);
            return $payload->id ?? null;
        } catch (ExpiredException|SignatureInvalidException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function hasAuditRole(Utilisateurs $user): bool
    {
        $role = $user->getRoleId();
        return $role && in_array($role->getCode(), ['ADMIN', 'SUPER_ADMIN', 'AUDIT', 'COMPLIANCE'], true);
    }
}

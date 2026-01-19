<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\BackupMetadata;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use App\Service\JwtService;
use App\Service\BackupService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoints de gestion des sauvegardes (backups)
 * 
 * ⚠️ Très sensibles – accès strict ADMIN / SYSTEM uniquement
 * 
 * Règle 3-2-1:
 * - 3 copies
 * - 2 supports différents
 * - 1 hors site
 * 
 * ✔ Sauvegardes automatisées
 * ✔ Chiffrement (AES-256)
 * ✔ Tests réguliers de restauration
 * ✔ Journalisation des restaurations (audit)
 * 
 * @see https://nvlpubs.nist.gov/nistpubs/Legacy/SP/nistspecialpublication800-34r1.pdf
 * @see https://www.iso.org/standard/27001
 */
#[Route('/api/administrations/backups', name: 'api_backups_')]
class BackupsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JwtService $jwtService,
        private readonly BackupService $backupService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé (SYSTEM/ADMIN requis)'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $typeBackup = $request->query->get('typeBackup');
            $statut = $request->query->get('statut');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(200, max(1, $request->query->getInt('limit', 50)));
            $offset = ($page - 1) * $limit;

            $qb = $this->entityManager->getRepository(BackupMetadata::class)
                ->createQueryBuilder('b')
                ->leftJoin('b.utilisateurId', 'u')
                ->leftJoin('b.hopitalId', 'h')
                ->addSelect('u', 'h')
                ->orderBy('b.dateDebut', 'DESC');

            if ($hopitalId > 0) {
                $qb->andWhere('h.id = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }
            if ($typeBackup) {
                $qb->andWhere('b.typeBackup = :typeBackup')->setParameter('typeBackup', $typeBackup);
            }
            if ($statut) {
                $qb->andWhere('b.statut = :statut')->setParameter('statut', $statut);
            }

            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('b.dateDebut >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }
            if ($dateTo) {
                $dtTo = (\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo) ?: new \DateTimeImmutable($dateTo))
                    ->setTime(23, 59, 59);
                $qb->andWhere('b.dateDebut <= :dateTo')->setParameter('dateTo', $dtTo);
            }

            $countQb = clone $qb;
            $total = (int) $countQb->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();

            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (BackupMetadata $b): array {
                return [
                    'id' => $b->getId(),
                    'backupId' => $b->getBackupId(),
                    'typeBackup' => $b->getTypeBackup(),
                    'statut' => $b->getStatut(),
                    'localisationBackup' => $b->getLocalisationBackup(),
                    'localisationSecondaire' => $b->getLocalisationSecondaire(),
                    'tailleBytes' => $b->getTailleBytes(),
                    'tailleGb' => $b->getTailleBytes() ? round($b->getTailleBytes() / (1024 ** 3), 2) : null,
                    'dureeSecondes' => $b->getDureeSecondes(),
                    'nombreFichiers' => $b->getNombreFichiers(),
                    'nombreTables' => $b->getNombreTables(),
                    'checksumSha256' => $b->getChecksumSha256(),
                    'compression' => $b->getCompression(),
                    'dateDebut' => $b->getDateDebut()->format('c'),
                    'dateFin' => $b->getDateFin()?->format('c'),
                    'dateExpiration' => $b->getDateExpiration()?->format('c'),
                    'utilisateur' => [
                        'id' => $b->getUtilisateurId()->getId(),
                        'nom' => method_exists($b->getUtilisateurId(), 'getNom') ? $b->getUtilisateurId()->getNom() : null,
                        'username' => method_exists($b->getUtilisateurId(), 'getUsername') ? $b->getUtilisateurId()->getUsername() : null,
                    ],
                    'hopital' => [
                        'id' => $b->getHopitalId()->getId(),
                        'nom' => $b->getHopitalId()->getNom(),
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

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $b = $this->entityManager->getRepository(BackupMetadata::class)->find($id);
            if (!$b) {
                return $this->json(['success' => false, 'error' => 'Backup non trouvé'], 404);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $b->getId(),
                    'backupId' => $b->getBackupId(),
                    'typeBackup' => $b->getTypeBackup(),
                    'statut' => $b->getStatut(),
                    'localisationBackup' => $b->getLocalisationBackup(),
                    'localisationSecondaire' => $b->getLocalisationSecondaire(),
                    'tailleBytes' => $b->getTailleBytes(),
                    'tailleGb' => $b->getTailleBytes() ? round($b->getTailleBytes() / (1024 ** 3), 2) : null,
                    'dureeSecondes' => $b->getDureeSecondes(),
                    'nombreFichiers' => $b->getNombreFichiers(),
                    'nombreTables' => $b->getNombreTables(),
                    'checksumSha256' => $b->getChecksumSha256(),
                    'compression' => $b->getCompression(),
                    'cleChiffrement' => $b->getCleChiffrement(),
                    'messageErreur' => $b->getMessageErreur(),
                    'notes' => $b->getNotes(),
                    'dateDebut' => $b->getDateDebut()->format('c'),
                    'dateFin' => $b->getDateFin()?->format('c'),
                    'dateExpiration' => $b->getDateExpiration()?->format('c'),
                    'utilisateur' => [
                        'id' => $b->getUtilisateurId()->getId(),
                        'nom' => method_exists($b->getUtilisateurId(), 'getNom') ? $b->getUtilisateurId()->getNom() : null,
                        'username' => method_exists($b->getUtilisateurId(), 'getUsername') ? $b->getUtilisateurId()->getUsername() : null,
                    ],
                    'hopital' => [
                        'id' => $b->getHopitalId()->getId(),
                        'nom' => $b->getHopitalId()->getNom(),
                    ],
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $payload = json_decode($request->getContent(), true);
            if (!is_array($payload)) {
                return $this->json(['success' => false, 'error' => 'Payload JSON invalide'], 400);
            }

            $hopitalId = $payload['hopitalId'] ?? null;
            $typeBackup = $payload['typeBackup'] ?? 'COMPLETE';
            $localisationBackup = $payload['localisationBackup'] ?? null;
            $localisationSecondaire = $payload['localisationSecondaire'] ?? null;

            if (!$hopitalId) {
                return $this->json(['success' => false, 'error' => 'hopitalId requis'], 400);
            }
            if (!$localisationBackup) {
                return $this->json(['success' => false, 'error' => 'localisationBackup requis'], 400);
            }

            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find((int) $hopitalId);
            if (!$hopital) {
                return $this->json(['success' => false, 'error' => 'Hôpital non trouvé'], 404);
            }

            $backup = $this->backupService->createBackup(
                $user,
                $hopital,
                $typeBackup,
                $localisationBackup,
                $localisationSecondaire,
            );

            return $this->json([
                'success' => true,
                'message' => 'Backup créé',
                'id' => $backup->getId(),
                'backupId' => $backup->getBackupId(),
            ], 201);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $payload = json_decode($request->getContent(), true);
            if (!is_array($payload)) {
                return $this->json(['success' => false, 'error' => 'Payload JSON invalide'], 400);
            }

            $backup = $this->entityManager->getRepository(BackupMetadata::class)->find($id);
            if (!$backup) {
                return $this->json(['success' => false, 'error' => 'Backup non trouvé'], 404);
            }

            $statut = $payload['statut'] ?? null;
            if (!$statut) {
                return $this->json(['success' => false, 'error' => 'statut requis'], 400);
            }

            $backup = $this->backupService->updateBackupStatus(
                $backup,
                $statut,
                $payload['tailleBytes'] ?? null,
                $payload['dureeSecondes'] ?? null,
                $payload['checksumSha256'] ?? null,
                $payload['nombreFichiers'] ?? null,
                $payload['nombreTables'] ?? null,
                $payload['messageErreur'] ?? null,
            );

            return $this->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'data' => [
                    'id' => $backup->getId(),
                    'statut' => $backup->getStatut(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/verify', name: 'verify', methods: ['POST'])]
    public function verify(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $backup = $this->entityManager->getRepository(BackupMetadata::class)->find($id);
            if (!$backup) {
                return $this->json(['success' => false, 'error' => 'Backup non trouvé'], 404);
            }

            $backup = $this->backupService->verifyBackup($backup);

            return $this->json([
                'success' => true,
                'message' => 'Backup vérifié',
                'data' => [
                    'id' => $backup->getId(),
                    'statut' => $backup->getStatut(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/hopital/{hopitalId}/last-successful', name: 'last_successful', methods: ['GET'])]
    public function lastSuccessful(int $hopitalId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($hopitalId);
            if (!$hopital) {
                return $this->json(['success' => false, 'error' => 'Hôpital non trouvé'], 404);
            }

            $backup = $this->backupService->getLastSuccessfulBackup($hopital);
            if (!$backup) {
                return $this->json(['success' => false, 'error' => 'Aucun backup réussi trouvé'], 404);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $backup->getId(),
                    'backupId' => $backup->getBackupId(),
                    'typeBackup' => $backup->getTypeBackup(),
                    'statut' => $backup->getStatut(),
                    'tailleGb' => $backup->getTailleBytes() ? round($backup->getTailleBytes() / (1024 ** 3), 2) : null,
                    'dateDebut' => $backup->getDateDebut()->format('c'),
                    'dateFin' => $backup->getDateFin()?->format('c'),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/hopital/{hopitalId}/stats', name: 'stats', methods: ['GET'])]
    public function stats(int $hopitalId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasSystemRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($hopitalId);
            if (!$hopital) {
                return $this->json(['success' => false, 'error' => 'Hôpital non trouvé'], 404);
            }

            $stats = $this->backupService->getBackupStats($hopital);

            return $this->json([
                'success' => true,
                'data' => $stats,
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

    private function hasSystemRole(Utilisateurs $user): bool
    {
        $role = $user->getRoleId();
        return $role && in_array($role->getCode(), ['ADMIN', 'SUPER_ADMIN', 'SYSTEM'], true);
    }
}

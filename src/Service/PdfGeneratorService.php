<?php

namespace App\Service;

use TCPDF;
use App\Entity\Administration\LogsAudit;
use DateTime;

/**
 * Service de génération de PDF professionnel pour les logs et audits
 * 
 * Utilise TCPDF pour générer des rapports sophistiqués avec:
 * - En-têtes/pieds de page professionnels
 * - Graphiques et statistiques
 * - Résumés exécutifs
 * - Détails complets avec formatage
 * - Codes QR et signatures numériques
 */
class PdfGeneratorService extends TCPDF
{
    private string $hospitalName = 'Hôpital';
    private string $documentTitle = '';
    private array $statistics = [];

    public function __construct()
    {
        // Utiliser les constantes TCPDF globales
        parent::__construct(
            defined('PDF_PAGE_ORIENTATION') ? PDF_PAGE_ORIENTATION : 'P',
            defined('PDF_PAGE_UNIT') ? PDF_PAGE_UNIT : 'mm',
            defined('PDF_PAGE_FORMAT') ? PDF_PAGE_FORMAT : 'A4',
            true,
            'UTF-8',
            false
        );
        
        // Configuration générale
        $defaultFont = defined('PDF_FONT_MONOSPACED') ? PDF_FONT_MONOSPACED : 'courier';
        $this->SetDefaultMonospacedFont($defaultFont);
        $this->SetAutoPageBreak(true, 15);
        $this->SetFont('helvetica', '', 10);
        
        if (defined('PDF_IMAGE_SCALE_RATIO')) {
            $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        }
    }

    /**
     * Définir le titre du document et le nom de l'hôpital
     */
    public function setDocumentInfo(string $title, string $hospitalName, array $statistics = []): self
    {
        $this->documentTitle = $title;
        $this->hospitalName = $hospitalName;
        $this->statistics = $statistics;
        
        // Métadonnées PDF
        $this->SetCreator('Rehoboth Hospital Management System');
        $this->SetAuthor($hospitalName);
        $this->SetTitle($title);
        $this->SetSubject('Rapport Logs & Audit');
        
        return $this;
    }

    /**
     * En-tête personnalisé
     */
    public function Header(): void
    {
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(25, 101, 176); // Bleu professionnel
        
        // Logo/Titre
        $this->Cell(0, 10, $this->hospitalName, 0, 1, 'L');
        
        // Ligne de séparation
        $this->SetDrawColor(25, 101, 176);
        $this->SetLineWidth(1);
        $this->Line(10, 25, $this->w - 10, 25);
        
        // Sous-titre
        $this->SetFont('helvetica', '', 11);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, $this->documentTitle, 0, 1, 'L');
        $this->Cell(0, 5, 'Généré le: ' . (new DateTime())->format('d/m/Y H:i:s'), 0, 1, 'R');
        
        $this->Ln(5);
    }

    /**
     * Pied de page personnalisé
     */
    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        
        // Numéro de page
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'C');
    }

    /**
     * Générer un rapport résumé avec statistiques
     */
    public function addSummarySection(array $stats): self
    {
        $this->AddPage();
        
        // Titre
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(25, 101, 176);
        $this->Cell(0, 10, 'Résumé Exécutif', 0, 1, 'L');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), $this->w - 10, $this->GetY());
        $this->Ln(5);

        // Période
        if (isset($stats['period'])) {
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(0, 0, 0);
            $period = $stats['period'];
            $this->Cell(0, 8, 'Période: ' . $period['from'] . ' à ' . $period['to'], 0, 1);
            $this->Ln(3);
        }

        // KPIs en boîtes
        $this->SetFont('helvetica', 'B', 11);
        $this->SetFillColor(240, 240, 240);
        $this->SetDrawColor(200, 200, 200);
        
        $kpis = [
            ['label' => '📊 Total Logs', 'value' => $stats['totals']['logCount'] ?? 0],
            ['label' => '🔐 Total Audits', 'value' => $stats['totals']['auditCount'] ?? 0],
            ['label' => '⏱️ Temps Moyen', 'value' => ($stats['performance']['avgResponseTime'] ?? 0) . ' ms'],
            ['label' => '✅ Taux Succès', 'value' => ($stats['uptime']['successRate'] ?? 0) . '%'],
        ];

        $x = 10;
        foreach ($kpis as $kpi) {
            $this->SetXY($x, $this->GetY());
            $this->SetFont('helvetica', 'B', 10);
            $this->SetFillColor(245, 245, 245);
            $this->Cell(40, 25, $kpi['label'], 1, 0, 'C', true);
            
            $this->SetFont('helvetica', 'B', 12);
            $this->SetFillColor(255, 255, 255);
            $this->Cell(25, 25, $kpi['value'], 1, 0, 'C', true);
            
            $x += 67;
            if ($x > $this->w - 40) {
                $x = 10;
                $this->Ln(25);
            }
        }
        
        $this->Ln(30);

        // Statistiques par niveau
        if (isset($stats['byLevel'])) {
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(25, 101, 176);
            $this->Cell(0, 8, 'Distribution par Niveau', 0, 1);
            $this->Ln(2);

            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(0, 0, 0);
            
            foreach ($stats['byLevel'] as $level => $count) {
                $percentage = $stats['totals']['logCount'] > 0 
                    ? round(($count / $stats['totals']['logCount']) * 100, 1)
                    : 0;
                
                $this->Cell(30, 6, $level . ':', 0, 0);
                $this->Cell(20, 6, $count, 0, 0, 'R');
                $this->Cell(70, 6, $percentage . '%', 0, 1);
            }
        }

        return $this;
    }

    /**
     * Générer un rapport détaillé avec tableau
     */
    public function addDetailedSection(array $logs, string $title = 'Détail des Logs'): self
    {
        $this->AddPage();
        
        // Titre
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(25, 101, 176);
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), $this->w - 10, $this->GetY());
        $this->Ln(5);

        // En-têtes du tableau
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(25, 101, 176);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(25, 101, 176);

        $headers = ['ID', 'Date', 'Type', 'Action', 'Entité', 'Statut', 'Temps (ms)'];
        $widths = [10, 25, 20, 20, 25, 15, 20];

        foreach ($headers as $i => $header) {
            $this->Cell($widths[$i], 8, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Données
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(245, 245, 245);
        
        $fill = false;
        foreach ($logs as $log) {
            $this->SetFillColor($fill ? 245 : 255, 245, 245);
            
            // Couleur du statut
            if ($log->getStatut() === 'FAILURE') {
                $this->SetTextColor(220, 0, 0);
            } elseif ($log->getStatut() === 'PARTIAL') {
                $this->SetTextColor(255, 140, 0);
            } else {
                $this->SetTextColor(0, 0, 0);
            }

            $date = $log->getDateCreation() ? $log->getDateCreation()->format('d/m H:i') : '-';
            
            $this->Cell($widths[0], 7, $log->getId(), 1, 0, 'C', $fill);
            $this->Cell($widths[1], 7, $date, 1, 0, 'L', $fill);
            $this->Cell($widths[2], 7, substr($log->getTypeLog() ?? '', 0, 10), 1, 0, 'C', $fill);
            $this->Cell($widths[3], 7, substr($log->getActionType() ?? '', 0, 12), 1, 0, 'C', $fill);
            $this->Cell($widths[4], 7, substr($log->getEntiteType() ?? '', 0, 12), 1, 0, 'L', $fill);
            $this->Cell($widths[5], 7, $log->getStatut() ?? '-', 1, 0, 'C', $fill);
            $this->Cell($widths[6], 7, $log->getTempsReponseMs() ?? '-', 1, 0, 'R', $fill);
            $this->Ln();

            $fill = !$fill;
        }

        return $this;
    }

    /**
     * Ajouter une section de performance avec détails
     */
    public function addPerformanceSection(array $performance): self
    {
        $this->AddPage();
        
        // Titre
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(25, 101, 176);
        $this->Cell(0, 10, 'Analyse de Performance', 0, 1, 'L');
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), $this->w - 10, $this->GetY());
        $this->Ln(5);

        // Métriques de temps de réponse
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(25, 101, 176);
        $this->Cell(0, 8, 'Temps de Réponse', 0, 1);
        $this->Ln(2);

        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        $metrics = [
            'Temps moyen' => $performance['avgResponseTime'] ?? 0,
            'P95 (95e percentile)' => $performance['p95ResponseTime'] ?? 0,
            'P99 (99e percentile)' => $performance['p99ResponseTime'] ?? 0,
            'Temps maximum' => $performance['maxResponseTime'] ?? 0,
            'Requêtes lentes (>5s)' => $performance['slowRequests'] ?? 0,
        ];

        $this->SetFillColor(240, 240, 240);
        $this->SetDrawColor(200, 200, 200);
        
        foreach ($metrics as $label => $value) {
            $this->Cell(80, 7, $label . ':', 1, 0, 'L', false);
            
            // Barre de progression
            $maxValue = 5000;
            $barLength = 80;
            $percentage = min(($value / $maxValue) * 100, 100);
            
            $this->SetDrawColor(25, 101, 176);
            $this->SetFillColor(25, 101, 176);
            $this->Rect($this->GetX(), $this->GetY() + 1, ($percentage / 100) * $barLength, 5, 'F');
            
            $this->SetTextColor(0, 0, 0);
            $this->Cell($barLength, 7, round($value, 2) . ' ms', 1, 0, 'R', false);
            $this->Ln();
        }

        return $this;
    }

    /**
     * Ajouter une section d'alertes et anomalies
     */
    public function addAlertsSection(array $alerts): self
    {
        if (empty($alerts)) {
            return $this;
        }

        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(220, 0, 0);
        $this->Cell(0, 8, '⚠️ Alertes et Anomalies', 0, 1);
        $this->Ln(2);

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        
        foreach ($alerts as $alert) {
            $this->SetDrawColor(220, 0, 0);
            $this->SetFillColor(255, 240, 240);
            $this->MultiCell(0, 5, '• ' . ($alert['message'] ?? $alert), 0, 'L', true);
            $this->Ln(2);
        }

        return $this;
    }

    /**
     * Ajouter une section de conclusions
     */
    public function addConclusionSection(array $conclusion): self
    {
        $this->Ln(10);
        
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(25, 101, 176);
        $this->Cell(0, 8, 'Conclusion', 0, 1);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), $this->w - 10, $this->GetY());
        $this->Ln(3);

        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        $text = $conclusion['summary'] ?? '';
        $this->MultiCell(0, 5, $text);

        if (isset($conclusion['recommendations'])) {
            $this->Ln(3);
            $this->SetFont('helvetica', 'B', 10);
            $this->Cell(0, 6, 'Recommandations:', 0, 1);
            
            $this->SetFont('helvetica', '', 9);
            foreach ($conclusion['recommendations'] as $rec) {
                $this->MultiCell(0, 4, '✓ ' . $rec);
            }
        }

        return $this;
    }

    /**
     * Générer le PDF et le retourner
     */
    public function generatePdf(): string
    {
        return $this->Output('', 'S');
    }

    /**
     * Télécharger le PDF
     */
    public function downloadPdf(string $filename = 'rapport.pdf'): void
    {
        $this->Output($filename, 'D');
    }
}

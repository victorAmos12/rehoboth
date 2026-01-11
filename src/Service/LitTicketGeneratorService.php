<?php

namespace App\Service;

use App\Entity\Patients\Lits;
use TCPDF;

/**
 * Service pour générer les tickets des lits en PDF
 * 
 * Génère des tickets à coller sur les lits avec les informations essentielles
 */
class LitTicketGeneratorService
{
    /**
     * Génère un ticket PDF pour un lit
     * 
     * @param Lits $lit Le lit pour lequel générer le ticket
     * @param bool $download Si true, retourne le PDF en téléchargement, sinon en affichage
     * @return string Le contenu PDF
     */
    public function generateTicket(Lits $lit, bool $download = false): string
    {
        // Créer une nouvelle instance TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A6', true, 'UTF-8', false);
        
        // Définir les marges (réduites pour un petit ticket)
        $pdf->SetMargins(5, 5, 5);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 14);
        
        // Titre du ticket
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'TICKET LIT', 0, 1, 'C');
        
        // Ligne de séparation
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(5, $pdf->GetY(), 105, $pdf->GetY());
        $pdf->Ln(2);
        
        // Numéro du lit (grand et visible)
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(0, 12, $lit->getNumeroLit(), 0, 1, 'C');
        
        // Ligne de séparation
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(5, $pdf->GetY(), 105, $pdf->GetY());
        $pdf->Ln(2);
        
        // Informations du lit
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        
        // Type de lit
        if ($lit->getTypeLit()) {
            $pdf->Cell(30, 5, 'Type:', 0, 0);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 5, $lit->getTypeLit(), 0, 1);
            $pdf->SetFont('helvetica', '', 9);
        }
        
        // Chambre
        $pdf->Cell(30, 5, 'Chambre:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, $lit->getChambreId()->getNumeroChambre(), 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        // Étage
        if ($lit->getEtage()) {
            $pdf->Cell(30, 5, 'Étage:', 0, 0);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 5, (string)$lit->getEtage(), 0, 1);
            $pdf->SetFont('helvetica', '', 9);
        }
        
        // Service
        $pdf->Cell(30, 5, 'Service:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, $lit->getServiceId()->getNom(), 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        // Hôpital
        $pdf->Cell(30, 5, 'Hôpital:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, $lit->getHopitalId()->getNom(), 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        // Ligne de séparation
        $pdf->Ln(2);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(5, $pdf->GetY(), 105, $pdf->GetY());
        $pdf->Ln(2);
        
        // Statut avec couleur
        $pdf->SetFont('helvetica', 'B', 10);
        $statusColor = $this->getStatusColor($lit->getStatut());
        $pdf->SetTextColor($statusColor['r'], $statusColor['g'], $statusColor['b']);
        $pdf->Cell(0, 6, 'Statut: ' . ucfirst($lit->getStatut()), 0, 1, 'C');
        
        // Code QR (optionnel - ID du lit)
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Ln(2);
        $pdf->Cell(0, 4, 'ID: ' . $lit->getId(), 0, 1, 'C');
        
        // Date de création
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(0, 4, 'Créé: ' . $lit->getDateCreation()->format('d/m/Y'), 0, 1, 'C');
        
        // Retourner le PDF
        if ($download) {
            return $pdf->Output('ticket_lit_' . $lit->getNumeroLit() . '.pdf', 'S');
        } else {
            return $pdf->Output('ticket_lit_' . $lit->getNumeroLit() . '.pdf', 'S');
        }
    }

    /**
     * Génère plusieurs tickets PDF pour plusieurs lits
     * 
     * @param array $lits Tableau des lits
     * @return string Le contenu PDF avec tous les tickets
     */
    public function generateMultipleTickets(array $lits): string
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        
        foreach ($lits as $index => $lit) {
            // Ajouter une page pour chaque lit
            $pdf->AddPage();
            
            // Titre
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 10, 'TICKET LIT', 0, 1, 'C');
            
            // Ligne de séparation
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);
            
            // Numéro du lit (grand)
            $pdf->SetFont('helvetica', 'B', 32);
            $pdf->SetTextColor(200, 0, 0);
            $pdf->Cell(0, 20, $lit->getNumeroLit(), 0, 1, 'C');
            
            // Ligne de séparation
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);
            
            // Informations
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            
            if ($lit->getTypeLit()) {
                $pdf->Cell(50, 6, 'Type:', 0, 0);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 6, $lit->getTypeLit(), 0, 1);
                $pdf->SetFont('helvetica', '', 12);
            }
            
            $pdf->Cell(50, 6, 'Chambre:', 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, $lit->getChambreId()->getNumeroChambre(), 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            
            if ($lit->getEtage()) {
                $pdf->Cell(50, 6, 'Étage:', 0, 0);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 6, (string)$lit->getEtage(), 0, 1);
                $pdf->SetFont('helvetica', '', 12);
            }
            
            $pdf->Cell(50, 6, 'Service:', 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, $lit->getServiceId()->getNom(), 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            
            $pdf->Cell(50, 6, 'Hôpital:', 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, $lit->getHopitalId()->getNom(), 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            
            // Ligne de séparation
            $pdf->Ln(5);
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);
            
            // Statut
            $pdf->SetFont('helvetica', 'B', 14);
            $statusColor = $this->getStatusColor($lit->getStatut());
            $pdf->SetTextColor($statusColor['r'], $statusColor['g'], $statusColor['b']);
            $pdf->Cell(0, 8, 'Statut: ' . ucfirst($lit->getStatut()), 0, 1, 'C');
            
            // Informations supplémentaires
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln(5);
            $pdf->Cell(0, 5, 'ID: ' . $lit->getId() . ' | Créé: ' . $lit->getDateCreation()->format('d/m/Y'), 0, 1, 'C');
        }
        
        return $pdf->Output('tickets_lits.pdf', 'S');
    }

    /**
     * Obtient la couleur en fonction du statut
     * 
     * @param string|null $statut Le statut du lit
     * @return array Tableau avec les clés 'r', 'g', 'b'
     */
    private function getStatusColor(?string $statut): array
    {
        return match($statut) {
            'disponible' => ['r' => 0, 'g' => 128, 'b' => 0],      // Vert
            'occupé' => ['r' => 255, 'g' => 165, 'b' => 0],        // Orange
            'maintenance' => ['r' => 255, 'g' => 0, 'b' => 0],     // Rouge
            'réservé' => ['r' => 0, 'g' => 0, 'b' => 255],         // Bleu
            'hors_service' => ['r' => 128, 'g' => 128, 'b' => 128], // Gris
            default => ['r' => 0, 'g' => 0, 'b' => 0],             // Noir
        };
    }
}

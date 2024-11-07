<?php
require('../vendors/Fpdf/fpdf.php');

class PDF extends FPDF {
    // En-tête
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Contrat d\'adhesion - Privilege Luxury Fitness Club', 0, 1, 'C');
        $this->Ln(10);
    }

    // Pied de page
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
    }

    // Section principale du contrat
    function Section($title, $content) {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, $title, 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->MultiCell(0, 8, $content);
        $this->Ln(5);
    }
}

// Créer une instance de PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Ajouter les informations de l'adhérent
$nom = "Nom de l'adhérent";
$prenom = "Prénom";
$dateNaissance = "Date de naissance";
$adresse = "Adresse";
$ville = "Ville";
$cin = "CIN";
$telPortable = "Téléphone portable";
$email = "Email";
$urgence = "Personne à contacter en cas d'urgence";

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Nom: $nom", 0, 1);
$pdf->Cell(0, 10, "Prénom: $prenom", 0, 1);
$pdf->Cell(0, 10, "Né le: $dateNaissance", 0, 1);
$pdf->Cell(0, 10, "Adresse: $adresse, $ville", 0, 1);
$pdf->Cell(0, 10, "CIN: $cin", 0, 1);
$pdf->Cell(0, 10, "Téléphone portable: $telPortable", 0, 1);
$pdf->Cell(0, 10, "Email: $email", 0, 1);
$pdf->Cell(0, 10, "Personne à contacter en cas d'urgence: $urgence", 0, 1);
$pdf->Ln(10);

// Ajouter des sections du contrat
$pdf->Section("I - Objet du Contrat", "Le present contrat d'abonnement a pour objet...");
$pdf->Section("II - Droits d'inscription", "L'adhérent s'acquitte par cheque, espèce, ou carte...");
$pdf->Section("III - Choix du Type d'Abonnement", "Options disponibles: Individuel, Famille, Groupe...");
$pdf->Section("IV - Modalités de Règlement", "Le montant total est de X DH TTC...");

// Signature et validation
$pdf->Ln(20);
$pdf->Cell(0, 10, "Fait en double exemplaire a Casablanca le " . date("d/m/Y"), 0, 1);
$pdf->Ln(10);
$pdf->Cell(0, 10, "Signature de l'adhérent precedée de la mention 'lu et approuvé' :", 0, 1);

// Générer le PDF
$pdf->Output('D', 'Contrat_Privilege_Club.pdf');
?>

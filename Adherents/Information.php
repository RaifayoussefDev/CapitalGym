<?php
require('../vendors/Fpdf/fpdf.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user ID from URL
if (isset($_GET['id_user'])) {
    $user_id = intval($_GET['id_user']);

    // Retrieve user details
    $sql = "
    SELECT 
        u.id,
        u.matricule,
        u.nom,
        u.prenom,
        u.email,
        u.phone,
        u.cin,
        u.date_naissance,
        u.genre,
        u.photo,
        a.type AS abonnement_type,
        a.id AS abonnement_id,
        GROUP_CONCAT(DISTINCT ua.activite_id ORDER BY ua.activite_id ASC SEPARATOR ',') AS activites_ids,
        GROUP_CONCAT(DISTINCT act.nom ORDER BY act.nom ASC SEPARATOR ', ') AS activites,
        COALESCE(a.date_fin, '') AS date_fin_abn,
        COALESCE(SUM(p.montant_paye), 0) AS montant_paye,
        tp.type AS type_paiement,
        COALESCE(SUM(p.reste), 0) AS reste
    FROM 
        users u
    JOIN 
        abonnements a ON u.id = a.user_id
    LEFT JOIN 
        user_activites ua ON a.id = ua.abonnement_id
    LEFT JOIN 
        activites act ON ua.activite_id = act.id
    LEFT JOIN 
        payments p ON a.id = p.abonnement_id
    LEFT JOIN 
        type_paiements tp ON p.type_paiement_id = tp.id
    WHERE 
        u.id = ?
    GROUP BY 
        u.id, a.type, a.date_fin, tp.type;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Retrieve payments for the user
    $payments_sql = "
    SELECT 
        p.id, 
        p.montant_paye, 
        p.avance, 
        tp.type AS type_paiement, 
        p.reste, 
        p.total 
    FROM 
        payments p
    JOIN 
        type_paiements tp ON p.type_paiement_id = tp.id
    WHERE 
        p.user_id = ? AND p.abonnement_id = ?
    ";

    $stmt = $conn->prepare($payments_sql);
    $stmt->bind_param('ii', $user_id, $user['abonnement_id']);
    $stmt->execute();
    $payments_result = $stmt->get_result();

} else {
    die("Aucun utilisateur sélectionné.");
}

$conn->close();

class PDF extends FPDF {
    // Page header
    function Header() {
        $this->Image('../assets/img/capitalsoft/logo_light.png',10,8,33);
        $this->SetFont('Arial','B',20);
        $this->Cell(80);
        $this->Cell(50,10,utf8_decode('Contrat d\'Adhésion'),0,1,'C');
        $this->Ln(20);
    }

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page ' . $this->PageNo() . '/{nb}',0,0,'C');
    }

    // Section title
    function SectionTitle($label) {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(200,220,255);
        $this->Cell(0,10,utf8_decode($label),0,1,'L',true);
        $this->Ln(4);
    }

    // Content with labels
    function ContentLabel($label, $value) {
        $this->SetFont('Arial','',12);
        $this->Cell(50,10,utf8_decode($label . ':'),0,0);
        $this->Cell(0,10,utf8_decode($value),0,1);
    }
}

// Instantiation of FPDF class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);

// Contract title
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode('Contrat d\'Adhésion au Club'),0,1,'C');
$pdf->Ln(10);

// User Information Section
$pdf->SectionTitle('Informations Personnelles');
$pdf->ContentLabel('CIN', $user['cin']);
$pdf->ContentLabel('Matricule', $user['matricule']);
$pdf->ContentLabel('Nom', $user['nom']);
$pdf->ContentLabel('Prénom', $user['prenom']);
$pdf->ContentLabel('Email', $user['email']);
$pdf->ContentLabel('Téléphone', $user['phone']);
$pdf->ContentLabel('Date de naissance', $user['date_naissance']);
$pdf->ContentLabel('Genre', ($user['genre'] === 'M' ? 'Mâle' : 'Femelle'));

$pdf->Ln(10);

// Subscription Information Section
$pdf->SectionTitle('Informations d\'Abonnement');
$pdf->ContentLabel('Type d\'abonnement', $user['abonnement_type']);
$pdf->ContentLabel('Activités', $user['activites']);
$pdf->ContentLabel('Date de fin d\'abonnement   ', $user['date_fin_abn']);

$pdf->Ln(10);

$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
$pdf->Ln(10);
// Display the payment modes in a table
$pdf->SectionTitle('Modes de Paiement Utilisés');

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, utf8_decode('Montant Payé'), 1);
$pdf->Cell(40, 10, utf8_decode('Type de Paiement'), 1);
$pdf->Cell(40, 10, utf8_decode('Reste à Payer'), 1);
$pdf->Cell(40, 10, utf8_decode('Total'), 1);
$pdf->Ln();


// Add table rows
$pdf->SetFont('Arial', '', 12);
while ($payment = $payments_result->fetch_assoc()) {
    $pdf->Cell(40, 10, $payment['montant_paye'], 1);
    $pdf->Cell(40, 10, utf8_decode($payment['type_paiement']), 1);
    $pdf->Cell(40, 10, $payment['reste'], 1);
    $pdf->Cell(40, 10, $payment['total'], 1);
    $pdf->Ln();
}

// Contract Footer with Regulation Text
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, utf8_decode("En signant ce contrat, l'adhérent s'engage à respecter les règles et règlements du club. Ce contrat est valide jusqu'à la date de fin d'abonnement mentionnée ci-dessus. Pour toute question, veuillez contacter notre support client."), 0, 'L');

$pdf->Ln(20);

// Add Regulation Text
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Règlement du Club'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, utf8_decode("Veuillez lire attentivement le règlement ci-dessous avant de signer ce contrat. En signant, vous acceptez de respecter toutes les règles et politiques du club."));

$pdf->Ln(20);

// Signature Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Signatures'), 0, 1, 'L');

$pdf->Ln(10);
// Signature lines
$pdf->SetFont('Arial', '', 12);

// Signature title for the society
$pdf->Cell(60, 10, utf8_decode('SIGNATURE'), 0, 0, 'L');

// Signature title for the client aligned to the right
$pdf->Cell(00, 10, utf8_decode('SIGNATURE CLIENT'), 0, 1, 'R');

// Add lines for signatures
$pdf->Ln(10);
$pdf->Cell(60, 0, '', 'B'); // Society's signature line
$pdf->Cell(60); // Move to the right
$pdf->Cell(60, 0, '', 'B'); // Client's signature line

// Additional space before the end of the document
$pdf->Ln(30);


// Output the PDF
$pdf->Output();
?>

<?php
require('../vendors/Fpdf/fpdf.php');

// Set up the PDF dimensions (width: 86mm, height: 54mm)
$pdf = new FPDF('L', 'mm', array(86, 54)); // 'L' for landscape orientation

require "../inc/conn_db.php";

// Retrieve user data after insertion
$user_id = $_GET['user_id']; // Ensure to pass the user ID in the URL
$sql = "SELECT nom, prenom, matricule, photo FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Add a page
$pdf->AddPage();

// Define the margins
$pdf->SetMargins(5, 5, 5); // Set left, top, right margins

// Add a rectangle for the badge background
$pdf->SetFillColor(255, 255, 255); // White background
$pdf->Rect(0, 0, 86, 54, 'F'); // A rectangle covering the badge area

// Set font for the name and surname
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(5, 10); // Set position for name and surname
$pdf->Cell(40, 8,  $user['nom']. ' ' . $user['prenom'], 0, 1);

// Set position for the photo (top right)
$imageX = 55; // X position for the image
$imageY = 5;  // Y position for the image
$imageWidth = 25; // Width for the image
$imageHeight = 25; // Height for the image

// Set font for the matricule above the image
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY($imageX, $imageY); // Position above the image
$pdf->Cell(0, 8, 'Matricule: ' . $user['matricule'], 0, 1);

// Add the photo to the right (below matricule)
$imageY += 8; // Move image position down to make space for matricule
if (!empty($user['photo'])) {
    $pdf->Image('../assets/img/capitalsoft/profils/' . $user['photo'], $imageX, $imageY, $imageWidth, $imageHeight); // Adjust the coordinates and size as needed
}

// Set display mode
$pdf->SetDisplayMode('fullpage');

// Output the PDF (Inline mode to open in browser)
$pdf->Output('I', 'user_card.pdf'); // I for inline display
exit();
?>

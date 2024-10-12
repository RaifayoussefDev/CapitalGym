<?php
ob_start();
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'vendor/autoload.php';

$mail = new PHPMailer(); // create a new object
$mail->IsSMTP(); // enable SMTP
$mail->Host = '10.2.5.16'; // Adresse du serveur SMTP
$mail->SMTPAuth = false; // Désactiver l'authentification
$mail->SMTPSecure = false; // Désactiver le chiffrement TLS
$mail->Port = 25; // Utiliser le port SMTP par défaut (25)
$mail->IsHTML(true);
// Expéditeur et destinataire
$mail->setFrom('Registredetraitement@cpm.co.ma', 'Registre de traitement');
?>
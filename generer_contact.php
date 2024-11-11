<?php
// Inclure le fichier de connexion
include './inc/conn_db.php';

// Initialiser le contenu du fichier VCF
$vcfContent = "";

// Requête pour récupérer les utilisateurs avec leur numéro de téléphone
$query = $conn->query("SELECT nom, prenom, phone FROM users WHERE role_id = 3 and etat='actif'");

foreach ($query as $user) {
    $nomComplet = $user['prenom'] . ' ' . $user['nom'];
    $numero = $user['phone'];

    // Ajouter les informations de contact au format vCard
    $vcfContent .= "BEGIN:VCARD\n";
    $vcfContent .= "VERSION:3.0\n";
    $vcfContent .= "FN:$nomComplet\n";
    $vcfContent .= "TEL;TYPE=CELL:$numero\n";
    $vcfContent .= "END:VCARD\n";
}

// Enregistrer le contenu dans un fichier .vcf
$fileName = "contacts.vcf";
file_put_contents($fileName, $vcfContent);

echo "Fichier $fileName généré avec succès. Téléchargez-le et importez-le sur votre téléphone.";
?>

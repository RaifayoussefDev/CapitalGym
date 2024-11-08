<?php
require "../../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $description = $conn->real_escape_string($_POST['description']);  // Ajout de la description

    // Utilisation d'une requête préparée pour éviter les injections SQL
    $sql = "INSERT INTO activites (nom, prix, sex, description) VALUES (?, ?, ?, ?)";

    // Préparation de la requête
    if ($stmt = $conn->prepare($sql)) {
        // Lier les paramètres
        $stmt->bind_param("sdsd", $nom, $prix, $sex, $description);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo "<script>window.location.href = '../index.php?msg=success';</script>";
        } else {
            echo "<script>window.location.href = '../index.php?msg=error';</script>";
        }

        // Fermer la requête préparée
        $stmt->close();
    } else {
        echo "<script>window.location.href = '../index.php?msg=error';</script>";
    }
}

$conn->close();
?>

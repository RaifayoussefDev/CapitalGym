<?php
require "../inc/conn_db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $statut_actif = $_POST['statut_actif'];

    // Update the statut_actif in the database
    $stmt = $conn->prepare("UPDATE habilitation SET statut_actif = ? WHERE id_habilitation = ?");
    $stmt->bind_param("si", $statut_actif, $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}

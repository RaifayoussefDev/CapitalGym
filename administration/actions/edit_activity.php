<?php
require "../../inc/conn_db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $description = $conn->real_escape_string($_POST['description']); // Ajout de la description

    // Mise à jour de la requête SQL pour inclure la description
    $sql = "UPDATE activites SET nom='$nom', prix=$prix, sex='$sex', description='$description' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href = '../index.php?msg=edit_success';</script>";
    } else {
        echo "<script>window.location.href = '../index.php?msg=edit_error';</script>";
    }
}

$conn->close();
?>

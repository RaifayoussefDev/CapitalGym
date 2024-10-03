<?php
require "../../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $sex = $conn->real_escape_string($_POST['sex']);

    $sql = "INSERT INTO activites (nom, prix, sex) VALUES ('$nom', $prix, '$sex')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href = '../index.php?msg=success';</script>";
    } else {
        echo "<script>window.location.href = '../index.php?msg=error';</script>";
    }
}

$conn->close();
?>

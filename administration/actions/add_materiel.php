<?php
require "../../inc/conn_db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $emplacement = $_POST['emplacement'];
    $date_achat = $_POST['date_achat'];

    $sql = "INSERT INTO materiel (nom, emplacement, date_achat) VALUES ('$nom', '$emplacement', '$date_achat')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../materiels.php?msg=success");
    } else {
        header("Location: ../materiels.php?msg=error");
    }
}

$conn->close();
?>

<?php
require "../../inc/conn_db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $emplacement = $_POST['emplacement'];
    $date_achat = $_POST['date_achat'];

    $sql = "UPDATE materiel SET nom='$nom', emplacement='$emplacement', date_achat='$date_achat' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../materiels.php?msg=success");
    } else {
        header("Location: ../materiels.php?msg=error");
    }
}

$conn->close();
?>

<?php
require "../../inc/conn_db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $sex = $conn->real_escape_string($_POST['sex']);

    $sql = "UPDATE activites SET nom='$nom', prix=$prix, sex='$sex' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href = '../index.php?msg=edit_success';</script>";
    } else {
        echo "<script>window.location.href = '../index.php?msg=edit_error';</script>";
    }
}

$conn->close();
?>

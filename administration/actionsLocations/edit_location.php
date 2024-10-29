<?php
require "../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id']; // Convert to integer
    $name = $conn->real_escape_string($_POST['name']);
    $nomber_place = (int)$_POST['nomber_place']; // Convert to integer

    $sql = "UPDATE locations SET name = '$name', nomber_place = $nomber_place WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../local.php?msg=success");
    } else {
        header("Location: ../local.php?msg=error");
    }
}

$conn->close();
?>

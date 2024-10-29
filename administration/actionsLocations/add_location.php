<?php
require "../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $nomber_place = (int)$_POST['nomber_place']; // Convert to integer

    $sql = "INSERT INTO locations (name, nomber_place) VALUES ('$name', $nomber_place)";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../local.php?msg=success");
    } else {
        header("Location: ../local.php?msg=error");
    }
}

$conn->close();
?>

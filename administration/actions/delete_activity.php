<?php

require "../../inc/conn_db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);

    $sql = "DELETE FROM activites WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href = '../index.php?msg=success';</script>";
    } else {
        echo "<script>window.location.href = '../index.php?msg=error';</script>";
    }
}

$conn->close();
?>

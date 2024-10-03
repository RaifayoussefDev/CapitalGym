<?php
require "../../inc/conn_db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $sql = "DELETE FROM materiel WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../materiels.php?msg=success");
    } else {
        header("Location: ../materiels.php?msg=error");
    }
}

$conn->close();
?>

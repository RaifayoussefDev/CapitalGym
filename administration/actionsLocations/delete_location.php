<?php
require "../../inc/conn_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id']; // Convert to integer

    $sql = "DELETE FROM locations WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../local.php?msg=success");
    } else {
        header("Location: ../local.php?msg=error");
    }
}

$conn->close();
?>

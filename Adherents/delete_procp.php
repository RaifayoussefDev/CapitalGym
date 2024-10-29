<?php
// Include database connection
require "../inc/conn_db.php";

if (isset($_GET['id_user'])) {
    $id_user = htmlspecialchars($_GET['id_user']);

    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Prepare and execute delete statement
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) {
        header("Location: ./");
        exit();
    } else {
        header("Location: ./");
        exit();
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");

    $stmt->close();
} else {
    header("Location: ./");
    exit();
}

$conn->close();

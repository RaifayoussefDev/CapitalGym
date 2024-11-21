<?php
require "../inc/app.php";

require "../inc/conn_db.php";

// Validate session ID
if (!isset($_POST['sessionId']) || empty($_POST['sessionId'])) {
    die("Invalid session ID");
}

$session_id = $_POST['sessionId'];

// Delete the session from the database
$sql = "DELETE FROM sessions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);

// Delete the session from the database
$sql = "DELETE FROM `session_planning` WHERE session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);

if ($stmt->execute()) {
    // Success message
    echo "<script>window.location.href = 'index.php?date=" . date('Y-m-d') . "&msg=success&action=delete';</script>";
} else {
    // Error message
    echo "<script>window.location.href = 'index.php?date=" . date('Y-m-d') . "&msg=error&action=delete';</script>";
}


$stmt->close();
$conn->close();
?>

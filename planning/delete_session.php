<?php
require "../inc/app.php";

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate session ID
if (!isset($_POST['sessionId']) || empty($_POST['sessionId'])) {
    die("Invalid session ID");
}

$session_id = $_POST['sessionId'];

// Delete the session from the database
$sql = "DELETE FROM sessions WHERE id = ?";
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

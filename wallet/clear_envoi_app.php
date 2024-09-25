<?php

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your actual password
$dbname = "privilage";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear the envoi_app table
$clear_sql = "DELETE FROM envoi_app";

if ($conn->query($clear_sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'envoi_app table cleared.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear envoi_app table.']);
}

$conn->close();
?>

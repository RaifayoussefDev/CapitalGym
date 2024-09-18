<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT MAX(id) AS latest_id FROM users";
$result = $conn->query($query);
$row = $result->fetch_assoc();

echo json_encode(['latest_id' => $row['latest_id']]);
;?>
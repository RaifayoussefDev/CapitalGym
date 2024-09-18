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

$field = $_GET['field'];
$value = $_GET['value'];

$query = "SELECT COUNT(*) AS count FROM users WHERE $field = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $value);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();

echo json_encode(['exists' => $count > 0]);
?>

<?php
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

// Fetch users
$users_sql = "SELECT id, CONCAT(nom, ' ', prenom) AS name FROM users where role_id=3";
$users_result = $conn->query($users_sql);

$users = [];
if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($users);
?>

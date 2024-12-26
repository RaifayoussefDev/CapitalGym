<?php
require "../inc/conn_db.php";

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

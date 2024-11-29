<?php

require "../inc/conn_db.php";

$session_id = $_GET['session_id'];

// Fetch reserved users
$sql = "SELECT nom, prenom, email, users.id, matricule 
        FROM reservations 
        JOIN users ON reservations.user_id = users.id 
        WHERE session_planning_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode($users);
?>

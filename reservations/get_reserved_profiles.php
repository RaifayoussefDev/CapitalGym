<?php
require "../inc/conn_db.php";

// Get session ID from query string
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

// Fetch reserved profiles
$profiles_sql = "
    SELECT 
        u.nom, 
        u.prenom, 
        u.email, 
        u.matricule 
    FROM 
        reservations r 
    JOIN 
        users u ON r.user_id = u.id 
    WHERE 
        r.session_id = $session_id";

$profiles_result = $conn->query($profiles_sql);

$profiles = [];
if ($profiles_result->num_rows > 0) {
    while ($row = $profiles_result->fetch_assoc()) {
        $profiles[] = $row;
    }
}

$conn->close();

echo json_encode($profiles);
?>

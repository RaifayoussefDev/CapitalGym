<?php
require "../inc/conn_db.php";

// Get session ID from query string
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

// Fetch reserved profiles with "confirmed" status
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
        r.session_id = ? 
        AND r.etat_reservation = 'confirmé'";  // Filter by 'confirmé' status

$stmt = $conn->prepare($profiles_sql);
$stmt->bind_param("i", $session_id); // Bind the session_id parameter

$stmt->execute();
$profiles_result = $stmt->get_result();

$profiles = [];
if ($profiles_result->num_rows > 0) {
    while ($row = $profiles_result->fetch_assoc()) {
        $profiles[] = $row;
    }
}

$stmt->close();
$conn->close();

// Return the result as JSON
echo json_encode($profiles);
?>

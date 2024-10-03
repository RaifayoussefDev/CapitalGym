<?php
require "../inc/app.php";

require "../inc/conn_db.php";

$id = $_GET['id'];

// Fetch the session details
$sql = "SELECT 
            s.id, 
            s.activite_id,
            s.coach_id,
            s.location_id,
            s.date,
            s.start_time,
            s.end_time,
            s.max_attendees,
            a.nom AS activity_name,
            c.coach_name,
            l.name AS location
        FROM 
            sessions s 
        JOIN 
            activites a ON s.activite_id = a.id 
        JOIN 
            coaches c ON s.coach_id = c.id
        JOIN 
            locations l ON s.location_id = l.id
        WHERE 
            s.id = '$id'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $session = $result->fetch_assoc();
    echo json_encode($session);
} else {
    echo json_encode(['error' => 'Session not found']);
}

$conn->close();
?>

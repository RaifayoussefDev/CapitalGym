<?php
// PHP File: reservations.php
require "../inc/conn_db.php";

// Retrieve dates from the AJAX request (POST method)
$date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
$date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;

// Base SQL query
$users_sql = "SELECT 
    r.date_reservation, 
    u.matricule, 
    u.nom, 
    u.prenom,
    l.name AS local, 
    a.nom AS activite_nom,
    sp.day,
    sp.start_time,
    p.pack_name AS package_name
FROM 
    reservations r
JOIN 
    users u ON u.id = r.user_id
JOIN 
    abonnements ab ON ab.user_id = u.id
JOIN 
    packages p ON p.id = ab.type_abonnement
JOIN 
    session_planning sp ON sp.id = r.session_planning_id
JOIN 
    sessions s ON s.id = sp.session_id
JOIN 
    locations l ON l.id = s.location_id
JOIN 
    activites a ON a.id = s.activite_id";

// Add date filter if both dates are provided
if (!empty($date_debut) && !empty($date_fin)) {
    $users_sql .= " WHERE r.date_reservation BETWEEN '$date_debut' AND '$date_fin'";
}

// $users_sql .= "Order BY r.date_reservation DESC";

// Execute query
$users_result = $conn->query($users_sql);

// Process results
$users = [];
if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($users);

$conn->close();

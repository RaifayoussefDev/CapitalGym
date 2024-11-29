<?php
session_start();

if (isset($_GET['id'])) {
    $session_id = $_GET['id'];
    $session_idsp = $_GET['id_sp'];

    require "../inc/conn_db.php";

    $sql = "
 SELECT 
    sp.id As id_sp,
    s.id, 
    a.nom AS activity_name, 
    l.name AS location, 
    CONCAT(u.nom, ' ', u.prenom) AS coach_name, 
    sp.max_attendees,
    sp.remaining_slots,
    sp.day,
    CONCAT(sp.start_time, ' - ', sp.end_time) AS time_range
FROM 
    sessions s 
JOIN 
    coaches c ON s.coach_id = c.id 
JOIN 
    activites a ON s.activite_id = a.id 
JOIN 
    locations l ON s.location_id = l.id 
JOIN 
    users u ON c.user_id = u.id 
JOIN 
    session_planning sp ON sp.session_id = s.id 
WHERE 
    s.id = ? AND sp.id=?;
";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $session_id,$session_idsp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();

        // Check if the user has reserved this session
        if (isset($_SESSION['id'])) {
            $user_id = intval($_SESSION['id']);
            $reservation_check_sql = "SELECT * FROM reservations WHERE user_id = ? AND session_planning_id = ?";
            $stmt_check = $conn->prepare($reservation_check_sql);
            $stmt_check->bind_param("ii", $user_id, $session_idsp);
            $stmt_check->execute();
            $reservation_result = $stmt_check->get_result();

            $session['is_reserved'] = $reservation_result->num_rows > 0;
        } else {
            $session['is_reserved'] = false;
        }

        echo json_encode($session);
    } else {
        echo json_encode([]);
    }

    $stmt->close();
    $conn->close();
}

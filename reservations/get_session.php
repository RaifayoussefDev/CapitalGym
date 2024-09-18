<?php
session_start();

if (isset($_GET['id'])) {
    $session_id = $_GET['id'];
    $session_idsp = $_GET['id_sp'];

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

    $sql = "
 SELECT 
    sp.id As id_sp,
    s.id, 
    a.nom AS activity_name, 
    l.name AS location, 
    CONCAT(u.nom, ' ', u.prenom) AS coach_name, 
    sp.max_attendees,
    sp.remaining_slots
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

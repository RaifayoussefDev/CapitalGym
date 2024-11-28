<?php
require "../inc/conn_db.php";

if (isset($_GET['id'])) {
    $sessionId = intval($_GET['id']);
    
    // Fetch session details
    $sessionSql = "SELECT * FROM sessions WHERE id = ?";
    if ($stmt = $conn->prepare($sessionSql)) {
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();

        // Fetch session planning data
        $planningSql = "SELECT * FROM session_planning WHERE session_id = ?";
        $planningStmt = $conn->prepare($planningSql);
        $planningStmt->bind_param("i", $sessionId);
        $planningStmt->execute();
        $planningResult = $planningStmt->get_result();
        
        $days = [];
        $times = [];
        $maxAttendees = null; // To store max attendees, we will fetch it only once
        
        while ($planningRow = $planningResult->fetch_assoc()) {
            $days[] = $planningRow['day'];
            $times[$planningRow['day']] = $planningRow['start_time']; // Assuming only start time is needed
            
            // Get the max_attendees from the first row (assuming all planning rows have the same max_attendees)
            if ($maxAttendees === null) {
                $maxAttendees = $planningRow['max_attendees']; // Store it only once
            }
        }

        // Combine data and send as JSON
        $session['days'] = $days;
        $session['times'] = $times;
        $session['max_attendees'] = $maxAttendees; // Add max attendees to the session response
        echo json_encode($session);
    }
}
?>

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
        while ($planningRow = $planningResult->fetch_assoc()) {
            $days[] = $planningRow['day'];
            $times[$planningRow['day']] = $planningRow['start_time']; // Assuming only start time is needed
        }

        // Combine data and send as JSON
        $session['days'] = $days;
        $session['times'] = $times;
        echo json_encode($session);
    }
}
?>

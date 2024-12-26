<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $sessionId = $_POST['session_id'];

    // Connexion à la base de données
    include '../inc/conn_db.php';

    try {
        // Update the reservation status to 'annulé' instead of deleting
        $stmt = $conn->prepare("UPDATE reservations SET etat_reservation = 'annulé' WHERE user_id = ? AND session_planning_id = ?");
        $stmt->bind_param("ii", $userId, $sessionId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Reservation canceled successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to cancel the reservation.']);
        }

        // Update remaining_slots in sessions
        $stmt_update = $conn->prepare("UPDATE `session_planning` SET `remaining_slots`= remaining_slots+1 WHERE id = ?");
        $stmt_update->bind_param("i", $session_id);

        if (!$stmt_update->execute()) {
            die("error: Execute failed - " . htmlspecialchars($stmt_update->error));
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}

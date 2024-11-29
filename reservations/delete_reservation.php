<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $sessionId = $_POST['session_id'];

    // Connexion à la base de données
    include '../inc/conn_db.php';

    try {
        // Mise à jour de l'état de la réservation à 'annuler'
        $stmt = $conn->prepare("UPDATE reservations SET etat_reservation = 'annuler' WHERE user_id = ? AND session_planning_id = ?");
        $stmt->bind_param("ii", $userId, $sessionId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible de mettre à jour l\'état de la réservation.']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>

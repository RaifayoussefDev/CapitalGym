<?php
require "../inc/conn_db.php";

$cardId = $_POST['cardId']; // Fetch card value sent via POST

// Fetch card data from envoi_app table
$card_sql = "SELECT id, valeur FROM envoi_app WHERE valeur = ?";
$stmt = $conn->prepare($card_sql);
$stmt->bind_param('s', $cardId);
$stmt->execute();
$card_result = $stmt->get_result();
if ($card_result->num_rows > 0) {
    $card_data = $card_result->fetch_assoc();

    // Fetch user data based on card ID
    $user_sql = "SELECT id, cin, matricule, nom, prenom FROM users WHERE id_card = ?";
    $stmt2 = $conn->prepare($user_sql);
    $stmt2->bind_param('s', $card_data['valeur']);
    $stmt2->execute();
    $user_result = $stmt2->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();

        // Send user data as JSON response
        echo json_encode(['status' => 'success', 'user' => $user_data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Card not found']);
}

$conn->close();
?>

<?php
require "../inc/conn_db.php";
// Récupérer la valeur de la carte ou vérifiez simplement si une entrée existe
$sql = "SELECT ip, valeur FROM envoi_app LIMIT 1"; // Check for the latest card data
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // If a card is found, retrieve the user associated with this card
    $card = $result->fetch_assoc();
    $user_sql = "SELECT id, cin, matricule, nom, prenom FROM users WHERE id_card = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("s", $card['valeur']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();

        // Fetch wallet balance for the user
        $wallet_sql = "SELECT balance FROM wallet WHERE user_id = ?";
        $wallet_stmt = $conn->prepare($wallet_sql);
        $wallet_stmt->bind_param("i", $user['id']);
        $wallet_stmt->execute();
        $wallet_result = $wallet_stmt->get_result();

        if ($wallet_result->num_rows > 0) {
            $wallet = $wallet_result->fetch_assoc();
            $user['balance'] = $wallet['balance'];
        } else {
            $user['balance'] = 0;
        }

        // Return the user details
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } else {
        // If no user is found
        echo json_encode([
            'success' => false,
            'message' => "Utilisateur non trouvé"
        ]);
    }
   
} else {
    // If no card is found, return success as false
    echo json_encode([
        'success' => false,
        'message' => "Aucune carte détectée"
    ]);
}

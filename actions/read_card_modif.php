<?php
require "../inc/conn_db.php";

// Set the charset to UTF-8 to ensure proper encoding
$conn->set_charset("utf8");

// Récupérer la valeur de la carte ou vérifiez simplement si une entrée existe
$sql = "SELECT ip, valeur FROM envoi_app LIMIT 1"; // Check for the latest card data
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // If a card is found, retrieve the user associated with this card and wallet balance
    $card = $result->fetch_assoc();

    // Combined query to get user details and wallet balance
    $user_sql = "SELECT * FROM `envoi_app` WHERE valeur = ?";
    $user_stmt = $conn->prepare($user_sql);

    // Bind the card value to the query
    $user_stmt->bind_param("s", $card['valeur']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();

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

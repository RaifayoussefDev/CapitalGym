<?php
require "../inc/conn_db.php";

// Récupérer la valeur de la carte ou vérifiez simplement si une entrée existe
$sql = "SELECT ip, valeur FROM envoi_app LIMIT 1"; // Check for the latest card data
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // If a card is found, retrieve the user associated with this card and wallet balance
    $card = $result->fetch_assoc();

    // Combined query to get user details and wallet balance
    $user_sql = "
                SELECT 
            u.id, 
            u.cin, 
            u.matricule, 
            u.photo, 
            u.nom, 
            u.prenom, 
            p.pack_name, 
            a.date_abonnement,
            a.date_debut,
            a.date_fin, 
            w.balance,
            u.etat
        FROM 
            users u
        LEFT JOIN 
            abonnements a ON u.id = a.user_id
        LEFT JOIN 
            packages p ON a.type_abonnement = p.id
        LEFT JOIN 
            wallet w ON u.id = w.user_id
        WHERE 
            u.id_card = ? ;
            ";

    $user_stmt = $conn->prepare($user_sql);
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

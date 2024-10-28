<?php
require "../inc/conn_db.php";

// SQL query to get the latest record from envoi_tournique within the last 6 seconds
$sql = "SELECT ip, valeur, created_at 
        FROM envoi_tournique 
        WHERE created_at >= NOW() - INTERVAL 6 SECOND
        ORDER BY created_at DESC
        LIMIT 1";

$result = $conn->query($sql);

// Check if there's a result
if ($result->num_rows > 0) {
    // Fetch the result as an associative array
    $row = $result->fetch_assoc();
    $valeur = $row['valeur'];

    // Check if the badge is already assigned to a user
    $sql_check_user = "SELECT * FROM users WHERE id_card = '$valeur'";
    $user_result = $conn->query($sql_check_user);

    if ($user_result->num_rows > 0) {
        // If the badge is already assigned to a user
        $user = $user_result->fetch_assoc();
        echo json_encode([
            'success' => false,
            'message' => "Le badge est déjà affecté à un utilisateur.",
            'user' => $user // Optionally return user data
        ]);
    } else {
        // If the badge is not assigned to any user
        echo json_encode([
            'success' => true,
            'data' => $row,
            'message' => "Le badge n'est pas encore affecté."
        ]);
    }

} else {
    // If no record is found in the last 6 seconds
    echo json_encode([
        'success' => false,
        'message' => "Aucune carte détectée dans les dernières 6 secondes."
    ]);
}

$conn->close();
?>

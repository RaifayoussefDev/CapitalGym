<?php
require "../inc/conn_db.php";

// Define the SQL query
$sql = "
    SELECT 
        u.id,
        u.nom,
        u.prenom,
        u.email,
        u.phone,
        a.type AS abonnement_type,
        GROUP_CONCAT(DISTINCT act.nom ORDER BY act.nom ASC SEPARATOR ', ') AS activites,
        GROUP_CONCAT(DISTINCT CONCAT(act.nom, ' (', act.prix, ')') ORDER BY act.nom ASC SEPARATOR ', ') AS activites_prix
    FROM 
        users u
    JOIN 
        user_activites ua ON u.id = ua.user_id
    JOIN 
        abonnements a ON ua.abonnement_id = a.id
    LEFT JOIN 
        activites act ON ua.activite_id = act.id
    GROUP BY 
        u.id, a.type
    ORDER BY 
        u.nom, u.prenom;
";

// Execute the query
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    // Fetch all rows as an associative array
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return data in JSON format
echo json_encode(['data' => $data]);

// Close the connection
$conn->close();
?>

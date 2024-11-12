<?php 

require '../inc/conn_db.php'; // Include the database connection
require "./preparer_contrat.php";

// SQL query to fetch all user IDs where role_id = 3
$sql = "SELECT id FROM users WHERE role_id = 3 and etat = 'actif'";

// Execute the query
$result = $conn->query($sql);

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Loop through the results and display each user ID
    while ($user = $result->fetch_assoc()) {
        echo $user['id'] ;
        GenerateContrat($user['id']);
    }
} else {
    echo "Aucun utilisateur trouvé avec role_id = 3.";
}

?>

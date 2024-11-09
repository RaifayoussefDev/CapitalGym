<?php

require '../inc/conn_db.php';


// Get user ID from the URL
$userId = $_GET['id_user'];

// Fetch user details and contract information from the database
$query = "SELECT contract_name FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Check if the contract exists for the user
if ($user && $user['contract_name']) {
    $contractName = $user['contract_name'];
    $contractPath = "../contrat/$contractName"; // Assuming contracts are stored in this folder

    // Check if the contract file exists
    if (file_exists($contractPath)) {
        // If file exists, serve the file content to the user
        $fileContent = file_get_contents($contractPath);
        echo nl2br($fileContent); // Display the contract content with proper line breaks
    } else {
        echo "Le contrat n'a pas pu être trouvé.";
    }
} else {
    echo "Aucun contrat trouvé pour cet utilisateur.";
}

// Close the database connection
mysqli_close($conn);
?>

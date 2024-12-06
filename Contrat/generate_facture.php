<?php 

require '../inc/conn_db.php'; // Include the database connection
require "./preparer_facture.php"; // Include the preparer_contrat.php file once

// Check if 'id_user' is passed via GET
// if (isset($_GET['id_user'])) {
    $id_user = 346; // Sanitize the user ID (ensure it's an integer)

    // Prepare SQL query to fetch the user by specific id_user
    $sql = "SELECT id, contract_name FROM users WHERE id = ? AND role_id = 3 AND etat = 'actif'";

    // Prepare and bind the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_user); // Bind the user id as an integer
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            // Fetch the user data
            $user = $result->fetch_assoc();
            // Generate the contract for the user
            $contract_name = GenerateFacture($user['id']);

            // Redirect to a page where the user can download the contract
            header("Location: ./" . $contract_name);
            exit;
        } else {
            echo "Aucun utilisateur trouvé avec cet ID ou il n'est pas actif.";
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête SQL.";
    }
// } else {
//     // If no 'id_user' is provided in the URL, get all users with role_id = 3
//     $sql = "SELECT id FROM users WHERE role_id = 3 AND etat = 'actif'";

//     // Execute the query
//     if ($result = $conn->query($sql)) {
//         // Check if any rows are returned
//         if ($result->num_rows > 0) {
//             // Loop through the results and generate contract for each user
//             while ($user = $result->fetch_assoc()) {
//                 echo "Utilisateur ID: " . $user['id'] . "<br>";
//                 // Generate contract for each active user
//                 $contract_name = GenerateFacture($user['id']);
//                 // You can redirect to the contract download page after generation if needed.
//             }
//         } else {
//             echo "Aucun utilisateur trouvé avec role_id = 3.";
//         }
//     } else {
//         echo "Erreur de requête SQL.";
//     }
// }
?>

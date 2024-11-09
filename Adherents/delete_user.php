<?php
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is provided
if (isset($_GET['id_user']) && !empty($_GET['id_user'])) {
    $user_id = (int)$_GET['id_user'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related records from each table that references the users table
        $tablesToDelete = [
            "virement" => "id_utilisateur",
            "cheque" => "id_utilisateur",
            "payments" => "user_id",
            "abonnements" => "user_id",
            "user_activites" => "user_id",
            "wallet" => "user_id",
            "coaches" => "user_id",
            "documents" => "user_id",
            "envoi" => "user_id",
            "envoi_app" => "user_id",
            "reservations" => "user_id"
        ];

        foreach ($tablesToDelete as $table => $column) {
            $query = "DELETE FROM $table WHERE $column = $user_id";
            if (!$conn->query($query)) {
                throw new Exception("Error deleting from $table: " . $conn->error);
            }
        }

        // Additional fields in `users` that reference other `users` records
        $updateSaisiePar = "UPDATE users SET saisie_par = NULL WHERE saisie_par = $user_id";
        if (!$conn->query($updateSaisiePar)) {
            throw new Exception("Error updating saisie_par in users: " . $conn->error);
        }

        $updateUpdatedBy = "UPDATE users SET updated_by = NULL WHERE updated_by = $user_id";
        if (!$conn->query($updateUpdatedBy)) {
            throw new Exception("Error updating updated_by in users: " . $conn->error);
        }

        // Finally, delete the user from the users table
        $deleteUser = "DELETE FROM users WHERE id = $user_id";
        if (!$conn->query($deleteUser)) {
            throw new Exception("Error deleting user: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();
        echo "L'utilisateur a été supprimé avec succès.";
        header('location:supprimer.php');
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        echo "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }
} else {
    echo "ID d'utilisateur invalide.";
}

$conn->close();
?>

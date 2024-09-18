<?php
require "../inc/app.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start transaction
$conn->autocommit(FALSE);

// Retrieve user_id from the URL parameter
$user_id = isset($_GET['id_user']) ? intval($_GET['id_user']) : null;

if ($user_id === null) {
    echo "Error: No user ID provided.";
    exit;
}

try {
    // Update user details
    $update_user_sql = "UPDATE users SET 
                            cin = ?, 
                            nom = ?, 
                            prenom = ?, 
                            email = ?, 
                            phone = ?, 
                            date_naissance = ?, 
                            genre = ? 
                        WHERE id = ?";
    $stmt = $conn->prepare($update_user_sql);
    $stmt->bind_param(
        "sssssssi",
        $_POST['cin'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['date_naissance'],
        $_POST['genre'],
        $user_id
    );
    $stmt->execute();
    $stmt->close();

    // Update or insert abonnement details
    $abonnement_sql = "INSERT INTO abonnements (user_id, type, date_fin, renouvellement_automatique)
                       VALUES (?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE
                       type = VALUES(type), date_fin = VALUES(date_fin), renouvellement_automatique = VALUES(renouvellement_automatique)";
    $stmt = $conn->prepare($abonnement_sql);
    $stmt->bind_param("isss", $user_id, $_POST['type_abonnement'], $_POST['date_fin_abn'], $_POST['renouvellement']);
    $stmt->execute();
    $abonnement_id = $stmt->insert_id ? $stmt->insert_id : $conn->query("SELECT id FROM abonnements WHERE user_id = $user_id")->fetch_assoc()['id'];
    $stmt->close();

    // Delete old user activities
    $delete_user_activites_sql = "DELETE FROM user_activites WHERE abonnement_id = ?";
    $stmt = $conn->prepare($delete_user_activites_sql);
    $stmt->bind_param('i', $abonnement_id);
    $stmt->execute();
    $stmt->close();

    // Insert new user activities
    if (!empty($_POST['activites'])) {
        $insert_user_activites_sql = "INSERT INTO user_activites (user_id, activite_id, abonnement_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_user_activites_sql);
        foreach ($_POST['activites'] as $activite_id) {
            $stmt->bind_param("iii", $user_id, $activite_id, $abonnement_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Insert payment details
    $payment_sql = "INSERT INTO payments (user_id, abonnement_id, montant_paye, type_paiement_id, reste, total)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    montant_paye = VALUES(montant_paye), type_paiement_id = VALUES(type_paiement_id), reste = VALUES(reste), total = VALUES(total)";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param("iidddd", $user_id, $abonnement_id, $_POST['montant_paye'], $_POST['type_paiement'], $_POST['reste'], $_POST['total']);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo "User information updated successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

// Close connection
$conn->close();
?>

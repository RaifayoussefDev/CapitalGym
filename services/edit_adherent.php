<?php
ob_start(); // Start output buffering
session_start();
require "../inc/conn_db.php";

$conn->autocommit(FALSE); // Start transaction

// Function to upload profile photo
function uploadFile($file, $target_dir, $allowed_types = [])
{
    $file_name = basename($file["name"]);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $base_name = pathinfo($file_name, PATHINFO_FILENAME);

    if (!empty($allowed_types) && !in_array($file_type, $allowed_types)) return false;
    if ($file["size"] > 5000000) return false;

    $new_file_name = $file_name;
    $counter = 1;
    while (file_exists($target_dir . $new_file_name)) {
        $new_file_name = $base_name . "_" . time() . "_" . $counter . "." . $file_type;
        $counter++;
    }

    $target_file = $target_dir . $new_file_name;
    return move_uploaded_file($file["tmp_name"], $target_file) ? $new_file_name : false;
}

// Handle profile photo upload
$photo_name = "";
if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
    $photo_name = uploadFile($_FILES["profile_photo"], "../assets/img/capitalsoft/profils/", ['jpg', 'jpeg', 'png']);
    if (!$photo_name) {
        $conn->rollback();
        throw new Exception("Failed to upload profile photo.");
    }
}

// Retrieve user ID from session or GET
$user_id = $_GET['id_user'] ?? null;
if (!$user_id) {
    $conn->rollback();
    throw new Exception("User ID not found.");
}

// Additional fields
$date_naissance = $_POST['date_naissance'];
$adresse = $_POST['adresse'];
$fonction = $_POST['fonction'];
$num_urgence = $_POST['num_urgence'];
$employeur = $_POST['employeur'];
$changer_par = $_SESSION['id'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$cin = $_POST['cin'];
$badge_number = $_POST['badge_number'];

// SQL to update user details
$user_sql = "UPDATE `users` SET 
    `nom` = ?, `prenom` = ?, `cin` = ?, `phone` = ?, `email` = ?, 
    `photo` = ?, `adresse` = ?, `fonction` = ?, `num_urgence` = ?, 
    `employeur` = ?, `updated_by` = ?, `id_card` = ? 
    WHERE `id` = ?";

$stmt = $conn->prepare($user_sql);
if (!$stmt) {
    $conn->rollback();
    throw new Exception("Failed to prepare user update: " . $conn->error);
}
$stmt->bind_param("ssssssssssisi", $nom, $prenom, $cin, $phone, $email, $photo_name, $adresse, $fonction, $num_urgence, $employeur, $changer_par, $badge_number, $user_id);
if (!$stmt->execute()) {
    $conn->rollback();
    throw new Exception("User update failed: " . $stmt->error);
}
$stmt->close();

// Update abonnement details
$abonnement_date_fin = $_POST['date_fin_abn'];
$abonnement_type = $_POST['categorie_adherence'];
$date_debut = $_POST['date_debut_paiement'];
$offres_promotionnelles = $_POST['offre_promo'];
$description = $_POST['description'];

$abonnement_sql = "UPDATE `abonnements` SET `date_fin` = ?, `type_abonnement` = ?, 
    `date_debut` = ?, `offres_promotionnelles` = ?, `description` = ? 
    WHERE  `user_id` = ?";
$stmt = $conn->prepare($abonnement_sql);
if (!$stmt) {
    $conn->rollback();
    throw new Exception("Failed to prepare abonnement update: " . $conn->error);
}
$stmt->bind_param("sssssi", $abonnement_date_fin, $abonnement_type, $date_debut, $offres_promotionnelles, $description, $user_id);
if (!$stmt->execute()) {
    $conn->rollback();
    throw new Exception("Abonnement update failed: " . $stmt->error);
}
$stmt->close();

// Retrieve the abonnement_id
$abonnement_id_sql = "SELECT id FROM `abonnements` WHERE `user_id` = ?";
$stmt = $conn->prepare($abonnement_id_sql);
if (!$stmt) {
    $conn->rollback();
    throw new Exception("Failed to prepare abonnement ID fetch: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    $conn->rollback();
    throw new Exception("Failed to execute abonnement ID fetch: " . $stmt->error);
}
$stmt->bind_result($abonnement_id);
$stmt->fetch();
$stmt->close();


// Update payment details
if (isset($_POST['type_paiement'], $_POST['montant_paye'], $_POST['reste'], $_POST['total_activites'])) {
    foreach ($_POST['type_paiement'] as $index => $type_paiement_id) {
        $montant_paye = floatval($_POST['montant_paye'][$index]);
        $reste = floatval($_POST['reste'][$index]);
        $total = floatval($_POST['total_activites']);

        // Insert payment details
        $payment_sql = "INSERT INTO `payments` (`montant_paye`, `reste`, `total`, `user_id`, `abonnement_id`, `type_paiement_id`) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($payment_sql);
        if ($stmt) {
            $stmt->bind_param("ddiiii", $montant_paye, $reste, $total, $user_id, $abonnement_id, $type_paiement_id);
            $stmt->execute();
            $payment_id = $conn->insert_id; // Get the last inserted payment ID
            $stmt->close();
        } else {
            $conn->rollback();
            throw new Exception("Failed to prepare payment insert: " . $conn->error);
        }

        // If payment type is cheque, insert cheque details
        if ($type_paiement_id == 3) {
            $nomTitulaire = $_POST['nomTitulaire'][$index] ?? null;
            $numeroCheque = $_POST['numeroCheque'][$index] ?? null;
            $dateEmission = $_POST['dateEmission'][$index] ?? null;
            $banqueEmettrice = $_POST['banqueEmettrice'][$index] ?? null;
            $numeroCompte = $_POST['numeroCompte'][$index] ?? null;

            if ($nomTitulaire && $numeroCheque && $dateEmission && $banqueEmettrice) {
                $cheque_sql = "INSERT INTO `cheque` (`nomTitulaire`, `numeroCheque`, `dateEmission`, `banqueEmettrice`, `numeroCompte`, `payment_id`)
                               VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($cheque_sql);
                if ($stmt) {
                    $stmt->bind_param("sssssi", $nomTitulaire, $numeroCheque, $dateEmission, $banqueEmettrice, $numeroCompte, $payment_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Handle SQL prepare error for cheque insertion
                    continue; // Skip this iteration if the statement preparation fails
                }
            }
        }
        // If payment type is virement, insert virement details
        elseif ($type_paiement_id == 4) {
            // Ensure virement details are set
            $nomEmetteur = $_POST['nomEmetteur'][$index] ?? null; // Nom de l'émetteur
            $dateImitation = $_POST['dateImitation'][$index] ?? null; // Date d'imitation
            $reference = $_POST['reference'][$index] ?? null; // Référence
            $banqueEmettrice = $_POST['banqueEmettrice'][$index] ?? null; // Banque émettrice

            // Validate virement details
            if (!empty($nomEmetteur) && !empty($dateImitation) && !empty($reference) && !empty($banqueEmettrice)) {
                $virement_sql = "INSERT INTO `virement` (nomEmetteur, dateImitation, reference, banqueEmettrice, id_utilisateur, abonnement_id, payment_id)
                                 VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($virement_sql);
                if ($stmt) {
                    $stmt->bind_param("sssssii", $nomEmetteur, $dateImitation, $reference, $banqueEmettrice, $user_id, $abonnement_id, $payment_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Handle SQL prepare error for virement insertion
                    continue; // Skip this iteration if the statement preparation fails
                }
            } else {
                // Handle missing virement details (e.g., set an error message)
            }
        }
    }
}


$conn->commit();
header('location:../adherents/');
$conn->close();
ob_end_flush();

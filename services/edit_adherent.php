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
$note = $_POST['note'];
$n_dossier = $_POST['n_dossier'];

// SQL to update user details
$user_sql = "UPDATE `users` SET 
    `nom` = ?, `prenom` = ?, `cin` = ?, `phone` = ?, `email` = ?,date_naissance = ?, 
    `photo` = ?, `adresse` = ?, `fonction` = ?, `num_urgence` = ?, 
    `employeur` = ?, `updated_by` = ?, `id_card` = ? , `note` = ? , `N_dossier`=?
    WHERE `id` = ?";

$stmt = $conn->prepare($user_sql);
if (!$stmt) {
    $conn->rollback();
    throw new Exception("Failed to prepare user update: " . $conn->error);
}
$stmt->bind_param("sssssssssssisssi", $nom, $prenom, $cin, $phone, $email, $date_naissance, $photo_name, $adresse, $fonction, $num_urgence, $employeur, $changer_par, $badge_number, $note,$n_dossier , $user_id);
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
echo $date_debut;
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
echo $abonnement_sql;
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

// Insert user activities
if (isset($_POST['value']) && is_array($_POST['value'])) {
    foreach ($_POST['value'] as $activite_id) {
        // Assuming you have a logic to retrieve the correct abonnement_id for the user
        $abonnement_id = $_POST['id_abonnement']; // Replace this with logic to dynamically fetch if necessary
        $periode_activite = $_POST['periode_activite'][$activite_id];
        $type_activite = $_POST['type_activite'][array_search($activite_id, $_POST['value'])]; // Get the type for the current activity

        // Determine type of purchase based on the activity type
        $type_achat = ($type_activite == 'par mois') ? 'achat_par_periode' : 'achat_par_science';

        // Set date_fin based on the type
        if ($type_achat === 'achat_par_science') {
            $date_fin = NULL; // No date_fin for 'achat_par_science'
        } else {
            // Here we assume the period is defined in months, modify as needed
            $date_fin = date('Y-m-d', strtotime("+$periode_activite month")); // Example: add the period to the current date
        }

        // Check if the user already has this activity
        $check_activite_sql = "SELECT 1 FROM `user_activites` WHERE `user_id` = ? AND `activite_id` = ?";
        $check_stmt = $conn->prepare($check_activite_sql);
        if ($check_stmt === false) {
            $conn->rollback(); // Rollback if prepare fails
            throw new Exception("Failed to prepare check statement: " . $conn->error);
        }

        // Bind parameters for the check
        $check_stmt->bind_param("ii", $user_id, $activite_id);

        // Execute the check statement
        $check_stmt->execute();
        $check_stmt->store_result();

        // If the activity already exists for this user, skip insertion
        if ($check_stmt->num_rows > 0) {
            // Activity already exists, skip insertion
            $check_stmt->close();
            continue; // Skip this iteration of the loop
        }

        // Close check statement
        $check_stmt->close();

        // SQL to insert into user_activites
        $insert_activite_sql = "INSERT INTO `user_activites` (`user_id`, `activite_id`, `abonnement_id`, `date_inscription`, `periode_activites`, `date_fin`, `type_activite`)
            VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ";

        $stmt = $conn->prepare($insert_activite_sql);
        if ($stmt === false) {
            $conn->rollback(); // Rollback if prepare fails
            throw new Exception("Failed to prepare insert statement: " . $conn->error);
        }

        // Bind parameters for insertion
        $stmt->bind_param(
            "iiisss",
            $user_id,
            $activite_id,
            $abonnement_id,
            $periode_activite,
            $date_fin,
            $type_achat
        );

        if (!$stmt->execute()) {
            $conn->rollback(); // Rollback if execution fails
            throw new Exception("Failed to execute insert statement: " . $stmt->error);
        }
        $stmt->close();
    }
}


// Update payment details
if (isset($_POST['type_paiement'], $_POST['montant_paye'], $_POST['reste'], $_POST['total_activites'])) {
    $conn->begin_transaction(); // Start a transaction

    try {
        foreach ($_POST['type_paiement'] as $index => $type_paiement_id) {
            $montant_paye = floatval($_POST['montant_paye'][$index]);
            $reste = floatval($_POST['reste'][$index]);
            $total = floatval($_POST['total_activites']);

            // Update the total if necessary
            if ($total + $montant_paye != 0) {
                $payment_sql = "UPDATE `payments` 
                                SET `total` = total + ? 
                                WHERE `user_id` = ? AND `abonnement_id` = ? AND `type_paiement_id` = ?";
                $stmt = $conn->prepare($payment_sql);
                if ($stmt) {
                    $stmt->bind_param("diii", $montant_paye, $user_id, $abonnement_id, $type_paiement_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Failed to prepare payment update: " . $conn->error);
                }
            }

            // Insert a new payment record
            $payment_sql = "INSERT INTO `payments` (`montant_paye`, `reste`, `total`, `user_id`, `abonnement_id`, `type_paiement_id`) 
                            VALUES (?, ?, 0, ?, ?, ?)";
            $stmt = $conn->prepare($payment_sql);
            if ($stmt) {
                $stmt->bind_param("ddiii", $montant_paye, $reste, $user_id, $abonnement_id, $type_paiement_id);
                $stmt->execute();
                $payment_id = $conn->insert_id; // Get the last inserted payment ID
                $stmt->close();
            } else {
                throw new Exception("Failed to prepare payment insert: " . $conn->error);
            }

            // Handle cheque details (type_paiement_id == 3)
            if ($type_paiement_id == 3) {
                $nomTitulaire = $_POST['nomTitulaire'][$index] ?? null;
                $numeroCheque = $_POST['numeroCheque'][$index] ?? null;
                $dateEmission = $_POST['dateEmission'][$index] ?? null;
                $banqueEmettrice = $_POST['banqueEmettrice'][$index] ?? null;
                $numeroCompte = $_POST['numeroCompte'][$index] ?? null;

                if ($nomTitulaire && $numeroCheque && $dateEmission && $banqueEmettrice) {
                    $cheque_sql = "INSERT INTO `cheque` (`nomTitulaire`, `numeroCheque`, `dateEmission`, `banqueEmettrice`, `numeroCompte`, `payment_id`, `abonnement_id`, `id_utilisateur`)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($cheque_sql);
                    if ($stmt) {
                        $stmt->bind_param("sssssiis", $nomTitulaire, $numeroCheque, $dateEmission, $banqueEmettrice, $numeroCompte, $payment_id, $abonnement_id, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        throw new Exception("Failed to prepare cheque insert: " . $conn->error);
                    }
                }
            }

            // Handle virement details (type_paiement_id == 4)
            if ($type_paiement_id == 4) {
                $nomEmetteur = $_POST['nomEmetteur'][$index] ?? null;
                $dateImitation = $_POST['dateImitation'][$index] ?? null;
                $reference = $_POST['reference'][$index] ?? null;
                $banqueEmettrice = $_POST['banqueEmettrice'][$index] ?? null;

                if (!empty($nomEmetteur) && !empty($dateImitation) && !empty($reference) && !empty($banqueEmettrice)) {
                    $virement_sql = "INSERT INTO `virement` (nomEmetteur, dateImitation, reference, banqueEmettrice, id_utilisateur, abonnement_id, payment_id)
                                     VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($virement_sql);
                    if ($stmt) {
                        $stmt->bind_param("sssssii", $nomEmetteur, $dateImitation, $reference, $banqueEmettrice, $user_id, $abonnement_id, $payment_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        throw new Exception("Failed to prepare virement insert: " . $conn->error);
                    }
                }
            }
        }

        // Commit the transaction if all queries succeed
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}



$conn->commit();
header('location:../adherents/');

$conn->close();
ob_end_flush();

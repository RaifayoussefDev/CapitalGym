<?php
ob_start(); // Start output buffering
session_start();
require "../inc/conn_db.php";
// require "../inc/app.php";

// Function to upload files
function uploadFile($file, $target_dir, $allowed_types = [])
{
    $file_name = basename($file["name"]);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $base_name = pathinfo($file_name, PATHINFO_FILENAME);

    // Check file type
    if (!empty($allowed_types) && !in_array($file_type, $allowed_types)) {
        return false;
    }

    // Check file size (5MB limit)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Ensure unique file name
    $new_file_name = $file_name;
    $counter = 1;
    while (file_exists($target_dir . $new_file_name)) {
        $new_file_name = $base_name . "_" . time() . "_" . $counter . "." . $file_type;
        $counter++;
    }

    $target_file = $target_dir . $new_file_name;

    // Attempt to move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_file_name; // Return the new file name
    } else {
        return false;
    }
}

// Handle file upload
function uploadProfilePhoto($file)
{
    $target_dir = "../assets/img/capitalsoft/profils/";
    return uploadFile($file, $target_dir, ['jpg', 'jpeg', 'png']);
}

// Start transaction
$conn->autocommit(FALSE);

// Handle profile photo upload
$photo_name = "";
if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
    $photo_name = uploadProfilePhoto($_FILES["profile_photo"]);
    if (!$photo_name) {
        $conn->rollback(); // Rollback transaction if upload fails
        throw new Exception("Failed to upload profile photo.");
    }
}

// Retrieve user ID from session or GET
if (isset($_GET['id_user'])) {
    $user_id = $_GET['id_user'];
} else {
    $conn->rollback(); // Rollback transaction if no user ID
    throw new Exception("User ID not found in session.");
}

// Additional fields
$date_naissance = $_POST['date_naissance'];
$adresse = $_POST['adresse'];
$fonction = $_POST['fonction'];
$num_urgence = $_POST['num_urgence'];
$employeur = $_POST['employeur'];
$changer_par = $_SESSION['id']; // Assuming user ID is stored in session
$phone = $_POST['phone'];
$email = $_POST['email'];
$nom = $_POST['nom'];
$prenom= $_POST['prenom'];
$cin= $_POST['cin'];

// SQL to update user details
$user_sql = "UPDATE `users` SET 
    `nom` = ?,
    `prenom` = ?,
    `cin` = ?,
    `phone` = ?, 
    `email` = ?, 
    `photo` = ?, 
    `adresse` = ?, 
    `fonction` = ?, 
    `num_urgence` = ?, 
    `employeur` = ?, 
    `updated_by` = ?, 
    `id_card` = ? 
WHERE `id` = ?;";

// Prepare the statement
$stmt = $conn->prepare($user_sql);
if ($stmt === false) {
    $conn->rollback(); // Rollback transaction if prepare fails
    throw new Exception("Failed to prepare statement: " . $conn->error);
}

$badge_number = $_POST['badge_number'];

// Bind parameters and execute the statement
$stmt->bind_param(
    "ssssssssssisi",
    $nom,
    $prenom,
    $cin,
    $phone,
    $email,
    $photo_name,
    $adresse,
    $fonction,
    $num_urgence,
    $employeur,
    $changer_par,
    $badge_number,
    $user_id
);

if (!$stmt->execute()) {
    $conn->rollback(); // Rollback transaction if execution fails
    throw new Exception("Failed to execute statement: " . $stmt->error);
}

// Close the statement
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

        // SQL to insert into user_activites
        $insert_activite_sql = "INSERT INTO `user_activites` (`user_id`, `activite_id`, `abonnement_id`, `date_inscription`, `periode_activites`, `date_fin`, `type_activite`)
            VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ";

        $stmt = $conn->prepare($insert_activite_sql);
        if ($stmt === false) {
            $conn->rollback(); // Rollback if prepare fails
            throw new Exception("Failed to prepare insert statement: " . $conn->error);
        }

        // Bind parameters
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

if (isset($_POST['type_paiement'], $_POST['montant_paye'], $_POST['reste'], $_POST['total_activites'], $_POST['id_abonnement'])) {
    foreach ($_POST['type_paiement'] as $index => $type_paiement_id) {
        // Convert string amounts to float
        $montant_paye = isset($_POST['montant_paye'][$index]) ? floatval($_POST['montant_paye'][$index]) : 0;
        $reste = isset($_POST['reste'][$index]) ? floatval($_POST['reste'][$index]) : 0;
        $total = floatval($_POST['total_activites']);
        $abonnement_id = $_POST['id_abonnement'];
        $user_id = $_POST['user_id'] ?? null; // Ensure $user_id is set

        // Validate inputs
        if ($montant_paye < 0 || $reste < 0 || $total < 0 || !$user_id) {
            continue; // Skip this iteration if validation fails
        }

        // Insert payment details
        $payment_sql = "INSERT INTO payments (user_id, abonnement_id, montant_paye, type_paiement_id, reste, total)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($payment_sql);
        if ($stmt) {
            $stmt->bind_param("iidddd", $user_id, $abonnement_id, $montant_paye, $type_paiement_id, $reste, $total);
            $stmt->execute();
            $payment_id = $stmt->insert_id; // Get the last inserted payment ID
            $stmt->close();
        } else {
            continue; // Skip this iteration if the statement preparation fails
        }

        // If the payment type is cheque (id 3), insert cheque details
        if ($type_paiement_id == 3) {
            // Ensure cheque details are set
            $nomTitulaire = $_POST['nomTitulaire'][$index] ?? null;
            $numeroCheque = $_POST['numeroCheque'][$index] ?? null;
            $dateEmission = $_POST['dateEmission'][$index] ?? null;
            $banqueEmettrice = $_POST['banqueEmettrice'][$index] ?? null;
            $numeroCompte = $_POST['numeroCompte'][$index] ?? null;

            // Validate cheque details
            if (!empty($nomTitulaire) && !empty($numeroCheque) && !empty($dateEmission) && !empty($banqueEmettrice)) {
                $cheque_sql = "INSERT INTO cheque (nomTitulaire, numeroCheque, dateEmission, banqueEmettrice, numeroCompte, id_utilisateur, abonnement_id, payment_id)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($cheque_sql);
                if ($stmt) {
                    $stmt->bind_param("sssssiii", $nomTitulaire, $numeroCheque, $dateEmission, $banqueEmettrice, $numeroCompte, $user_id, $abonnement_id, $payment_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    continue; // Skip this iteration if the statement preparation fails
                }
            } else {
                // Handle missing cheque details (e.g., set an error message)
            }
        }
    }
}



// Commit the transaction
$conn->commit();

header('location:../adherents/');

// Close connection and end output buffering
$conn->close();
ob_end_flush();

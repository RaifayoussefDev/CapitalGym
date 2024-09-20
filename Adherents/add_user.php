<?php
ob_start(); // Start output buffering

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

// Function to generate a secure random password
function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    return $password;
}

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

    // Check file size (adjust as needed)
    if ($file["size"] > 5000000) { // 5MB
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
        return $new_file_name; // Return only the new file name
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

// Handle document uploads
function uploadDocuments($files)
{
    $target_dir = "../assets/documents/";
    $allowed_types = ['jpg', 'jpeg', 'png', 'doc', 'docx', 'pdf', 'xlsx'];
    $uploaded_files = [];

    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] == UPLOAD_ERR_OK) {
            $file = [
                'name' => $files['name'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'type' => $files['type'][$key]
            ];
            $file_name = uploadFile($file, $target_dir, $allowed_types);

            if ($file_name) {
                $uploaded_files[] = $file_name;
            } else {
                throw new Exception("Failed to upload file: " . $name);
            }
        }
    }

    return $uploaded_files;
}

// Start transaction
$conn->autocommit(FALSE);

try {
    // Handle profile photo upload
    $photo_name = "";
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $photo_name = uploadProfilePhoto($_FILES["profile_photo"]);
        if (!$photo_name) {
            throw new Exception("Failed to upload profile photo.");
        }
    }

    // Generate a random password
    $password = generateRandomPassword();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    // Initialize an array to store missing fields
    $missingFields = [];

    // List of required fields
    $requiredFields = [
        'cin' => 'CIN',
        'nom' => 'Nom',
        'prenom' => 'Prénom',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'genre' => 'Genre',
        'type_abonnement' => 'Type d\'abonnement',
        'date_fin_abn' => 'Date de fin d\'abonnement',
        'activites' => 'Activités',
        'type_paiement' => 'Type de paiement',
        'montant_paye' => 'Montant payé',
        'reste' => 'Reste',
        'total' => 'Total'
    ];

    // Validate required POST parameters
    foreach ($requiredFields as $field => $label) {
        if (empty($_POST[$field])) {
            $missingFields[] = $label;
        }
    }

    // If there are missing fields, stop the process and redirect
    if (!empty($missingFields)) {
        $missingFieldsList = implode(', ', $missingFields);
        header("Location: ../error_page.php?missing=" . urlencode($missingFieldsList)); 
        exit();
    }

    $user_id = $_SESSION['last_user'];

    // Insert user details
    $user_sql = "UPDATE `users` SET `etat` = 'actif' and photo=? WHERE `users`.`id` = ?;";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("is", $user_id , $photo_name);
    $stmt->execute();
    $stmt->close();

    // Insert subscription details
    $abonnement_sql = "INSERT INTO abonnements (user_id, type, date_fin)
                       VALUES (?, ?, ?)";
    $stmt = $conn->prepare($abonnement_sql);
    $stmt->bind_param("iss", $user_id, $_POST['type_abonnement'], $_POST['date_fin_abn']);
    $stmt->execute();
    $abonnement_id = $stmt->insert_id;
    $stmt->close();

    // Insert user activities
    foreach ($_POST['activites'] as $activite_id) {
        $user_activites_sql = "INSERT INTO user_activites (user_id, activite_id, abonnement_id)
                               VALUES (?, ?, ?)";
        $stmt = $conn->prepare($user_activites_sql);
        $stmt->bind_param("iii", $user_id, $activite_id, $abonnement_id);
        $stmt->execute();
        $stmt->close();
    }

    // Handle document uploads
    $uploaded_files = uploadDocuments($_FILES['file_document']);

    // Insert document details
    foreach ($uploaded_files as $index => $file_name) {
        $libelle_document = $_POST['libelle_document'][$index];
        $doc_sql = "INSERT INTO documents (user_id, libelle_document, file_document)
                    VALUES (?, ?, ?)";
        $stmt = $conn->prepare($doc_sql);
        $stmt->bind_param("iss", $user_id, $libelle_document, $file_name);
        $stmt->execute();
        $stmt->close();
    }

    // Loop through each selected payment type
    foreach ($_POST['type_paiement'] as $index => $type_paiement_id) {
        // Convert string amounts to float
        $montant_paye = floatval($_POST['montant_paye'][$index]);
        $reste = floatval($_POST['reste'][$index]);
        $total = floatval($_POST['total']);

        // Insert payment details
        $payment_sql = "INSERT INTO payments (user_id, abonnement_id, montant_paye, type_paiement_id, reste, total)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($payment_sql);
        $stmt->bind_param("iidddd", $user_id, $abonnement_id, $montant_paye, $type_paiement_id, $reste, $total);
        $stmt->execute();
        $stmt->close();

        // If the payment type is cheque (id 3), insert cheque details
        if ($type_paiement_id == 3) {
            $cheque_sql = "INSERT INTO cheque (nomTitulaire, numeroCheque, dateEmission, banqueEmettrice, numeroCompte, id_utilisateur, abonnement_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($cheque_sql);
            $stmt->bind_param("ssssssi", $_POST['nomTitulaire'], $_POST['numeroCheque'], $_POST['dateEmission'], $_POST['banqueEmettrice'], $_POST['numeroCompte'], $user_id, $abonnement_id);
            $stmt->execute();
            $stmt->close();
        }
    }


    
    // Commit transaction
    $conn->commit();

    // Redirect on success
    header("Location: ./");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    // Redirect to an error page with the error message
    header("Location: ../error_page.php?error=" . urlencode($e->getMessage()));
    exit();
}

// Close the connection
$conn->close();
ob_end_flush();
;?>
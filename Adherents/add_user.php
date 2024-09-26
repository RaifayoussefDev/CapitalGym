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
    // header("Location: ../error_page.php?missing=" . urlencode($missingFieldsList)); 
    exit();
}

// Retrieve user ID from session
if (isset($_SESSION['last_user'])) {
    $user_id = $_SESSION['last_user'];
} else {
    throw new Exception("User ID not found in session.");
}

$type_abonnement = $_POST['categorie_adherence'];

// Requête SQL pour récupérer l'ID et le pack_name du type d'abonnement
$sql = "SELECT id, pack_name FROM packages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $type_abonnement); // Lier l'ID d'abonnement en tant que paramètre
$stmt->execute();
$result = $stmt->get_result();

// Vérifier si le résultat existe
if ($result->num_rows > 0) {
    // Récupérer les données
    $row = $result->fetch_assoc();

    $pack_id = $row['id']; // Récupérer l'ID du pack
    $pack_name = $row['pack_name']; // Récupérer le nom du pack

    // Obtenir la première lettre du pack_name en majuscule
    $premiere_lettre = strtoupper(substr($pack_name, 0, 1));

    // Calculer le matricule (première lettre + user_id + 1000)
    $matricule = $premiere_lettre . ($user_id + 1000);

    // Afficher ou utiliser le matricule
} else {
    $matricule = '';
}

// Retrieve the user_id from envoi_app table based on valeur
$user_id_sql = "SELECT valeur FROM envoi_app WHERE user_id = ?";
$stmt_user_id = $conn->prepare($user_id_sql);
if ($stmt_user_id === false) {
    throw new Exception("Failed to prepare statement: " . $conn->error);
}

// Bind the valeur parameter
$stmt_user_id->bind_param("s", $user_id);
$stmt_user_id->execute();
$stmt_user_id->bind_result($rmsvalue); // Bind the result to user_id
$stmt_user_id->fetch(); // Fetch the result
$stmt_user_id->close();



// Récupération des variables POST
$date_naissance = $_POST['date_naissance']; // date de naissance
$genre = $_POST['genre']; // genre
$adresse = $_POST['adresse']; // adresse
$fonction = $_POST['fonction']; // fonction
$num_urgence = $_POST['num_urgence']; // numéro d'urgence
$employeur = $_POST['employeur']; // employeur


// Update user details in the database
$user_sql = "UPDATE `users` SET
    `matricule` = ?,
    `etat` = 'actif', 
    `photo` = ?, 
    `date_naissance` = ?, 
    `genre` = ?, 
    `adresse` = ?, 
    `fonction` = ?, 
    `num_urgence` = ?, 
    `employeur` = ?,
    `id_card`=? 
WHERE `id` = ?;";

$stmt = $conn->prepare($user_sql);
if ($stmt === false) {
    throw new Exception("Failed to prepare statement: " . $conn->error);
}

// Remplacez les variables par les valeurs appropriées
$stmt->bind_param("sssssssssi", $matricule, $photo_name, $date_naissance, $genre, $adresse, $fonction, $num_urgence, $employeur, $rmsvalue, $user_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute statement: " . $stmt->error);
}
$stmt->close();

// Commit the transaction
$conn->commit();


// Insert subscription details
$abonnement_sql = "INSERT INTO abonnements (user_id, type_abonnement, date_fin , date_debut)
                       VALUES (?, ?, ? , ?)";
$stmt = $conn->prepare($abonnement_sql);
$stmt->bind_param("isss", $user_id, $_POST['categorie_adherence'], $_POST['date_fin_abn'], $_POST['date_debut_paiement']);
$stmt->execute();
$abonnement_id = $stmt->insert_id;
$stmt->close();


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

    // Validate inputs
    if ($montant_paye < 0 || $reste < 0 || $total < 0) {
        // Handle the error (for example, set an error message)
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
        // Handle SQL prepare error
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
                // Handle SQL prepare error
                continue; // Skip this iteration if the statement preparation fails
            }
        } else {
            // Handle missing cheque details (e.g., set an error message)
        }
    }
}
$clear_sql = "DELETE FROM envoi_app";
if ($conn->query($clear_sql) === TRUE) {
    // Optional: You can log or echo a message if needed
    // echo "Table envoi_app has been cleared.";
}




// Commit transaction
$conn->commit();
echo '<script type="text/javascript">
    // Open the PDF generation page in a new tab
    window.open("generate_pdf.php?user_id=' . $user_id . '", "_blank");

    // Redirect back to index.php
    setTimeout(function() {
        window.location.href = "index.php";
    }, 1000); // Adjust the delay if necessary
</script>';
exit();



// Close the connection
$conn->close();
ob_end_flush();;

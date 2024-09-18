<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

session_start();


$_SESSION['user_insert'] = 0;
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
        echo "Sorry, only the following file types are allowed: " . implode(", ", $allowed_types);
        return false;
    }

    // Check file size (adjust as needed)
    if ($file["size"] > 5000000) { // 5MB
        echo "Sorry, your file is too large.";
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
        echo "Sorry, there was an error uploading your file.";
        return false;
    }
}

// Handle profile photo upload
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

if ($_SESSION['user_insert'] == 0) {
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
            'phone' => 'Téléphone'
        ];

        // Validate required POST parameters
        foreach ($requiredFields as $field => $label) {
            if (empty($_POST[$field])) {
                $missingFields[] = $label;
            }
        }

        // Set date_naissance to the current date if it is not provided
        $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : date('Y-m-d');
        $genre = !empty($_POST['genre']) ? $_POST['genre'] : 'M';

        // If there are missing required fields, handle the error
        if (empty($missingFields)) {
            // All required fields are present; Insert user details
            $user_sql = "INSERT INTO users (cin, nom, prenom, email, phone, date_naissance, genre, password, photo, etat, role_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'proceP', 3)";
            $stmt = $conn->prepare($user_sql);

            // The correct number of 's' should match the number of parameters
            $stmt->bind_param(
                "sssssssss", // 10 's' for 10 parameters
                $_POST['cin'],
                $_POST['nom'],
                $_POST['prenom'],
                $_POST['email'],
                $_POST['phone'],
                $date_naissance,
                $genre,
                $hashed_password,
                $photo_name,
            );

            $stmt->execute();

            // Get the user ID from the insert operation
            $user_id = $stmt->insert_id;
            $_SESSION['last_user']=$user_id;
            echo "User details added successfully. User ID: " . $user_id . "<br>";
            $stmt->close();

            // Update matricule
            $matricule = ($user_id + 1000) . 'PP';
            $update_sql = "UPDATE users SET matricule = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $matricule, $user_id);
            $stmt->execute();
            
            $stmt->close();

            $_SESSION['user_insert']=1;
            echo  "-------------".$_SESSION['last_user']."<br>";


            echo "Matricule updated successfully. Matricule: " . $matricule . "<br>";
        } else {
            // Some required fields are missing, handle the error
            echo "The following required fields are missing: " . implode(", ", $missingFields) . "<br>";

            // Check if user already exists
            $cin = $_POST['cin'];
            $existing_user_sql = "SELECT id FROM users WHERE cin = ?";
            $stmt = $conn->prepare($existing_user_sql);
            $stmt->bind_param("s", $cin);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // User exists, update the existing record
                $stmt->bind_result($user_id);
                $stmt->fetch();
                $stmt->close();

                $update_sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, phone = ?, date_naissance = ?, genre = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param(
                    "ssssssi",
                    $_POST['nom'],
                    $_POST['prenom'],
                    $_POST['email'],
                    $_POST['phone'],
                    $date_naissance,
                    $_POST['genre'],
                    $user_id
                );
                $stmt->execute();
                $stmt->close();

                echo "User details updated successfully. User ID: " . $user_id . "<br>";
            } else {
                throw new Exception("User not found for CIN: " . $cin);
            }
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Failed to insert data: " . $e->getMessage();
    }
}


$conn->autocommit(TRUE); // Return to autocommit mode

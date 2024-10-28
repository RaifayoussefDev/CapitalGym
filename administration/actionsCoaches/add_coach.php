<?php
require "../../inc/conn_db.php";

// Function to generate a secure random password
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    return $password;
}

// Handle file upload for profile photo
function uploadProfilePhoto($file) {
    $target_dir = "../../assets/img/capitalsoft/profils/";
    $file_name = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $base_name = pathinfo($file_name, PATHINFO_FILENAME);
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }

    // Check file size
    if ($file["size"] > 500000) { // 500KB
        throw new Exception("Sorry, your file is too large.");
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        throw new Exception("Sorry, only JPG, JPEG, & PNG files are allowed.");
    }

    // Ensure unique file name
    $new_file_name = $file_name;
    $counter = 1;
    while (file_exists($target_dir . $new_file_name)) {
        $new_file_name = $base_name . "_" . time() . "_" . $counter . "." . $imageFileType;
        $counter++;
    }

    $target_file = $target_dir . $new_file_name;

    // Attempt to move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_file_name; // Return only the new file name
    } else {
        throw new Exception("Sorry, there was an error uploading your file.");
    }
}

// Start transaction
$conn->autocommit(FALSE);

try {
    // Handle profile photo upload
    $photo_name = "";
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $photo_name = uploadProfilePhoto($_FILES["profile_photo"]);
    }

    // Generate a random password
    $password = generateRandomPassword();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    // Insert user details
    $user_sql = "INSERT INTO users (cin, matricule, nom, prenom, email, phone, date_naissance, genre, password, photo, etat, role_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', 2)";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param(
        "ssssssssss",
        $_POST['cin'],
        $_POST['matricule'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['date_naissance'],
        $_POST['genre'],
        $hashed_password,
        $photo_name // Use only the file name
    );
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Insert coach details
    $coach_sql = "INSERT INTO coaches (user_id, activite_id)
                  VALUES (?, ?)";
    $stmt = $conn->prepare($coach_sql);
    $stmt->bind_param("ii", $user_id, $_POST['activite_id']);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Output JavaScript to redirect
    echo "<script>
            window.open('Information.php?id_user=" . $user_id . "', '_blank');
            window.location.href = '../coaches.php';
          </script>";
    exit(); // Ensure no further output

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

// Close connection
$conn->close();
?>

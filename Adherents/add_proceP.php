<?php
session_start();

require "../inc/conn_db.php";

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
        echo "Sorry, only the following file types are allowed: " . implode(', ', $allowed_types);
        return false;
    }

    $target_file = $target_dir . $base_name . '_' . uniqid() . '.' . $file_type;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        echo "Sorry, there was an error uploading your file.";
        return false;
    }
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs to prevent SQL injection
    $cin = mysqli_real_escape_string($conn, $_POST['cin']);
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $date_naissance = $_POST['date_naissance'] ? mysqli_real_escape_string($conn, $_POST['date_naissance']) : null;
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $fonction = mysqli_real_escape_string($conn, $_POST['fonction']);
    $num_urgence = mysqli_real_escape_string($conn, $_POST['num_urgence']);
    $employeur = mysqli_real_escape_string($conn, $_POST['employeur']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    $saissie_par=$_SESSION['id'];
    $password=generateRandomPassword();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password


    // // Upload profile photo
    // if ($_FILES['profile_photo']['name']) {
    //     $target_dir = "uploads/profile_photos/";
    //     $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    //     $profile_photo = uploadFile($_FILES['profile_photo'], $target_dir, $allowed_types);
    // } else {
    //     $profile_photo = null;
    // }

    // Insert data into database
    $sql = "INSERT INTO users (cin, nom, prenom, email, phone, date_naissance, genre, adresse, fonction, num_urgence, employeur , saisie_par , `role_id` , `etat` ,`password`,`Note`)
            VALUES ('$cin', '$nom', '$prenom', '$email', '$phone', '$date_naissance', '$genre', '$adresse', '$fonction', '$num_urgence', '$employeur',$saissie_par,3,'proceP','$hashed_password','$note')";

    if ($conn->query($sql) === TRUE) {
        header('location:./');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

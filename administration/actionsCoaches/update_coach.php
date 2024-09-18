<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle file upload for profile photo
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

// Start session to retrieve user ID if needed
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get coach ID from form
    $coach_id = $_POST['coach_id'];

    try {
        // Handle profile photo upload if provided
        $photo_name = "";
        if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
            $photo_name = uploadProfilePhoto($_FILES["profile_photo"]);
        }

        // Prepare SQL statement to update user and coach details
        $update_user_sql = "UPDATE users u
                            INNER JOIN coaches c ON u.id = c.user_id
                            SET u.cin = ?, u.nom = ?, u.prenom = ?, u.email = ?, u.phone = ?, 
                                u.date_naissance = ?, u.genre = ?, u.photo = ?
                            WHERE c.id = ?";
        $stmt = $conn->prepare($update_user_sql);
        $stmt->bind_param(
            "ssssssssi",
            $_POST['cin'],
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['date_naissance'],
            $_POST['genre'],
            $photo_name,
            $coach_id
        );
        $stmt->execute();
        $stmt->close();

        // Update coach's activite_id if necessary
        $update_coach_sql = "UPDATE coaches SET activite_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_coach_sql);
        $stmt->bind_param("ii", $_POST['activite_id'], $coach_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to a success page or dashboard
        header("Location: ../coaches.php");
        exit();

    } catch (Exception $e) {
        // Handle any exceptions or errors
        echo "Error: " . $e->getMessage();
    }
}

// Close database connection
$conn->close();
?>

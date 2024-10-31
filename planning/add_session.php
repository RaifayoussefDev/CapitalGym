<?php
require "../inc/conn_db.php";

// Start session for messages
session_start();

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $libelle = trim($_POST['libelle']);
    $activity = intval($_POST['activity']);
    $coach = intval($_POST['coach']);
    $location = intval($_POST['location']);
    $maxAttendees = intval($_POST['maxAttendees']);
    $genre = trim($_POST['gender']); // Genre
    $is_repetitive = ($_POST['repetitive'] === 'Repetitive') ? 1 : 0;

    // Handle logo upload
    $logo = $_FILES['logo'];
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 2 * 1024 * 1024; // 2 MB
    $target_dir = "../assets/img/capitalsoft/";
    $target_file = $target_dir . basename($logo['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type and size
    if (!in_array($imageFileType, $allowed_file_types)) {
        $_SESSION['message'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        $_SESSION['message_type'] = "danger";
        header("Location: index.php");
        exit();
    }

    if ($logo['size'] > $max_file_size) {
        $_SESSION['message'] = "File size exceeds the maximum limit of 2 MB.";
        $_SESSION['message_type'] = "danger";
        header("Location: index.php");
        exit();
    }

    // Move uploaded file
    if (!move_uploaded_file($logo['tmp_name'], $target_file)) {
        $_SESSION['message'] = "Error uploading logo.";
        $_SESSION['message_type'] = "danger";
        header("Location: index.php");
        exit();
    }

    // Insert session into sessions table (without max_attendees and remaining_slots)
    $sql = "INSERT INTO sessions (libelle, logo, coach_id, location_id, activite_id, genre, is_repetitive) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "ssiisss",  // Adjust the type string for all 7 variables
            $libelle,        // string
            $target_file,    // string
            $coach,          // int
            $location,       // int
            $activity,       // int
            $genre,          // string
            $is_repetitive   // int
        );

        // Execute the query
        if ($stmt->execute()) {
            $sessionId = $stmt->insert_id; // Get the ID of the newly inserted session

            // Insert session planning data into session_planning table
            $planningSql = "INSERT INTO session_planning (session_id, day, start_time, max_attendees, remaining_slots) VALUES (?, ?, ?, ?, ?)";
            $planningStmt = $conn->prepare($planningSql);

            // Handle repetitive planning (e.g., days of the week)
            if (isset($_POST['days'])) {
                foreach ($_POST['days'] as $day) {
                    $startTime = $_POST[$day . 'Hours']; // Retrieve the selected time slot for the day
                    if (!empty($startTime)) {
                        // Use only start time (no end time)
                        $planningStmt->bind_param("issii", $sessionId, $day, $startTime, $maxAttendees, $maxAttendees);
                        $planningStmt->execute();
                    }
                }
            }

            $planningStmt->close();
            $_SESSION['message'] = "Session added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding session. Please try again.";
            $_SESSION['message_type'] = "danger";
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error preparing the statement.";
        $_SESSION['message_type'] = "danger";
    }

    // Close the connection
    $conn->close();

    // Redirect based on request outcome
    header("Location: index.php");
    exit();
}
?>

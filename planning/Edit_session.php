<?php
require "../inc/conn_db.php";

// Start session for messages
session_start();

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $sessionId = intval($_POST['session_id']); // Session ID to modify
    $libelle = trim($_POST['libelle']);
    $activity = intval($_POST['activity']);
    $coach = intval($_POST['coach']);
    $location = intval($_POST['location']);
    $maxAttendees = intval($_POST['maxAttendees']);
    $genre = trim($_POST['gender']); // Genre
    $is_repetitive = 1;

    // Handle logo upload
    $logo = $_FILES['logo'];
    $target_file = null; // Initialize as null in case no file is uploaded
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 2 * 1024 * 1024; // 2 MB
    $target_dir = "../assets/img/capitalsoft/";

    if (!empty($logo['name'])) {
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
    }

    // Update session details
    $sql = "UPDATE sessions 
            SET libelle = ?, 
                coach_id = ?, 
                location_id = ?, 
                activite_id = ?, 
                genre = ?, 
                is_repetitive = ?" .
        (!empty($target_file) ? ", logo = ?" : "") . " 
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters dynamically
        if (!empty($target_file)) {
            $stmt->bind_param(
                "ssiisssi", // Add 's' for logo
                $libelle,
                $coach,
                $location,
                $activity,
                $genre,
                $is_repetitive,
                $target_file,
                $sessionId
            );
        } else {
            $stmt->bind_param(
                "ssiissi",
                $libelle,
                $coach,
                $location,
                $activity,
                $genre,
                $is_repetitive,
                $sessionId
            );
        }

        // Execute the query
        if ($stmt->execute()) {
            // Update session planning
            $deletePlanningSql = "DELETE FROM session_planning WHERE session_id = ?";
            $deleteStmt = $conn->prepare($deletePlanningSql);
            $deleteStmt->bind_param("i", $sessionId);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Insert updated planning data
            $planningSql = "INSERT INTO session_planning (session_id, day, start_time, end_time , max_attendees, remaining_slots) VALUES (?, ?, ?, ?, ?, ?)";
            $planningStmt = $conn->prepare($planningSql);

            if (!$planningStmt) {
                // Error preparing the statement
                die('MySQL Error: ' . $conn->error);
            }

            $days = ["lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche"];
            $insertSuccess = true;  // To track if all inserts are successful

            foreach ($days as $day) {
                $post = "edit-" . $day . "-hours";  // Formulating the correct post key
                if (isset($_POST[$post])) {
                    $startTime = $_POST[$post];  // Retrieve the selected time slot for the day

                    // Convert start time to DateTime object and add 1 hour
                    $startDateTime = DateTime::createFromFormat('H:i', $startTime); // Assuming the format is HH:MM (24-hour format)
                    if ($startDateTime) {
                        // Add 1 hour to the start time
                        $startDateTime->modify('+1 hour');

                        // Format the end time back to 'H:i' format (HH:MM)
                        $endTime = $startDateTime->format('H:i');
                    } else {
                        // Handle invalid time format
                        die('Invalid start time format');
                    }

                    if (!empty($startTime)) {
                        // Ensure maxAttendees is defined
                        $maxAttendees = isset($_POST['maxAttendees']) ? intval($_POST['maxAttendees']) : 0;
                        $remainingSlots = $maxAttendees; // Assuming remaining slots equals max attendees initially

                        // Bind parameters and execute the statement
                        if (!$planningStmt->bind_param("isssii", $sessionId, $day, $startTime, $endTime, $maxAttendees, $maxAttendees)) {
                            // Error binding parameters
                            die('Binding Error: ' . $planningStmt->error);
                        }

                        if (!$planningStmt->execute()) {
                            // If the execution fails, set insertSuccess to false and break the loop
                            $insertSuccess = false;
                            die('Execution Error: ' . $planningStmt->error); // Show error and terminate execution
                        }
                    }
                }
            }

            $planningStmt->close();

            $_SESSION['message'] = "Session updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating session. Please try again.";
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
    // header("Location: index.php");
    exit();
}

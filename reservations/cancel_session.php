<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "privilage"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Validate and sanitize input
    $session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : null;
    
    // Retrieve user ID from session or authentication system
    if (isset($_SESSION['id'])) {
        $user_id = intval($_SESSION['id']);
    } else {
        die("error: User session not found or invalid.");
    }

    if ($session_id === null) {
        die("error: session_id is missing or invalid.");
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Validate user existence
        $user_check_query = "SELECT id FROM users WHERE id = ?";
        $stmt_user = $conn->prepare($user_check_query);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->store_result();

        if ($stmt_user->num_rows === 0) {
            die("error: User does not exist or invalid user ID.");
        }

        // Prepare SQL statements to delete the reservation
        $stmt_delete = $conn->prepare("DELETE FROM reservations WHERE user_id = ? AND session_id = ?");
        $stmt_delete->bind_param("ii", $user_id, $session_id);

        if (!$stmt_delete->execute()) {
            die("error: Execute failed - " . htmlspecialchars($stmt_delete->error));
        }

        // Update remaining_slots in sessions
        $stmt_update = $conn->prepare("UPDATE sessions SET remaining_slots = remaining_slots + 1 WHERE id = ?");
        $stmt_update->bind_param("i", $session_id);

        if (!$stmt_update->execute()) {
            die("error: Execute failed - " . htmlspecialchars($stmt_update->error));
        }

        // Commit transaction if everything is successful
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $conn->rollback();
        die("error: " . $e->getMessage());
    }

    $stmt_user->close();
    $stmt_delete->close();
    $stmt_update->close();
    $conn->close();
}
?>

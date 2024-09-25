<?php

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the data from the AJAX request
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$balance = isset($_POST['balance']) ? $_POST['balance'] : null;

// Validate input
if ($user_id === null || $balance === null) {
    echo json_encode(['success' => false, 'message' => 'User ID and balance are required.']);
    exit;
}

// Update the user's balance
$update_sql = "UPDATE wallet SET balance = balance + ? WHERE user_id = ?";
$stmt = $conn->prepare($update_sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Update statement preparation failed: ' . $conn->error]);
    exit;
}


$stmt->bind_param("di", $balance, $user_id);

if ($stmt->execute()) {
    // After adding balance, clean the envoi_app table
    $clean_sql = "DELETE FROM envoi_app WHERE user_id = ?";
    $stmt_clean = $conn->prepare($clean_sql);
    
    if (!$stmt_clean) {
        echo json_encode(['success' => false, 'message' => 'Clean statement preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt_clean->bind_param("i", $user_id);
    $stmt_clean->execute();

    echo json_encode(['success' => true, 'message' => 'Balance updated and envoi_app cleaned.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update balance: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

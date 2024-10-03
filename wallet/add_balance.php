<?php

require "../inc/conn_db.php";

// Get the data from the AJAX request
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$balance = isset($_POST['balance']) ? $_POST['balance'] : null;

// Validate input
if ($user_id === null || $balance === null) {
    echo json_encode(['success' => false, 'message' => 'User ID and balance are required.']);
    exit;
}

// Check if the user has a wallet
$check_sql = "SELECT balance FROM wallet WHERE user_id = ?";
$stmt_check = $conn->prepare($check_sql);
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows == 0) {
    // No wallet entry exists for the user, insert a new wallet record
    $insert_sql = "INSERT INTO wallet (user_id, balance) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($insert_sql);
    
    if (!$stmt_insert) {
        echo json_encode(['success' => false, 'message' => 'Insert statement preparation failed: ' . $conn->error]);
        exit;
    }
    
    $stmt_insert->bind_param("id", $user_id, $balance);
    
    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'New wallet created and balance added.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create wallet: ' . $stmt_insert->error]);
    }
    
    $stmt_insert->close();
} else {
    // User already has a wallet, update the balance
    $update_sql = "UPDATE wallet SET balance = balance + ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($update_sql);

    if (!$stmt_update) {
        echo json_encode(['success' => false, 'message' => 'Update statement preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt_update->bind_param("di", $balance, $user_id);

    if ($stmt_update->execute()) {
        // After updating the balance, clean the envoi_app table
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
        echo json_encode(['success' => false, 'message' => 'Failed to update balance: ' . $stmt_update->error]);
    }

    $stmt_update->close();
}

$stmt_check->close();
$conn->close();
?>

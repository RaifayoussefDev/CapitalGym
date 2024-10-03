<?php
require "../inc/conn_db.php";

// Check if the POST request contains the required parameters
if (isset($_POST['cheque_id']) && isset($_POST['status'])) {
    $cheque_id = intval($_POST['cheque_id']); // Ensure cheque_id is an integer
    $new_status = $_POST['status'];

    // Prepare and execute the update statement
    $update_sql = "UPDATE cheque SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    
    // Bind the parameters (status and cheque_id)
    $stmt->bind_param("si", $new_status, $cheque_id);

    if ($stmt->execute()) {
        // If update is successful, return a success response
        echo json_encode([
            'success' => true,
            'chequeId' => $cheque_id,
            'newStatus' => $new_status
        ]);
    } else {
        // If update fails, return an error message
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status.'
        ]);
    }

    // Close the statement
    $stmt->close();
} else {
    // If POST data is missing, return an error message
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Missing parameters.'
    ]);
}

// Close the database connection
$conn->close();
?>

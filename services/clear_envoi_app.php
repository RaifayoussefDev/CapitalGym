<?php

require "../inc/conn_db.php";

// Clear the envoi_app table
$clear_sql = "DELETE FROM envoi_tournique";

if ($conn->query($clear_sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'envoi_app table cleared.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear envoi_app table.']);
}

$conn->close();
?>

<?php
require "../inc/app.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../inc/conn_db.php";

$id_user = $_GET['id_user'];

// Prepare and bind
$stmt = $conn->prepare("UPDATE users SET etat = 'actif' WHERE id = ?");
$stmt->bind_param("i", $id_user);

$response = [];
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Redirect to the index page
    echo "<script>
    window.location.href = 'Index.php';
  </script>";    exit();
} else {
    $response['status'] = 'error';
    $response['message'] = $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>

<?php
// $servername = "51.77.194.236";
// $username = "admin";
// $password = "C@p1t@l$0ft2022"; // Replace with your password
// $dbname = "privilage";

$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $id_card = $_POST['id_card'];

    $stmt = $conn->prepare("UPDATE users SET id_card = ? WHERE id = ?");
    $stmt->bind_param("si", $id_card, $id);

    if ($stmt->execute()) {
        echo "ID Card updated successfully";
    } else {
        echo "Error updating ID Card: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>

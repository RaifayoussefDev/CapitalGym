<?php
$servername = "51.77.194.236";
$username = "admin";
$password = "C@p1t@l$0ft2022";
$dbname = "privilage";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (!empty($id)) {
        // Update id_card to NULL for the specified user
        $stmt = $conn->prepare("UPDATE users SET id_card = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Badge removed successfully";
        } else {
            echo "Error removing badge: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Invalid user ID.";
    }
}

$conn->close();
?>

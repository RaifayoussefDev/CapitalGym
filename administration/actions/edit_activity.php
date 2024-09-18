<?php
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $sex = $conn->real_escape_string($_POST['sex']);

    $sql = "UPDATE activites SET nom='$nom', prix=$prix, sex='$sex' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href = '../index.php?msg=edit_success';</script>";
    } else {
        echo "<script>window.location.href = '../index.php?msg=edit_error';</script>";
    }
}

$conn->close();
?>

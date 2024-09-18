<?php
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $sql = "DELETE FROM materiel WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../materiels.php?msg=success");
    } else {
        header("Location: ../materiels.php?msg=error");
    }
}

$conn->close();
?>

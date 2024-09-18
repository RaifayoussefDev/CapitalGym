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
    $id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);

    $sql = "UPDATE locations SET name='$name' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../local.php?msg=success");
    } else {
        header("Location: ../local.php?msg=error");
    }
}

$conn->close();
?>

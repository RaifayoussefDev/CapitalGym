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
    $nom = $_POST['nom'];
    $emplacement = $_POST['emplacement'];
    $date_achat = $_POST['date_achat'];

    $sql = "UPDATE materiel SET nom='$nom', emplacement='$emplacement', date_achat='$date_achat' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../materiels.php?msg=success");
    } else {
        header("Location: ../materiels.php?msg=error");
    }
}

$conn->close();
?>

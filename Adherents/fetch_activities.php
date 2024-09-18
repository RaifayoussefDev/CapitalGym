<?php
$servername = "localhost";
$username = "root";
$password = ""; // Remplacez par votre mot de passe
$dbname = "privilage"; // Remplacez par le nom de votre base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$genre = $_GET['genre'];

// Préparer la requête pour récupérer les activités en fonction du genre
$sql = "SELECT id, nom FROM activites WHERE sex='$genre' OR sex='MF'";
$result = $conn->query($sql);

$activities = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

$conn->close();

// Retourner les activités sous forme de JSON
header('Content-Type: application/json');
echo json_encode($activities);
?>

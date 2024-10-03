<?php
require "../inc/conn_db.php";

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

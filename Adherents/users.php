<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "privilage";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all users
$sql = "SELECT u.id , etat , nom , prenom , matricule , email , phone , cin , photo , pack_name , date_fin from users u, abonnements a , packages p WHERE u.id=a.user_id and p.id=a.type_abonnement;";
$result = $conn->query($sql);

$users = [];
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return users as JSON
header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>

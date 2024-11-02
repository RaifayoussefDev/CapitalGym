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

$sql = "SELECT users.id, nom, prenom, email, phone, id_card, type_abonnement 
        FROM users 
        JOIN abonnements ON abonnements.user_id = users.id 
        WHERE role_id = 3";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
echo json_encode(["data" => $data]);

$conn->close();
?>

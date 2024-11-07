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

$sql = "SELECT users.id, users.nom, users.prenom, users.email, users.phone, users.id_card, abonnements.type_abonnement, COALESCE(GROUP_CONCAT(activites.nom SEPARATOR ', '), '') AS activites FROM users JOIN abonnements ON abonnements.user_id = users.id LEFT JOIN user_activites ON user_activites.user_id = users.id LEFT JOIN activites ON activites.id = user_activites.activite_id WHERE users.role_id = 3 GROUP BY users.id, users.nom, users.prenom, users.email, users.phone, users.id_card, abonnements.type_abonnement;";
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

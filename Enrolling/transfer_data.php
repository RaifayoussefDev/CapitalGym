<?php
require "../test_add_user.php";

$servername = "51.77.194.236";
$username = "admin";
$password = "C@p1t@l$0ft2022"; // Replace with your password
$dbname = "privilage";


// $servername = "localhost";
// $username = "root";
// $password = ""; // Replace with your password
// $dbname = "privilage";



// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users from MySQL
$sql = "SELECT 
    users.id, 
    users.nom, 
    users.prenom, 
    users.email, 
    users.id_card,  
    users.phone, 
    users.CodeQr, 
    abonnements.type_abonnement,  
    activites.nom AS activite_nom
FROM 
    users
LEFT JOIN 
    abonnements ON abonnements.user_id = users.id
LEFT JOIN 
    user_activites ON user_activites.user_id = users.id
LEFT JOIN 
    activites ON activites.id = user_activites.activite_id
WHERE 
    users.role_id = 3;";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["id"];
        $nom = $row['nom'];
        $prenom = $row['prenom'];
        $email = $row['email'];
        $phone = $row['phone'];
        $id_card = $row['id_card'];
        $qrcode = $row['id_card'];
        $type_abonnement = $row['type_abonnement'];
        $activite_nom = $row['activite_nom']; // Assume this column is fetched in your query

        // Determine department based on type_abonnement and CrossFit activity
        if ($type_abonnement == 2 || $type_abonnement == 3) {
            $departement = 19;
        } elseif(($type_abonnement != 2 || $type_abonnement != 3) && $activite_nom == 'Crossfit') {
            $departement = 19;
        }else{
            $departement = 20;
        }

        // Check if $qrcode is not empty
        if (!empty($qrcode)) {
            // Add personnel if QR code exists
            addPersonnel($qrcode, $id_card, $nom, $prenom, $email, $phone, $departement, $id);
        } else {
            $qrcode = $id + 500000;
            addPersonnel($qrcode, $id_card, $nom, $prenom, $email, $phone, $departement, $id);
        }
    }
} else {
    die("Error fetching users from MySQL: " . mysqli_error($conn));
}


// Close the connections
mysqli_close($conn);

<?php
require "../test_add_user.php";

$servername = "51.77.194.236";
$username = "admin";
$password = "C@p1t@l$0ft2022"; // Replace with your password
$dbname = "privilage";

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
    users.role_id = 3 AND id_card NOT LIKE ''";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["id"];
        
        // Truncate to 30 characters
        $nom = substr($row['nom'], 0, 30); 
        $prenom = substr($row['prenom'], 0, 30); // Take only the first word, ensure it's within 30 characters
        $prenom = str_replace("'", "\'", $prenom); // Escape any single quotes
        $prenom = explode(" ", $prenom)[0]; // Take only the first word

        $email = substr($row['email'], 0, 30); // Limit email to 30 characters
        $email = str_replace("'", "\'", $email); // Escape any single quotes

        $phone = substr($row['phone'], 0, 30); // Limit phone number to 30 characters
        $id_card = substr($row['id_card'], 0, 30); // Limit id_card to 30 characters
        $qrcode = $row['id_card'];
        $type_abonnement = $row['type_abonnement'];
        $activite_nom = substr($row['activite_nom'], 0, 30); // Ensure activity name is within 30 characters

        // Determine department based on type_abonnement and CrossFit activity
        if ($type_abonnement == 2 || $type_abonnement == 3) {
            $departement = 19;
        } elseif ($type_abonnement != 2 && $type_abonnement != 3 && $activite_nom == 'CROSSFIT') {
            $departement = 19;
        } elseif($activite_nom != 'CROSSFIT') {
            $departement = 20;
        }
        echo $departement;

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

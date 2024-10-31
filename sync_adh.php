<?php 

require "./inc/conn_db.php";
require "./test_add_user.php";

// Fetch users from MySQL
$sql = "SELECT nom, prenom, email, phone, id_card, CodeQr, abonnements.type_abonnement 
        FROM users 
        JOIN abonnements ON abonnements.user_id = users.id 
        WHERE role_id = 3";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nom = $row['nom'];
        $prenom = $row['prenom'];
        $email = $row['email'];
        $phone = $row['phone'];
        $id_card = $row['id_card'];
        $qrcode = $row['CodeQr'];
        $type_abonnement = $row['type_abonnement'];

        // Determine department based on type_abonnement
        $departement = ($type_abonnement == 2 || $type_abonnement == 3) ? 19 : 20;

        // Insert personnel data into SQL Server
        addPersonnel($qrcode, $id_card, $nom, $prenom, $email, $phone, $departement);
    }
} else {
    die("Error fetching users from MySQL: " . mysqli_error($conn));
}

// Close the connections
mysqli_close($conn);

?>

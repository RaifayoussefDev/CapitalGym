<?php
require "./add_blacklist.php";
require "./remove_blacklist.php";
require "../inc/conn_db.php";

$sql = "SELECT 
    u.id_card, 
    a.date_fin
FROM 
    users u
INNER JOIN 
    abonnements a ON u.id = a.user_id
WHERE 
    a.date_fin = (
        SELECT MAX(a2.date_fin)
        FROM abonnements a2
        WHERE a2.user_id = u.id
    );";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $currentDate = date('Y-m-d'); // Date actuelle pour comparaison
    while ($row = $result->fetch_assoc()) {
        $id_card = $row["id_card"];
        $date_fin = $row["date_fin"];
        
        if ($date_fin < $currentDate) {
            // Ajouter à la liste noire
            updateBlacklistForExpiredSubscriptions($id_card);
        } else {
            // Retirer de la liste noire
            removeBlacklistForExpiredSubscriptions($id_card);
        }
    }
} else {
    echo "Aucun résultat trouvé.";
}

$conn->close();
?>

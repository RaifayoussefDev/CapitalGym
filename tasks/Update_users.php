<?php
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
    )
    AND a.date_fin < CURDATE();";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID Card: " . $row["id_card"] . " - Date Fin: " . $row["date_fin"] . "<br>";
    }
} else {
    echo "0 results found";
}

$conn->close();
;?>

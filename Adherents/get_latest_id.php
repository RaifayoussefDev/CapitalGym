<?php
require "../inc/conn_db.php";

$query = "SELECT MAX(id) AS latest_id FROM users";
$result = $conn->query($query);
$row = $result->fetch_assoc();

echo json_encode(['latest_id' => $row['latest_id']]);
;?>
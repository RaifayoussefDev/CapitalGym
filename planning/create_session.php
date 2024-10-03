<?php
require "../inc/app.php";

require "../inc/conn_db.php";

// Get form data
$activite_id = $_POST['activite_id'];
$coach_id = $_POST['coach_id'];
$location_id = $_POST['location_id'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$max_attendees = $_POST['max_attendees'];
$id = isset($_POST['id']) ? $_POST['id'] : null;

if ($id) {
    // Edit session
    $sql = "UPDATE sessions SET 
                activite_id = '$activite_id', 
                coach_id = '$coach_id', 
                location_id = '$location_id', 
                date = '$date', 
                start_time = '$start_time', 
                end_time = '$end_time', 
                max_attendees = '$max_attendees' 
            WHERE id = '$id'";
    $message = 'update';
} else {
    // Create new session
    $sql = "INSERT INTO sessions (activite_id, coach_id, location_id, date, start_time, end_time, max_attendees) 
            VALUES ('$activite_id', '$coach_id', '$location_id', '$date', '$start_time', '$end_time', '$max_attendees')";
    $message = 'create';
}

if ($conn->query($sql) === TRUE) {
    header("Location: index.php?date=$date&msg=success&action=$message");
} else {
    header("Location: index.php?date=$date&msg=error&action=$message");
}

$conn->close();
?>

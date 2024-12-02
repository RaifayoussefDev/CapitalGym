<?php
// get_schedule.php
require "../inc/app.php";
require "../inc/conn_db.php";

$day = isset($_GET['day']) ? $_GET['day'] : 'lundi'; // Get the day from the query parameter

// SQL query to fetch schedule for the given day
$sql = "
SELECT time, activity
FROM schedule
WHERE day = ?
";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $day); // Bind the day parameter
$stmt->execute();
$result = $stmt->get_result();

$schedule = [];
while ($row = $result->fetch_assoc()) {
    $schedule[] = $row; // Store the schedule items
}

$response = [
    'success' => true,
    'schedule' => $schedule
];

if (empty($schedule)) {
    $response['success'] = false;
}

// Return the response as JSON
echo json_encode($response);
$conn->close();
?>

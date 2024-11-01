<?php
// Database connection
require "../inc/conn_db.php";

// Fetch all user IDs from the users table
$sql = "SELECT id FROM users where role_id = 3";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['id'];
        $new_id_card = $user_id + 50000;

        // Update id_card for each user
        $update_sql = "UPDATE users SET id_card = '$new_id_card' WHERE id = '$user_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "Updated id_card for user ID: $user_id to $new_id_card<br>";
        } else {
            echo "Error updating id_card for user ID: $user_id - " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Error fetching users: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?>

<?php

require "./inc/conn_db.php";
// Fetch all users with role_id = 3
$sql = "SELECT id FROM users WHERE role_id = 3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row['id'];
        
        // Generate 8-digit CodeQR: user id + random numbers
        $randomNumber = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $CodeQR = str_pad($userId, 3, '0', STR_PAD_LEFT) . $randomNumber;
        
        // Update the CodeQR field
        $updateSql = "UPDATE users SET CodeQR = '$CodeQR' WHERE id = $userId";
        
        if ($conn->query($updateSql) === TRUE) {
            echo "User ID $userId: CodeQR updated to $CodeQR<br>";
        } else {
            echo "Error updating user ID $userId: " . $conn->error . "<br>";
        }
    }
} else {
    echo "No users found with role_id = 3.";
}

// Close the database connection
$conn->close();
?>

<?php
// Database connection
require "../inc/conn_db.php";

// Fetch all user IDs from the users table
$sql = "SELECT id FROM users where role_id = 3";
$result = mysqli_query($conn, $sql);

if ($result) {
    // Current timestamp for created_at, updated_at, and transaction_date fields
    $timestamp = date("Y-m-d H:i:s");

    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['id'];
        $balance = 20.00;

        // Insert balance into the wallet table for each user
        $insert_wallet_sql = "INSERT INTO wallet (user_id, balance, created_at, updated_at) 
                              VALUES ('$user_id', '$balance', '$timestamp', '$timestamp')";

        if (mysqli_query($conn, $insert_wallet_sql)) {
            // Get the last inserted wallet ID
            $wallet_id = mysqli_insert_id($conn);

            // Prepare transaction details
            $amount = $balance;
            $transaction_type = "credit";
            $description = "Cadeau de privilège";

            // Insert a record in the transaction_wallet table
            $insert_transaction_sql = "INSERT INTO transaction_wallet (wallet_id, amount, transaction_type, transaction_date, description) 
                                       VALUES ('$wallet_id', '$amount', '$transaction_type', '$timestamp', '$description')";

            if (mysqli_query($conn, $insert_transaction_sql)) {
                echo "Transaction added for wallet ID: $wallet_id<br>";
            } else {
                echo "Error adding transaction for wallet ID: $wallet_id - " . mysqli_error($conn) . "<br>";
            }

            echo "Wallet entry added for user ID: $user_id<br>";
        } else {
            echo "Error adding wallet entry for user ID: $user_id - " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Error fetching user IDs: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?>

<?php
// SQL Server connection configuration
$serverName = "51.77.194.236";
$connectionOptions = [
    "Database" => "Card3500",
    "UID" => "sa",
    "PWD" => "azerty+123456",
    "Encrypt" => false,
    "TrustServerCertificate" => true
];

// Function to update the blacklist for users whose subscription has expired
function updateBlacklistForExpiredSubscriptions($code_id)
{
    global $serverName, $connectionOptions;

    // Establish connection to SQL Server
    $connsrv = sqlsrv_connect($serverName, $connectionOptions);
    if ($connsrv === false) {
        die("Connection failed: " . print_r(sqlsrv_errors(), true));
    }


    $userId = $row['id'];
    $updateBlacklistSql = "
            UPDATE [dbo].[Personnel]
            SET IsBlacklist = 1
            WHERE CardData = ?;
        ";

    $stmtUpdate = sqlsrv_query($connsrv, $updateBlacklistSql, $code_id);
    if ($stmtUpdate === false) {
        die("Error updating blacklist for user ID $userId: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "User with ID $userId has been added to the blacklist.<br>";
    }
}

// Call the function to update the blacklist
updateBlacklistForExpiredSubscriptions();

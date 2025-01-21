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
function removeBlacklistForExpiredSubscriptions($code_id)
{
    global $serverName, $connectionOptions;

    // Establish connection to SQL Server
    $connsrv = sqlsrv_connect($serverName, $connectionOptions);
    if ($connsrv === false) {
        die("Connection failed: " . print_r(sqlsrv_errors(), true));
    }

    // Prepare the update query with parameter placeholder
    $updateBlacklistSql = "
        UPDATE [dbo].[Personnel]
        SET IsBlacklist = 0
        WHERE CardData = ?;
    ";

    // Bind the parameter and execute the query
    $stmtUpdate = sqlsrv_query($connsrv, $updateBlacklistSql, array(&$code_id));
    if ($stmtUpdate === false) {
        die("Error updating blacklist for code ID $code_id: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "User with code ID $code_id has been added to the blacklist.<br>";
    }

    // Close the connection
    sqlsrv_close($connsrv);
}
?>

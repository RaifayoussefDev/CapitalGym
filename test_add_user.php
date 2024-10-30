<?php
// SQL Server connection configuration
$serverName = "DESKTOP-EE9DAKJ";
$connectionOptions = [
    "Database" => "Card3500",
    "UID" => "sa",
    "PWD" => "azerty+123456",
    "Encrypt" => false,
    "TrustServerCertificate" => true
];

// Establish connection to SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
} else {
    echo "Connection to SQL Server successful!<br>";
}

// CardCode to check
$cardCode = '4468611';

// Check if CardCode already exists in CardList
$checkCardSql = "SELECT COUNT(*) AS count FROM [dbo].[CardList] WHERE [CardData] = ?";
$checkCardParams = [$cardCode];
$checkStmt = sqlsrv_query($conn, $checkCardSql, $checkCardParams);

if ($checkStmt === false) {
    die("Error checking CardCode: " . print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
if ($row['count'] > 0) {
    echo "CardCode already exists in CardList. No insertion performed.<br>";
} else {
    // Prepare the INSERT statement for Personnel
    $insertPersonnelSql = "INSERT INTO [dbo].[Personnel]
               ([PName], [PCode], [CardData], [CardCode], [CardData_Backup], [CardCode_Backup],
               [PPassword], [Sex], [DepartmentID], [Job], [Nation], [Country], [NativePlace],
               [Birthday], [IdentityCard], [IdentityCardType], [Study], [Degree], [GraduateSchool],
               [GraduateTime], [Technical], [MobilePhone], [Addr], [EMail], [PDesc], [PImage],
               [InputUser], [InputSystemTime], [BackupIsFingerprint], [FPUserID], [IsBlacklist])
         VALUES
               (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Parameters for Personnel
    $params = [
        'TEST APPLICATION', // PName
        '20000',            // PCode
        $cardCode,          // CardData
        $cardCode,          // CardCode
        $cardCode,          // CardData_Backup
        $cardCode,          // CardCode_Backup
        '',                 // PPassword
        1,                  // Sex
        2,                  // DepartmentID
        'Manager',          // Job
        'Morocco',          // Nation
        'Other',            // Country
        'Other',            // NativePlace
        39005,              // Birthday
        '',                 // IdentityCard
        'National ID',      // IdentityCardType
        "Bachelor's",       // Study
        'Business',         // Degree
        '',                 // GraduateSchool
        2020,               // GraduateTime
        'Yes',              // Technical
        '',                 // MobilePhone
        '',                 // Addr
        '',                 // EMail
        '',                 // PDesc
        1,                  // PImage
        12345.6789012345,   // InputUser
        time(),             // InputSystemTime (use current timestamp)
        1,                  // BackupIsFingerprint
        0,                  // FPUserID
        0                   // IsBlacklist
    ];

    // Execute the Personnel insertion
    $stmt = sqlsrv_query($conn, $insertPersonnelSql, $params);

    if ($stmt === false) {
        die("Error in Personnel statement execution: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "Personnel record inserted successfully!<br>";

        // Get the last inserted Personnel ID
        $sql = "select MAX(Personnel.PersonnelID) As last_id  from Personnel";
        $result = sqlsrv_query($conn, $sql);

        if ($result === false) {
            die("Error retrieving last inserted Personnel ID: " . print_r(sqlsrv_errors(), true));
        }

        $lastIdRow = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        $personnelId = $lastIdRow['last_id'];

        if ($personnelId === null) {
            die("Error: Personnel ID is null. Insert into CardList cannot proceed.");
        }

        // Prepare the INSERT statement for CardList
        $insertCardListSql = "INSERT INTO [dbo].[CardList]
                   ([CardCode], [CardData], [CardStatus], [HstryTime], [PersonnelID], [ICWriteCard])
             VALUES (?, ?, ?, ?, ?, ?)";

        $hstryTime = time(); // or use a more appropriate datetime format

        $cardListParams1 = [
            $cardCode,          // CardCode (from CardData)
            $cardCode,          // CardData
            1,                  // CardStatus
            $hstryTime,        // HstryTime
            $personnelId,       // PersonnelID
            1                   // ICWriteCard
        ];

        // $cardListParams2 = [
        //     $cardCode,          // CardCode (from CardData_Backup)
        //     $cardCode,          // CardData_Backup
        //     1,                  // CardStatus
        //     $hstryTime,        // HstryTime
        //     $personnelId,       // PersonnelID
        //     1                   // ICWriteCard
        // ];

        // Insert CardData into CardList
        $stmtCardList1 = sqlsrv_query($conn, $insertCardListSql, $cardListParams1);
        if ($stmtCardList1 === false) {
            die("Error in CardList statement execution for CardData: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "CardList record for CardData inserted successfully!<br>";
        }

        // Insert CardData_Backup into CardList
        $stmtCardList2 = sqlsrv_query($conn, $insertCardListSql, $cardListParams2);
        if ($stmtCardList2 === false) {
            die("Error in CardList statement execution for CardData_Backup: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "CardList record for CardData_Backup inserted successfully!<br>";
        }
    }
}

// Close the connection
sqlsrv_close($conn);
?>

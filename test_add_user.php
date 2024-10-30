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

// Prepare the INSERT statement for Personnel
$insertPersonnelSql = "INSERT INTO [dbo].[Personnel]
           ([PName], [PCode], [CardData], [CardCode], [CardData_Backup], [CardCode_Backup],
           [PPassword], [Sex], [DepartmentID], [Job], [Nation], [Country], [NativePlace],
           [Birthday], [IdentityCard], [IdentityCardType], [Study], [Degree], [GraduateSchool],
           [GraduateTime], [Technical], [MobilePhone], [Addr], [EMail], [PDesc], [PImage],
           [InputUser], [InputSystemTime], [BackupIsFingerprint], [FPUserID], [IsBlacklist])
     VALUES
           (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare and bind parameters for the Personnel record
$params = [
    'TEST APPLICATION', // PName
    '20000',            // PCode
    '9627832',          // CardData
    '9627832',          // CardCode
    '9627832',          // CardData_Backup
    '9627832',          // CardCode_Backup
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
    12345.6789012345,   // InputSystemTime
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
    
    // Get the last inserted ID for Personnel to use as PersonnelID in CardList
    $sql = "SELECT SCOPE_IDENTITY() AS last_id";
    $result = sqlsrv_query($conn, $sql);
    $lastIdRow = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    $personnelId = $lastIdRow['last_id'];

    // Prepare the INSERT statement for CardList with CardData and CardData_Backup
    $insertCardListSql = "INSERT INTO [dbo].[CardList]
               ([CardCode], [CardData], [CardStatus], [HstryTime], [PersonnelID], [ICWriteCard])
         VALUES (?, ?, ?, ?, ?, ?)";
    
    $cardListParams1 = [
        '9627832',     // CardCode (from CardData)
        '9627832',     // CardData
        1,             // CardStatus (e.g., 1 for Active)
        12345.6789,    // HstryTime (timestamp or float)
        $personnelId,  // PersonnelID from the last inserted Personnel record
        1              // ICWriteCard (e.g., 1 for writable)
    ];

    $cardListParams2 = [
        '9627832',     // CardCode (from CardData_Backup)
        '9627832',     // CardData_Backup
        1,             // CardStatus (e.g., 1 for Active)
        12345.6789,    // HstryTime (timestamp or float)
        $personnelId,  // PersonnelID from the last inserted Personnel record
        1              // ICWriteCard (e.g., 1 for writable)
    ];

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

// Close the connection
sqlsrv_close($conn);
?>

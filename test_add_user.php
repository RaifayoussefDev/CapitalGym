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

// Define the function to add a personnel entry
function addPersonnel($qrcode, $id_card, $nom, $prenom, $email, $phone, $departement)
{
    global $serverName, $connectionOptions;

    // Establish connection to SQL Server
    $connsrv = sqlsrv_connect($serverName, $connectionOptions);
    if ($connsrv === false) {
        die("Connection failed: " . print_r(sqlsrv_errors(), true));
    }

    // Check if CardCode already exists in CardList
    $checkCardSql = "SELECT COUNT(*) AS count FROM [dbo].[CardList] WHERE [CardData] = ?";
    $checkStmt = sqlsrv_query($connsrv, $checkCardSql, [$qrcode]);
    if ($checkStmt === false) {
        die("Error checking CardCode: " . print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
    if ($row['count'] > 0) {
        echo "CardCode already exists in CardList. No insertion performed.<br>";
        return;
    }

    // Prepare the INSERT statement for Personnel
    $insertPersonnelSql = "INSERT INTO [dbo].[Personnel]
        ([PName], [PCode], [CardData], [CardCode], [CardData_Backup], [CardCode_Backup],
        [PPassword], [Sex], [DepartmentID], [Job], [Nation], [Country], [NativePlace],
        [Birthday], [IdentityCard], [IdentityCardType], [Study], [Degree], [GraduateSchool],
        [GraduateTime], [Technical], [MobilePhone], [Addr], [EMail], [PDesc], [PImage],
        [InputUser], [InputSystemTime], [BackupIsFingerprint], [FPUserID], [IsBlacklist])
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Parameters for Personnel
    $params = [
        "$nom $prenom",     // PName
        $id_card,           // PCode
        $qrcode,            // CardData
        $qrcode,            // CardCode
        $qrcode,            // CardData_Backup
        $qrcode,            // CardCode_Backup
        '',                 // PPassword
        1,                  // Sex
        $departement,       // DepartmentID
        'Adherent',         // Job
        'Morocco',          // Nation
        'Other',            // Country
        'Other',            // NativePlace
        39005,              // Birthday (placeholder)
        '',                 // IdentityCard
        'National ID',      // IdentityCardType
        "Bachelor's",       // Study
        'Business',         // Degree
        '',                 // GraduateSchool
        2020,               // GraduateTime
        'Yes',              // Technical
        $phone,             // MobilePhone
        '',                 // Addr
        $email,             // EMail
        '',                 // PDesc
        '',                 // PImage
        12345.6789,         // InputUser
        time(),             // InputSystemTime (timestamp)
        1,                  // BackupIsFingerprint
        0,                  // FPUserID
        0                   // IsBlacklist
    ];

    $stmt = sqlsrv_query($connsrv, $insertPersonnelSql, $params);
    if ($stmt === false) {
        die("Error in Personnel statement execution: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "Personnel record inserted successfully!<br>";

        $sql = "SELECT MAX(Personnel.PersonnelID) AS last_id FROM Personnel";
        $result = sqlsrv_query($connsrv, $sql);
        $lastIdRow = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        $personnelId = $lastIdRow['last_id'];

        // CardList insertion
        $insertCardListSql = "INSERT INTO [dbo].[CardList] 
            ([CardCode], [CardData], [CardStatus], [HstryTime], [PersonnelID], [ICWriteCard]) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $cardListParams = ['0049', $qrcode, 1, 45596.433645833335, $personnelId, 0];
        $stmtCardList = sqlsrv_query($connsrv, $insertCardListSql, $cardListParams);

        if ($stmtCardList === false) {
            die("Error in CardList insertion: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "CardList record inserted successfully!<br>";
        }

        // EmplOfEqupt insertion
        $insertEmplOfEquptSql = "INSERT INTO [dbo].[EmplOfEqupt]
            ([PersonnelID], [EquptID], [PermitTime], [ReadCount], [CardMode], 
            [HldEnabled], [DownloadState], [InOutState_Port1], [InOutState_Date_Port1], 
            [InOutState_Port2], [InOutState_Date_Port2], [InOutState_Port3], 
            [InOutState_Date_Port3], [InOutState_Port4], [InOutState_Date_Port4], 
            [TimePieceIndex], [OpenLock], [HldPwr], [UserType])
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CONVERT(binary(4), ?), CONVERT(binary(4), ?), CONVERT(binary(4), ?), ?)";
        
        $emplOfEquptParams = [
            $personnelId,        // PersonnelID
            1007,                // EquptID
            68849.999305555553,  // PermitTime
            65535,               // ReadCount
            0,                   // CardMode
            0,                   // HldEnabled
            0,                   // DownloadState
            0,                   // InOutState_Port1
            0.0,                 // InOutState_Date_Port1
            0,                   // InOutState_Port2
            0.0,                 // InOutState_Date_Port2
            0,                   // InOutState_Port3
            0.0,                 // InOutState_Date_Port3
            0,                   // InOutState_Port4
            0.0,                 // InOutState_Date_Port4
            hex2bin("00000000"), // TimePieceIndex (converted to binary)
            hex2bin("00000000"), // OpenLock (converted to binary)
            hex2bin("00000000"), // HldPwr (converted to binary)
            0                    // UserType
        ];

        $stmtEmplOfEqupt = sqlsrv_query($connsrv, $insertEmplOfEquptSql, $emplOfEquptParams);
        if ($stmtEmplOfEqupt === false) {
            die("Error in EmplOfEqupt statement execution: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "EmplOfEqupt record inserted successfully!<br>";
        }
    }
    sqlsrv_close($connsrv);
}

// Example usage of the function
addPersonnel('96891', '20000', 'John', 'Doe', 'johndoe@example.com', '1234567890', 19);
?>

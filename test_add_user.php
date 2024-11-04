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

// Define the function to add a personnel entry
function addPersonnel($qrcode, $id_card, $nom, $prenom, $email, $phone, $departement, $id)
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
    $pcode = $id + 10000;
    // Parameters for Personnel
    $params = [
        "$nom $prenom",     // PName
        $pcode,           // PCode
        $qrcode,            // CardData
        '000' . $id,            // CardCode
        '',            // CardData_Backup
        '',            // CardCode_Backup
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
        1,             // InputSystemTime (timestamp)
        0,                  // BackupIsFingerprint
        $pcode,                  // FPUserID
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
        // Define the QR code with leading zeros

        $insertPersonnelExtendSql = "INSERT INTO [dbo].[PersonnelExtend2]
           ([PersonnelID], [CarNumber], [KinsfolkName], [KinsfolkTel], [Kinsfolk], [DefineFields])
     VALUES (?, ?, ?, ?, ?, ?)";

        // Parameters array
        $params = [$personnelId, '', '', '', '', NULL];

        // Execute the query
        $stmtPersonnelExtend = sqlsrv_query($connsrv, $insertPersonnelExtendSql, $params);

        // Check if insertion was successful
        if ($stmtPersonnelExtend === false) {
            die("Error in PersonnelExtend2 insertion: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "PersonnelExtend2 record inserted successfully!<br>";
        }

        $insertCardListSql = "INSERT INTO [dbo].[CardList] 
            ([CardCode], [CardData], [CardStatus], [HstryTime], [PersonnelID], [ICWriteCard]) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $cardListParams = ['000' . $id, $qrcode, 1, 45596.433645833335, $personnelId, 0];
        $stmtCardList = sqlsrv_query($connsrv, $insertCardListSql, $cardListParams);

        if ($stmtCardList === false) {
            die("Error in CardList insertion: " . print_r(sqlsrv_errors(), true));
        } else {
            echo "CardList record inserted successfully with QR code $qrcode!<br>";
        }

        // EmplOfEqupt insertion SQL statement
        $insertEmplOfEquptSql = "INSERT INTO [dbo].[EmplOfEqupt]
([PersonnelID], [EquptID], [PermitTime], [ReadCount], [CardMode], 
[HldEnabled], [DownloadState], [InOutState_Port1], [InOutState_Date_Port1], 
[InOutState_Port2], [InOutState_Date_Port2], [InOutState_Port3], 
[InOutState_Date_Port3], [InOutState_Port4], [InOutState_Date_Port4], 
[TimePieceIndex], [OpenLock], [HldPwr], [UserType])
VALUES
(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CONVERT(binary(4), ?), CONVERT(binary(4), ?), CONVERT(binary(4), ?), ?)";

        // Define base parameters for insertion
        $emplOfEquptParamsBase = [
            $personnelId,        // PersonnelID
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
            // "0x01010000", // TimePieceIndex (converted to binary)
            // "0x01010000", // OpenLock (converted to binary)
            // "0x00000000", // HldPwr (converted to binary)
            pack('A*', 'U'),
            pack('A*', 'P'),
            pack('A*', ''),
            0                    // UserType
        ];

        // Array to store multiple insert parameters
        $inserts = [];

        // Check if departement is 19 to determine how many rows to insert
        if ($departement == 19) {
            // Add two records with EquptID 1017 and 1021
            $inserts[] = array_merge([$emplOfEquptParamsBase[0], 1017], array_slice($emplOfEquptParamsBase, 1));
            $inserts[] = array_merge([$emplOfEquptParamsBase[0], 1021], array_slice($emplOfEquptParamsBase, 1));
        } else {
            // Add only one record with EquptID 1017
            $inserts[] = array_merge([$emplOfEquptParamsBase[0], 1017], array_slice($emplOfEquptParamsBase, 1));
        }

        // Execute each insertion
        foreach ($inserts as $params) {
            $stmtEmplOfEqupt = sqlsrv_query($connsrv, $insertEmplOfEquptSql, $params);
            if ($stmtEmplOfEqupt === false) {
                die("Error in EmplOfEqupt statement execution: " . print_r(sqlsrv_errors(), true));
            } else {
                echo "EmplOfEqupt record with EquptID " . $params[1] . " inserted successfully!<br>";
            }
        }
    }
    sqlsrv_close($connsrv);
}

// Example usage of the function

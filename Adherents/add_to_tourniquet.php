<?php


/**
 * Insert a new personnel record into the database.
 *
 * @param string $nom          The name of the personnel.
 * @param string $prenom       The surname of the personnel.
 * @param string $matricule       The surname of the personnel.
 * @param string $email        The email address of the personnel.
 * @param string $telephone     The phone number of the personnel.
 * @param string $cardid       The card ID.
 * @param string $cardcode     The card code.
 * @param int $genre           The gender (1 for Male, 0 for Female).
 *
 * @return void
 */
function insertPersonnel($nom, $prenom, $email, $matricule, $telephone, $cardid, $cardcode, $genre)
{
    // SQL Server connection configuration
    $serverName = "DESKTOP-EE9DAKJ";
    $connectionOptions = [
        "Database" => "Card3500", // Replace with the name of your SQL Server database
        "UID" => "sa",            // Username
        "PWD" => "azerty+123456", // Password
        "Encrypt" => false,       // Encryption setting (optional)
        "TrustServerCertificate" => true // Trusts the server certificate if using encryption
    ];

    // Establish connection to SQL Server
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        // Handle connection errors
        die("Connection failed: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "Connection to SQL Server successful!<br>";
    }
    // Prepare the INSERT statement with direct values
    $sql = "INSERT INTO [dbo].[Personnel]
               ([PName], [PCode], [CardData], [CardCode], [CardData_Backup], [CardCode_Backup],
                [PPassword], [Sex], [DepartmentID], [Job], [Nation], [Country], [NativePlace],
                [Birthday], [IdentityCard], [IdentityCardType], [Study], [Degree], [GraduateSchool],
                [GraduateTime], [Technical], [MobilePhone], [Addr], [EMail], [PDesc], [PImage],
                [InputUser], [InputSystemTime], [BackupIsFingerprint], [FPUserID], [IsBlacklist])
         VALUES (
               '$nom $prenom',                -- PName
               '$matricule',               -- PCode (you can modify this as needed)
               '$cardid',            -- CardData
               '$cardcode',          -- CardCode
               0,                    -- CardData_Backup
               '',                   -- CardCode_Backup
               '',                   -- PPassword
               $genre,               -- Sex (1 for Male, 0 for Female)
               2,                    -- DepartmentID (set default value)
               'Manager',            -- Job (set default value)
               'Morroco',            -- Nation (set default value)
               'Casablanca',         -- Country (set default value)
               'Other',              -- NativePlace (set default value)
               39005,                -- Birthday (set a default value)
               '',                   -- IdentityCard
               'National ID',        -- IdentityCardType
               'Bachelors',          -- Study
               'Business',           -- Degree
               '',                   -- GraduateSchool
               2020,                 -- GraduateTime (set a default value)
               'Yes',                -- Technical (set default value)
               '$telephone',         -- MobilePhone
               '',                   -- Addr
               '$email',             -- EMail
               '',                   -- PDesc
               1,                    -- PImage (assuming it's a binary or flag, set default value)
               12345.6789012345,     -- InputUser (a user ID or reference, set default value)
               12345.6789012345,     -- InputSystemTime (using current time)
               1,                    -- BackupIsFingerprint (1 for Yes, 0 for No)
               0,                    -- FPUserID (fingerprint user ID)
               0                     -- IsBlacklist (0 for No, 1 for Yes)
         )";

    // Execute the INSERT statement
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        // Handle execution errors
        die("Error in statement execution: " . print_r(sqlsrv_errors(), true));
    } else {
        echo "Record inserted successfully!<br>";
    }
}

// Example usage of the insert function
insertPersonnel('John', 'Doe', 'john.doe@example.com', 'J1001', '0123456789', '1234567890', '1234567890', 1);

// Close the connection
sqlsrv_close($conn);

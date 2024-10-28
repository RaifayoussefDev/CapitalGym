<?php
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
           ([PName]
           ,[PCode]
           ,[CardData]
           ,[CardCode]
           ,[CardData_Backup]
           ,[CardCode_Backup]
           ,[PPassword]
           ,[Sex]
           ,[DepartmentID]
           ,[Job]
           ,[Nation]
           ,[Country]
           ,[NativePlace]
           ,[Birthday]                
           ,[IdentityCard]
           ,[IdentityCardType]
           ,[Study]
           ,[Degree]
           ,[GraduateSchool]          
           ,[GraduateTime]            
           ,[Technical]
           ,[MobilePhone]
           ,[Addr]
           ,[EMail]
           ,[PDesc]
           ,[PImage]
           ,[InputUser]
           ,[InputSystemTime]          -- Added this line
           ,[BackupIsFingerprint]
           ,[FPUserID]
           ,[IsBlacklist])
     VALUES
           ('ahmed ali',                -- PName
           '20000',                    -- PCode
           '1234567890',               -- CardData
           '1234567890',               -- CardCode
           0,                          -- CardData_Backup
           '',                         -- CardCode_Backup
           '',                         -- PPassword
           1,                          -- Sex (1 for Male, 0 for Female)
           2,                          -- DepartmentID
           'Manager',                  -- Job
           'Ivory Coast',              -- Nation
           'Other',                    -- Country
           'Other',                    -- NativePlace
           39005,                      -- Birthday (float format)
           '',                         -- IdentityCard
           'National ID',              -- IdentityCardType
           'Bachelor''s',              -- Study
           'Business',                 -- Degree
           '',                         -- GraduateSchool
           2020,                       -- GraduateTime (year)
           'Yes',                      -- Technical (Yes or No)
           '',                         -- MobilePhone
           '',                         -- Addr
           '',                         -- EMail
           '',                         -- PDesc
           1,                          -- PImage (assuming it's a binary or flag)
           12345.6789012345,          -- InputUser (a user ID or reference)
           12345.6789012345,          -- InputSystemTime (using CURRENT_TIMESTAMP for current time)
           1,                          -- BackupIsFingerprint (1 for Yes, 0 for No)
           0,                          -- FPUserID (fingerprint user ID)
           0)                          -- IsBlacklist (0 for No, 1 for Yes)
";

// Execute the INSERT statement
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    // Handle execution errors
    die("Error in statement execution: " . print_r(sqlsrv_errors(), true));
} else {
    echo "Record inserted successfully!<br>";
}

// Close the connection
sqlsrv_close($conn);
?>

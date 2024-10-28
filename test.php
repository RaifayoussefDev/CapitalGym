<?php
// Function to generate a secure random password (optional)
function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }
    return $password;
}

// Hashing Part
$password = 'Raifa98@@'; // Example password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashed_password . "<br>";
echo "Hashed no Password: " . $password . "<br>";

// Verification Part (Simulating a login attempt)
if (password_verify($password, $hashed_password)) {
    echo "\nPassword is valid!";
} else {
    echo "\nInvalid password.";
}
?>

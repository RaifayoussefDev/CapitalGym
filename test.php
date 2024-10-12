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
$password = 'Admin.privilege@@'; // Example password
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
<script>
    function calculerCalories(km, activite, poids) {
        let caloriesParKm;

        // Déterminer les calories brûlées par km en fonction de l'activité
        if (activite === 'marche') {
            caloriesParKm = 0.035 * poids; // Facteur moyen pour la marche
        } else if (activite === 'course') {
            caloriesParKm = 0.075 * poids; // Facteur moyen pour la course
        } else {
            return "Type d'activité non reconnu.";
        }

        // Calcul des calories totales
        let caloriesTotal = km * caloriesParKm;

        return caloriesTotal;
    }

    // Exemple d'utilisation
    let kmParcourus = 5; // Exemple de 5 km
    let activite = 'course'; // Activité de course
    let poids = 70; // Exemple de poids en kg
    console.log(`Calories consommées : ${calculerCalories(kmParcourus, activite, poids)} cal`);
</script>
<?php
// Inclure le fichier de connexion
require './inc/conn_db.php';

try {
    // Récupérer tous les utilisateurs en mode associatif avec MySQLi
    $result = $conn->query("SELECT id, matricule FROM users ORDER BY id ASC");

    // Définir le nouvel ID de départ
    $newId = 2401;

    // Boucle sur chaque utilisateur pour mettre à jour l'ID et le matricule
    while ($user = $result->fetch_assoc()) {
        $currentId = $user['id'];
        $currentMatricule = $user['matricule'];

        // Extraire la première lettre du matricule actuel
        $firstLetter = substr($currentMatricule, 0, 1);

        // Générer le nouveau matricule en gardant la même première lettre et en ajoutant le nouvel ID
        $newMatricule = $firstLetter . $newId;

        // Mettre à jour l'utilisateur avec le nouvel ID et le nouveau matricule
        $updateStmt = $conn->prepare("UPDATE users SET matricule = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newMatricule, $currentId);
        $updateStmt->execute();

        // Incrémenter le nouvel ID pour le prochain utilisateur
        $newId++;
    }

    echo "Mise à jour des IDs et des matricules terminée avec succès !";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

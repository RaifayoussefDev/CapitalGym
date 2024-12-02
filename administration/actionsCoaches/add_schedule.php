<?php
// Inclure le fichier de connexion à la base de données
require_once '../../inc/conn_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'ID du coach
    $coach_id = isset($_POST['coach_id']) ? intval($_POST['coach_id']) : null;

    // Vérifier si des jours ont été sélectionnés
    $jours = isset($_POST['jours']) ? $_POST['jours'] : [];
    $horaires = isset($_POST['horaires']) ? $_POST['horaires'] : [];

    if (!$coach_id || empty($jours)) {
        echo "Veuillez sélectionner un coach et au moins un jour.";
        exit;
    }

    try {
        // Débuter une transaction
        $conn->begin_transaction();

        // Insérer les jours et plages horaires dans la table `planning_coache`
        $insertQuery = "INSERT INTO planning_coache (coach_id, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);

        foreach ($jours as $jour) {
            if (isset($horaires[$jour])) {
                foreach ($horaires[$jour] as $horaire) {
                    // Diviser le créneau horaire en début et fin
                    list($heure_debut, $heure_fin) = explode(' - ', $horaire);

                    $stmt->bind_param("isss", $coach_id, $jour, $heure_debut, $heure_fin);
                    $stmt->execute();
                }
            }
        }

        // Valider la transaction
        $conn->commit();
        echo "Planning ajouté avec succès !";
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollback();
        echo "Erreur lors de l'ajout du planning : " . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}

<?php
// Inclure le fichier de connexion à la base de données
require_once '../../inc/conn_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données nécessaires
    $coach_id = isset($_POST['coach_id']) ? intval($_POST['coach_id']) : null;
    $location_id = 18; // ID spécifique donné
    $activite_id = 56; // ID spécifique donné
    $libelle = 'Coaching Privé'; // Exemple : libellé de la session
    $logo = ''; // Chemin par défaut si vide
    $genre = 'Mix'; // Exemple : genre
    $is_repetitive = 1; // Exemple : session répétitive

    // Vérifier si des jours et des horaires ont été fournis
    $jours = isset($_POST['jours']) ? $_POST['jours'] : [];
    $horaires = isset($_POST['horaires']) ? $_POST['horaires'] : [];

    if (!$coach_id || empty($jours)) {
        echo "Veuillez sélectionner un coach et au moins un jour.";
        exit;
    }

    try {
        // Débuter une transaction
        $conn->begin_transaction();

        // Insérer dans la table `sessions`
        $sessionQuery = "INSERT INTO sessions (coach_id, location_id, activite_id, libelle, logo, genre, is_repetitive) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtSession = $conn->prepare($sessionQuery);
        $stmtSession->bind_param("iiisssi", $coach_id, $location_id, $activite_id, $libelle, $logo, $genre, $is_repetitive);
        $stmtSession->execute();

        // Récupérer l'ID de la session insérée
        $session_id = $conn->insert_id;

        // Insérer les jours et plages horaires dans la table `session_planning`
        $planningQuery = "INSERT INTO session_planning (session_id, day, start_time, end_time, max_attendees, remaining_slots) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmtPlanning = $conn->prepare($planningQuery);

        foreach ($jours as $jour) {
            if (isset($horaires[$jour])) {
                foreach ($horaires[$jour] as $horaire) {
                    // Diviser le créneau horaire en début et fin
                    list($start_time, $end_time) = explode(' - ', $horaire);
                    $max_attendees = 1;
                    $remaining_slots = 1;

                    // Lier les paramètres et exécuter
                    $stmtPlanning->bind_param("isssii", $session_id, $jour, $start_time, $end_time, $max_attendees, $remaining_slots);
                    $stmtPlanning->execute();
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

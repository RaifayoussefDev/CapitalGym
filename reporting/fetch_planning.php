<?php
// fetch_planning.php

require "../inc/conn_db.php";

if (isset($_POST['coach_id'])) {
    $coach_id = intval($_POST['coach_id']);  // Get selected coach ID from POST

    $coaches_sql = "WITH TimeSlots AS (
        SELECT '08:00-09:00' AS time_slot
        UNION ALL
        SELECT '09:00-10:00'
        UNION ALL
        SELECT '10:00-11:00'
        UNION ALL
        SELECT '11:00-12:00'
        UNION ALL
        SELECT '12:00-13:00'
        UNION ALL
        SELECT '13:00-14:00'
        UNION ALL
        SELECT '14:00-15:00'
        UNION ALL
        SELECT '15:00-16:00'
        UNION ALL
        SELECT '16:00-17:00'
        UNION ALL
        SELECT '17:00-18:00'
        UNION ALL
        SELECT '18:00-19:00'
        UNION ALL
        SELECT '19:00-20:00'
        UNION ALL
        SELECT '20:00-21:00'
    )
    SELECT 
        u.matricule AS coach_matricule,  
        u.nom AS coach_nom, 
        u.prenom AS coach_prenom, 
        ts.time_slot,
        MAX(CASE WHEN sp.day = 'lundi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Lundi,
        MAX(CASE WHEN sp.day = 'mardi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Mardi,
        MAX(CASE WHEN sp.day = 'mercredi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Mercredi,
        MAX(CASE WHEN sp.day = 'jeudi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Jeudi,
        MAX(CASE WHEN sp.day = 'vendredi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Vendredi,
        MAX(CASE WHEN sp.day = 'samedi' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Samedi,
        MAX(CASE WHEN sp.day = 'dimanche' THEN CONCAT('- ', s.libelle, ' | ', l.name) END) AS Dimanche
    FROM TimeSlots ts
    LEFT JOIN session_planning sp ON 
        (sp.start_time <= ts.time_slot AND sp.end_time > ts.time_slot)
    JOIN sessions s ON sp.session_id = s.id
    JOIN locations l ON s.location_id = l.id
    JOIN coaches c ON s.coach_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE u.id = ?  -- Bind coach ID
    GROUP BY ts.time_slot, u.id
    ORDER BY ts.time_slot";

    $stmt = $conn->prepare($coaches_sql);
    $stmt->bind_param("i", $coach_id);  // Bind the coach ID
    $stmt->execute();
    $result = $stmt->get_result();

    $coaches = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $coaches[] = $row;
        }
    }

    echo json_encode($coaches);  // Return JSON data
    exit;
}
;?>
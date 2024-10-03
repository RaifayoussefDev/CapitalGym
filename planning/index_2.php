<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Récupérer les activités
$activites_sql = "SELECT id, nom FROM activites";
$activites_result = $conn->query($activites_sql);

$activites = [];
if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}

// Récupérer les coaches
$coaches_sql = "SELECT c.id, CONCAT(u.nom, ' ', u.prenom) AS name FROM coaches c JOIN users u ON c.user_id = u.id";
$coaches_result = $conn->query($coaches_sql);

$coaches = [];
if ($coaches_result->num_rows > 0) {
    while ($row = $coaches_result->fetch_assoc()) {
        $coaches[] = $row;
    }
}

// Récupérer les lieux
$locations_sql = "SELECT id, name FROM locations";
$locations_result = $conn->query($locations_sql);

$locations = [];
if ($locations_result->num_rows > 0) {
    while ($row = $locations_result->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Récupérer les sessions pour aujourd'hui
$sessions_sql = "SELECT 
    s.id, 
    a.nom AS activity_name, 
    s.date, 
    s.start_time, 
    s.end_time, 
    l.name AS location, 
    CONCAT(u.nom, ' ', u.prenom) AS coach_name 
FROM 
    sessions s
JOIN 
    activites a ON s.activite_id = a.id
JOIN 
    coaches c ON s.coach_id = c.id
JOIN 
    users u ON c.user_id = u.id
JOIN 
    locations l ON s.location_id = l.id
WHERE 
    s.date = CURDATE() 
ORDER BY 
    s.start_time;";
$sessions_result = $conn->query($sessions_sql);

$sessions = [];
if ($sessions_result->num_rows > 0) {
    while ($row = $sessions_result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une Session</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-4">Créer une Session d'Entraînement</h3>
        <form action="create_session.php" method="post">
            <div class="mb-3">
                <label for="activity" class="form-label">Activité</label>
                <select id="activity" name="activite_id" class="form-select select2" required>
                    <option value="">Sélectionner une activité</option>
                    <?php foreach ($activites as $activite) : ?>
                        <option value="<?php echo $activite['id']; ?>"><?php echo htmlspecialchars($activite['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="coach" class="form-label">Coach</label>
                <select id="coach" name="coach_id" class="form-select select2" required>
                    <option value="">Sélectionner un coach</option>
                    <?php foreach ($coaches as $coach) : ?>
                        <option value="<?php echo $coach['id']; ?>"><?php echo htmlspecialchars($coach['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Lieu</label>
                <select id="location" name="location_id" class="form-select select2" required>
                    <option value="">Sélectionner un lieu</option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="sessionDate" class="form-label">Date</label>
                <input type="date" id="sessionDate" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="startTime" class="form-label">Heure de début</label>
                <input type="time" id="startTime" name="start_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="endTime" class="form-label">Heure de fin</label>
                <input type="time" id="endTime" name="end_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="maxAttendees" class="form-label">Nombre maximal d'adhérents</label>
                <input type="number" id="maxAttendees" name="max_attendees" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Créer la Session</button>
        </form>

        <!-- Table de l'emploi du temps -->
        <h3 class="mt-5 mb-4">Emploi du Temps pour Aujourd'hui</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $start_time = '08:30';
                $end_time = '21:30';
                $current_time = $start_time;

                while (strtotime($current_time) <= strtotime($end_time)) {
                    $next_time = date('H:i', strtotime($current_time) + 30 * 60); // Adding 30 minutes
                    $session_info = '';

                    foreach ($sessions as $session) {
                        if ($session['start_time'] <= $current_time && $session['end_time'] > $current_time) {
                            $session_info = "{$session['activity_name']} avec {$session['coach_name']} à {$session['location']}";
                            break;
                        }
                    }

                    echo "<tr>";
                    echo "<td>$current_time - $next_time</td>";
                    echo "<td>$session_info</td>";
                    echo "</tr>";

                    $current_time = $next_time;
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>

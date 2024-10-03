<?php
require "../inc/app.php";

require "../inc/conn_db.php";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'])) {
    // Get the session ID from the form
    $session_id = $_POST['session_id'];

    // Get the form data
    $activity = $_POST['activity'];
    $coach = $_POST['coach'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $maxAttendees = $_POST['maxAttendees'];

    // Calculate the remaining slots initially equal to max attendees
    $remainingSlots = $maxAttendees;

    // Prepare the SQL statement
    $sql = "UPDATE `sessions` SET `date`=?, `start_time`=?, `end_time`=?, `max_attendees`=?, `remaining_slots`=?, `coach_id`=?, `location_id`=?, `activite_id`=? WHERE `id`=?";

    $requestSuccessful = false; // Initialize as false

    // Prepare and bind
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssiiiiii", $date, $startTime, $endTime, $maxAttendees, $remainingSlots, $coach, $location, $activity, $session_id);

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['message'] = "Séance mise à jour avec succès!";
            $_SESSION['message_type'] = "success";
            $requestSuccessful = true; // Set to true if the query was successful
        } else {
            $_SESSION['message'] = "Erreur lors de la mise à jour de la séance. Veuillez réessayer.";
            $_SESSION['message_type'] = "danger";
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erreur de préparation de la requête.";
        $_SESSION['message_type'] = "danger";
    }

    // Close the connection
    $conn->close();

    if ($requestSuccessful) {
        echo "<script type='text/javascript'>
            window.location.href = 'index.php?msg=success';
        </script>";
    } else {
        echo "<script type='text/javascript'>
            window.location.href = 'index.php?msg=error';
        </script>";
    }

    exit();
} elseif (isset($_GET['session_id'])) {
    // Retrieve session details for pre-filling the form
    $session_id = $_GET['session_id'];

    // Fetch session data from database
    $sql = "SELECT * FROM `sessions` WHERE `id` = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
    } else {
        $_SESSION['message'] = "Erreur de préparation de la requête.";
        $_SESSION['message_type'] = "danger";
        $conn->close();
        exit("<script>window.location.href = 'index.php';</script>");
    }
} else {
    $_SESSION['message'] = "ID de session non spécifié.";
    $_SESSION['message_type'] = "danger";
    $conn->close();
    exit("<script>window.location.href = 'index.php';</script>");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une séance</title>
    <!-- Include necessary CSS -->
</head>
<body>

    <form action="update_session.php" method="POST">
        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['id']); ?>">

        <div class="modal-body">
            <div class="form-group">
                <label for="activity">Activité</label>
                <select id="activity" name="activity" class="form-control select2" required>
                    <option value="">Sélectionner une activité</option>
                    <?php foreach ($activites as $activite) : ?>
                        <option value="<?php echo htmlspecialchars($activite['id']); ?>" <?php if ($activite['id'] == $session['activite_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($activite['nom']); ?> - <?php echo htmlspecialchars($activite['prix']); ?>€
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="coach">Coach</label>
                <select id="coach" name="coach" class="form-control select2" required>
                    <option value="">Sélectionner un coach</option>
                    <?php foreach ($coaches as $coach) : ?>
                        <option value="<?php echo htmlspecialchars($coach['id']); ?>" <?php if ($coach['id'] == $session['coach_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($coach['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="location">Lieu</label>
                <select id="location" name="location" class="form-control select2" required>
                    <option value="">Sélectionner un lieu</option>
                    <?php foreach ($locations as $location) : ?>
                        <option value="<?php echo htmlspecialchars($location['id']); ?>" <?php if ($location['id'] == $session['location_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($location['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($session['date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="startTime">Heure de début</label>
                <input type="time" id="startTime" name="startTime" class="form-control" value="<?php echo htmlspecialchars($session['start_time']); ?>" required>
            </div>
            <div class="form-group">
                <label for="endTime">Heure de fin</label>
                <input type="time" id="endTime" name="endTime" class="form-control" value="<?php echo htmlspecialchars($session['end_time']); ?>" required>
            </div>
            <div class="form-group">
                <label for="maxAttendees">Nombre de places</label>
                <input type="number" id="maxAttendees" name="maxAttendees" class="form-control" value="<?php echo htmlspecialchars($session['max_attendees']); ?>" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
    </form>

    <!-- Include necessary JavaScript -->
</body>
</html>

<?php
require "../inc/app.php";

require "../inc/conn_db.php";

// Initialize $coach variable
$coach = [];

// Check if ID parameter is passed in the URL
if (isset($_GET['id_coach'])) {
    $id_coach = $_GET['id_coach'];

    // Query to fetch coach details and sessions
    $sql = "
    SELECT 
        coaches.id,
        users.nom AS user_nom,
        users.prenom AS user_prenom,
        users.matricule,
        users.email,
        users.phone,
        users.photo,
                activites.nom AS activite_nom,

    FROM 
        coaches
    JOIN 
        users ON coaches.user_id = users.id
    JOIN 
        activites ON coaches.activite_id = activites.id
    LEFT JOIN
        sessions ON coaches.id = sessions.coach_id
    WHERE 
        coaches.id = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_coach);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch coach details
        $coach = $result->fetch_assoc();

        // Fetch sessions if available
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
    } else {
        // Handle case where coach with given ID is not found
        echo "Coach not found";
        exit();
    }
} else {
    // Handle case where ID parameter is missing
    echo "Coach ID not provided";
    exit();
}

// Include header
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Consultation Coach</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Consultation Coach</h4>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Informations Personnelles</h5>
                    <section>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?php if (!empty($coach['photo'])) : ?>
                                        <img src="../assets/img/capitalsoft/profils/<?= htmlspecialchars($coach['photo']) ?>" alt="Photo de Profil" style="width: 150px; height: 150px; object-fit: cover;" />
                                    <?php else : ?>
                                        <span>Aucune photo de profil disponible</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Nom:</label>
                                    <span><?= htmlspecialchars($coach['user_nom']) ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Prénom:</label>
                                    <span><?= htmlspecialchars($coach['user_prenom']) ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Matricule:</label>
                                    <span><?= htmlspecialchars($coach['matricule']) ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <span><?= htmlspecialchars($coach['email']) ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Téléphone:</label>
                                    <span><?= htmlspecialchars($coach['phone']) ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Activité:</label>
                                    <span><?= htmlspecialchars($coach['activite_nom']) ?></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <?php if (!empty($sessions)) : ?>
                        <!-- <h5>Sessions animées par le Coach</h5> -->
                        <!-- <section>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom de la Session</th>
                                            <th>Date</th>
                                            <th>Heure de Début</th>
                                            <th>Heure de Fin</th>
                                            <th>Emplacement</th>
                                            <th>Nombre Maximum d'Participants</th>
                                            <th>Places Restantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($session['activite_nom']) ?></td>
                                                <td><?= htmlspecialchars($session['date']) ?></td>
                                                <td><?= htmlspecialchars($session['start_time']) ?></td>
                                                <td><?= htmlspecialchars($session['end_time']) ?></td>
                                                <td><?= htmlspecialchars($session['location_id']) ?></td>
                                                <td><?= htmlspecialchars($session['max_attendees']) ?></td>
                                                <td><?= htmlspecialchars($session['remaining_slots']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section> -->
                    <?php else : ?>
                        <p>Ce coach n'anime actuellement aucune session.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require "../inc/footer.php";
?>
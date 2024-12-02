<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Query to retrieve coaches' details with associated user and activity information
$sql = "
SELECT 
    c.id AS coach_id,
    u.nom AS coach_nom,
    u.prenom AS coach_prenom,
    u.photo AS photo,
    pc.jour,
    MIN(pc.heure_debut) AS heure_debut,
    MAX(pc.heure_fin) AS heure_fin
FROM 
    planning_coache pc
INNER JOIN 
    coaches c ON pc.coach_id = c.id
INNER JOIN 
    users u ON c.user_id = u.id
GROUP BY 
    c.id, pc.jour
ORDER BY 
    heure_debut;
";

$result = $conn->query($sql);

$coaches = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $coaches[] = $row;
    }
} else {
    $coaches = [];
}

// Récupérer les activités
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_result = $conn->query($activites_sql);
$activites = [];
$type_paiements = [];

if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}
$conn->close();
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Coachs</h3>
        </div>
    </div>
    <div class="row">
        <!-- Your existing cards here -->
        <div class="col-sm-12 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"></h4>
                        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addCoachModal">
                            <i class="fa fa-plus"></i>
                            Ajouter Coachs
                        </button>
                    </div>
                </div>
                <div class="modal fade" id="addCoachModal" tabindex="-1" aria-labelledby="addCoachModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addCoachModalLabel">
                                    <span class="fw-mediumbold">New</span>
                                    <span class="fw-light">Schedule</span>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="schedule-form" action="actionsCoaches/add_schedule.php" method="post">
                                    <h5>Informations Coach</h5>
                                    <section>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="coach_id">Sélectionnez un coach</label>
                                                    <select name="coach_id" id="coach_id" class="form-control">
                                                        <?php foreach ($coaches as $coach) : ?>
                                                            <option value="<?= $coach['id'] ?>">
                                                                <?= htmlspecialchars($coach['coach_nom'] . ' ' . $coach['coach_prenom']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <h5>Jours et Horaires</h5>
                                    <section>
                                        <div class="form-group">
                                            <label>Horaires par jour</label>
                                            <div class="row">
                                                <?php
                                                $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
                                                foreach ($jours as $index => $jour) : ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input day-checkbox" type="checkbox" id="day_<?= $index ?>" value="<?= strtolower($jour) ?>" name="jours[]" onchange="toggleHourSelect(<?= $index ?>)">
                                                            <label class="form-check-label" for="day_<?= $index ?>"><?= $jour ?></label>
                                                        </div>
                                                        <div class="mt-2">
                                                            <select name="horaires[<?= strtolower($jour) ?>][]" id="hour_<?= $index ?>" class="form-control" multiple disabled>
                                                                <!-- Les options des heures seront générées par JavaScript -->
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </section>

                                    <div class="wizard-action">
                                        <div class="pull-right">
                                            <button type="submit" class="btn btn-secondary">Valider</button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom</th>
                                    <th>jour</th>
                                    <th>Heure debut</th>
                                    <th>heure fin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom</th>
                                    <th>jour</th>
                                    <th>Heure debut</th>
                                    <th>heure fin</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php foreach ($coaches as $coach) : ?>
                                    <tr>
                                        <td style="width:50px">
                                            <img src="../assets/img/capitalsoft/profils/<?php echo !empty($coach['photo']) ? htmlspecialchars($coach['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                                        </td>
                                        <td><?php echo htmlspecialchars($coach['coach_nom']); ?> <?php echo htmlspecialchars($coach['coach_prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['jour']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['heure_debut']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['heure_fin']); ?></td>
                                        <td>
                                            <!-- Actions links or buttons -->
                                            <!-- Example: View and Edit links -->
                                            <a href="consult.php?id_coach=<?php echo htmlspecialchars($coach['coach_id']); ?>" class="btn btn-info btn-consult">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="modif.php?id_coach=<?php echo htmlspecialchars($coach['coach_id']); ?>" class="btn btn-warning btn-modify">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];

        // Fonction pour générer les options horaires
        function generateHourOptions(selectElement) {
            selectElement.innerHTML = ""; // Réinitialiser
            let startHour = 6; // 06:00
            let endHour = 23; // 23:00

            for (let hour = startHour; hour < endHour; hour++) {
                let startTime = hour.toString().padStart(2, "0") + ":00:00";
                let endTime = (hour + 1).toString().padStart(2, "0") + ":00:00";
                let option = document.createElement("option");
                option.value = `${startTime} - ${endTime}`;
                option.textContent = `${startTime} - ${endTime}`;
                selectElement.appendChild(option);
            }
        }

        // Fonction pour activer/désactiver le select des heures
        window.toggleHourSelect = function(index) {
            const dayCheckbox = document.getElementById(`day_${index}`);
            const hourSelect = document.getElementById(`hour_${index}`);
            if (dayCheckbox.checked) {
                hourSelect.disabled = false;
                generateHourOptions(hourSelect); // Générer les heures si activé
            } else {
                hourSelect.disabled = true;
                hourSelect.innerHTML = ""; // Réinitialiser si désactivé
            }
        };
    });
</script>

<?php
require "../inc/footer.php";
?>
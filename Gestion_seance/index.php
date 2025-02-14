<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch activities
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_result = $conn->query($activites_sql);

$activites = [];
if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}

// Fetch coaches
$coaches_sql = "SELECT c.id, CONCAT(u.nom, ' ', u.prenom) AS name FROM coaches c JOIN users u ON c.user_id = u.id";
$coaches_result = $conn->query($coaches_sql);

$coaches = [];
if ($coaches_result->num_rows > 0) {
    while ($row = $coaches_result->fetch_assoc()) {
        $coaches[] = $row;
    }
}

// Fetch locations
$locations_sql = "SELECT id, name, nomber_place FROM locations";
$locations_result = $conn->query($locations_sql);

$locations = [];
if ($locations_result->num_rows > 0) {
    while ($row = $locations_result->fetch_assoc()) {
        $locations[] = $row;
    }
}

$days_of_week = [
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi',
    'Saturday' => 'Samedi',
    'Sunday' => 'Dimanche'
];

// Get the day from the query parameter, defaulting to today if not provided or invalid
$selected_day = isset($_GET['day']) && array_key_exists($_GET['day'], $days_of_week) ? $_GET['day'] : date('l');

// Convert the selected day to French
$selected_day_french = $days_of_week[$selected_day];


// Fetch sessions based on selected date
$date = $selected_day_french;
$sessions_sql = "
    SELECT 
        s.id AS session_id, 
        a.nom AS activity_name, 
        l.name AS location, 
        CONCAT(u.nom, ' ', u.prenom) AS coach_name,
        sp.max_attendees,
        sp.remaining_slots,
        sp.start_time,
        sp.end_time,
        sp.day
    FROM 
        sessions s 
    JOIN 
        session_planning sp ON s.id = sp.session_id
    JOIN 
        coaches c ON s.coach_id = c.id 
    JOIN 
        activites a ON s.activite_id = a.id 
    JOIN 
        locations l ON s.location_id = l.id 
    JOIN 
        users u ON c.user_id = u.id 
    WHERE 
        sp.day = ? 
    AND 
        s.activite_id not in (54 , 55,56)
    AND
        sp.etat = 'active'
    ";

$stmt = $conn->prepare($sessions_sql);
$stmt->bind_param("s", $date); // Bind the selected date
$stmt->execute();
$sessions_result = $stmt->get_result();

$sessions = [];
if ($sessions_result->num_rows > 0) {
    while ($row = $sessions_result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

$conn->close();


?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        // Initialize select2 for dropdowns
        $('.select2').select2();

        // Function to handle the fade-in and fade-out animations
        function animateAlert(alertId) {
            var alert = $('#' + alertId);
            alert.addClass('fade-in-right');
            setTimeout(function() {
                alert.addClass('fade-out-left');
                setTimeout(function() {
                    alert.alert('close');
                }, 1000); // Time for fade-out animation
            }, 5000); // Display time before starting fade-out
        }

        // Apply the animations to the alerts
        if ($('#alert-success').length) {
            animateAlert('alert-success');
        } else if ($('#alert-error').length) {
            animateAlert('alert-error');
        }

        // Handle the edit button click event
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'get_session.php',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    const session = JSON.parse(response);
                    $('#editActivityModal #activity').val(session.activite_id).trigger('change');
                    $('#editActivityModal #coach').val(session.coach_id).trigger('change');
                    $('#editActivityModal #location').val(session.location_id).trigger('change');
                    $('#editActivityModal #date').val(session.date);
                    $('#editActivityModal #startTime').val(session.start_time);
                    $('#editActivityModal #endTime').val(session.end_time);
                    $('#editActivityModal #maxAttendees').val(session.max_attendees);
                    $('#editActivityModal form').attr('action', 'update_session.php'); // Ensure the form submits to update_session.php
                    $('#editActivityModal #sessionId').val(session.id); // Add the ID for editing
                }
            });
        });

        // Handle the delete button click event
        // $(document).on('click', '.btn-delete', function() {
        //     const id = $(this).data('id');
        //     $('#deleteActivityModal #deleteSessionId').val(id);
        // });
    });
</script>

<style>
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeOutLeft {
        from {
            opacity: 1;
            transform: translateX(0);
        }

        to {
            opacity: 0;
            transform: translateX(-50px);
        }
    }

    .fade-in-right {
        animation: fadeInRight 1s ease-in-out;
    }

    .fade-out-left {
        animation: fadeOutLeft 1s ease-in-out;
    }
</style>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Séance</h3>
        </div>
        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addActivityModal">
            <i class="fa fa-plus"></i> Ajouter Séance
        </button>
    </div>

    <!-- Day Navigation -->
    <div class="mb-3 d-flex justify-content-center">
        <?php foreach ($days_of_week as $english_day => $french_day): ?>
            <a href="?day=<?php echo urlencode($english_day); ?>" class="btn btn-dark mx-2 <?php echo ($selected_day == $english_day) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($french_day); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success') : ?>
        <div id="alert-success" class="alert alert-success alert-dismissible fade show" role="alert">
            Operation was successful!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error') : ?>
        <div id="alert-error" class="alert alert-danger alert-dismissible fade show" role="alert">
            An error occurred. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sessionTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom de l'activité</th>
                                    <th>Date</th>
                                    <th>Heure de début</th>
                                    <th>Heure de fin</th>
                                    <th>Lieu</th>
                                    <th>Nom du coach</th>
                                    <th>Nombre de places</th>
                                    <th>Disponibles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($sessions) > 0) : ?>
                                    <?php foreach ($sessions as $session) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($session['activity_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['day']); ?></td>
                                            <td><?php echo htmlspecialchars($session['start_time']); ?></td>
                                            <td><?php echo htmlspecialchars($session['end_time']); ?></td>
                                            <td><?php echo htmlspecialchars($session['location']); ?></td>
                                            <td><?php echo htmlspecialchars($session['coach_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['max_attendees']); ?></td>
                                            <td><?php echo htmlspecialchars($session['remaining_slots']); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-edit" data-id="<?php echo htmlspecialchars($session['session_id']); ?>" data-bs-toggle="modal" data-bs-target="#EditActivityModal">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-delete" data-id="<?php echo htmlspecialchars($session['session_id']); ?>" data-bs-toggle="modal" data-bs-target="#deleteActivityModalM">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="9">No sessions available for the selected day.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Session Modal -->
        <div class="modal fade" id="deleteActivityModalM" tabindex="-1" role="dialog" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteActivityModalLabel">Supprimer Séance</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="delete_session.php" method="POST">
                        <div class="modal-body">
                            <p>Êtes-vous sûr de vouloir supprimer cette séance ?</p>
                            <!-- Hidden input to store session ID -->
                            <input type="hidden" id="deleteSessionId" name="sessionId">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                            <button type="submit" class="btn btn-danger">Oui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Day's Schedule Table -->
        <div class="col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <h5>Horaire de la journée (8:00 - 21:00)</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Heure</th>
                                    <th>Activité</th>
                                    <th>Lieu</th>
                                    <th>Coach</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Define the time slots
                                $time_slots = [
                                    '08:00',
                                    '09:00',
                                    '10:00',
                                    '11:00',
                                    '12:00',
                                    '13:00',
                                    '14:00',
                                    '15:00',
                                    '16:00',
                                    '17:00',
                                    '18:00',
                                    '19:00',
                                    '20:00',
                                    '21:00',
                                ];

                                // Loop through each time slot, pairing each start time with its next as the end time
                                for ($i = 0; $i < count($time_slots) - 1; $i++) {
                                    $slot_start = $time_slots[$i];      // Start time of the slot
                                    $slot_end = $time_slots[$i + 1];    // End time of the slot (next time in the array)
                                    $found = false;
                                    $activity_name = '';
                                    $location = '';
                                    $coach_name = '';

                                    // Loop through sessions to check if any session falls exactly within this slot
                                    foreach ($sessions as $session) {
                                        // Convert session times to 'H:i' format
                                        $session_start = date('H:i', strtotime($session['start_time']));
                                        $session_end = date('H:i', strtotime($session['end_time']));

                                        // Check if session falls within the current slot (inclusive of start time and exclusive of end time)
                                        if ($session_start == $slot_start && $session_end == $slot_end) {
                                            $found = true;
                                            $activity_name = htmlspecialchars($session['activity_name']);
                                            $location = htmlspecialchars($session['location']);
                                            $coach_name = htmlspecialchars($session['coach_name']);
                                            break; // Stop the loop once a session is found for the current slot
                                        }
                                    }

                                    // Display the time slot and session information
                                    echo
                                    "<tr>
                                        <td>$slot_start - $slot_end</td>
                                        <td>" . ($found ? $activity_name : '') . "</td>
                                        <td>" . ($found ? $location : '') . "</td>
                                        <td>" . ($found ? $coach_name : '') . "</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>




                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Session Modal -->
    <div class="modal fade" id="deleteActivityModal" tabindex="-1" role="dialog" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteActivityModalLabel">Supprimer Séance</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="delete_session.php" method="POST">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer cette séance ?</p>
                        <!-- Hidden input to store session ID -->
                        <input type="hidden" id="deleteSessionId" name="sessionId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                        <button type="submit" class="btn btn-danger">Oui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="EditActivityModal" tabindex="-1" role="dialog" aria-labelledby="EditActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="EditActivityModalLabel">Modifier la Séance</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="EditSessionForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Libellé -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-libelle">Libellé de la Séance</label>
                                    <input type="text" id="edit-libelle" name="libelle" class="form-control" placeholder="Nom de la séance" required>
                                    <input type="text" id="Edit_session_id" name="session_id" class="form-control d-none" required>
                                </div>
                            </div>

                            <!-- Activité -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-activity">Activité</label>
                                    <select id="edit-activity" name="activity" class="form-control select2" required>
                                        <option value="">Sélectionner une activité</option>
                                        <?php foreach ($activites as $activite): ?>
                                            <option value="<?php echo htmlspecialchars($activite['id']); ?>">
                                                <?php echo htmlspecialchars($activite['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Logo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <img id="edit-logoPreview" src="" alt="Logo actuel" style="max-width: 100px; display: none;">
                                </div>
                                <div class="form-group">
                                    <label for="edit-logo">Logo de la Séance</label>
                                    <input type="file" id="edit-logo" name="logo" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <!-- Coach -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-coach">Coach</label>
                                    <select id="edit-coach" name="coach" class="form-control select2" required>
                                        <option value="">Sélectionner un coach</option>
                                        <?php foreach ($coaches as $coach): ?>
                                            <option value="<?php echo htmlspecialchars($coach['id']); ?>">
                                                <?php echo htmlspecialchars($coach['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Lieu -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Edit-location">Lieu</label>
                                    <select id="Edit-location" name="location" class="form-control select2" required>
                                        <option value="">Sélectionner un lieu</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location['id']); ?>" data-max-Edit-attendees="<?php echo htmlspecialchars($location['nomber_place']); ?>">
                                                <?php echo htmlspecialchars($location['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Genre -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit-gender">Genre</label>
                                    <select id="edit-gender" name="gender" class="form-control select2" required>
                                        <option value="Mix" selected>Mixte</option>
                                        <option value="Homme">Homme</option>
                                        <option value="Femme">Femme</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Nombre de places (Max Attendees) -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="maxAttendees">Nombre de places</label>
                                    <input type="number" id="Edit-maxAttendees" name="maxAttendees" class="form-control" readonly required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-lundi">Lundi</label>
                                        <select name="edit-lundi-hours" id="Edit-lundi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-mardi">Mardi</label>
                                        <select name="edit-mardi-hours" id="Edit-mardi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Mercredi -->

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-mercredi">Mercredi</label>
                                        <select name="edit-mercredi-hours" id="Edit-mercredi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Jeudi -->

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-jeudi">Jeudi</label>
                                        <select name="edit-jeudi-hours" id="Edit-jeudi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Vendredi -->

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-vendredi">Vendredi</label>
                                        <select name="edit-vendredi-hours" id="Edit-vendredi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>



                                <!-- Samedi -->

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-samedi">Samedi</label>
                                        <select name="edit-samedi-hours" id="Edit-samedi" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>



                                <!-- Dimanche -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Edit-dimanche">Dimanche</label>
                                        <select name="edit-dimanche-hours" id="Edit-dimanche" class="form-control">
                                            <option value=""></option>
                                            <option value="07:00">07:00</option>
                                            <option value="08:00">08:00</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:30">12:30</option>
                                            <option value="13:30">13:30</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                            <option value="19:00">19:00</option>
                                            <option value="19:30">19:30</option>
                                            <option value="20:00">20:00</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>

                </form>
            </div>
        </div>
    </div>

</div>
</div>


<!-- Add Session Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1" role="dialog" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addActivityModalLabel">Ajouter Séance</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSessionForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Libelle Field (Session Name) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="libelle">Libellé de la Séance</label>
                                <input type="text" id="libelle" name="libelle" class="form-control" placeholder="Nom de la séance" required>
                            </div>
                        </div>

                        <!-- Activité Field -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activity">Activité</label>
                                <select id="activity" name="activity" class="form-control select2" required>
                                    <option value="">Sélectionner une activité</option>
                                    <?php foreach ($activites as $activite) : ?>
                                        <option value="<?php echo htmlspecialchars($activite['id']); ?>">
                                            <?php echo htmlspecialchars($activite['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Logo Field -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="logo">Logo de la Séance</label>
                                <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Coach Field -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="coach">Coach</label>
                                <select id="coach" name="coach" class="form-control select2" required>
                                    <option value="">Sélectionner un coach</option>
                                    <?php foreach ($coaches as $coach) : ?>
                                        <option value="<?php echo htmlspecialchars($coach['id']); ?>">
                                            <?php echo htmlspecialchars($coach['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Lieu (Location) Field -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Lieu</label>
                                <select id="location" name="location" class="form-control select2" required>
                                    <option value="">Sélectionner un lieu</option>
                                    <?php foreach ($locations as $location) : ?>
                                        <option value="<?php echo htmlspecialchars($location['id']); ?>" data-max-attendees="<?php echo htmlspecialchars($location['nomber_place']); ?>">
                                            <?php echo htmlspecialchars($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="repetitive">Planification</label>
                                <select id="repetitive" name="repetitive" class="form-control select2" required>
                                    <option value="Non-repetitive">Non répétitif</option>
                                    <option value="Repetitive" selected>Répétitif</option>
                                </select>
                            </div>
                        </div>
                        <!-- Genre Field -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Genre</label>
                                <select id="gender" name="gender" class="form-control select2" required>
                                    <option value="Mix" selected>Mixte</option>
                                    <option value="Homme">Homme</option>
                                    <option value="Femme">Femme</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nombre de places (Max Attendees) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maxAttendees">Nombre de places</label>
                                <input type="number" id="maxAttendees" name="maxAttendees" class="form-control" readonly required>
                            </div>
                        </div>
                        <!-- Days and Hours Selection -->
                        <div class="row">
                            <div class="form-group">
                                <label for="days">Sélectionnez les jours et les horaires :</label>
                                <?php
                                $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                                foreach ($days as $index => $day): ?>
                                    <?php if ($index % 2 === 0): ?> <!-- Start a new row every two days -->
                                        <div class="row">
                                        <?php endif; ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input day-checkbox" type="checkbox" id="<?php echo $day; ?>" name="days[]" value="<?php echo $day; ?>">
                                                <label class="form-check-label" for="<?php echo $day; ?>"><?php echo ucfirst($day); ?></label>
                                                <select id="<?php echo $day; ?>Hours" name="<?php echo $day; ?>Hours" class="form-control time-select" disabled>
                                                    <!-- Time options will be generated here -->
                                                </select>
                                            </div>
                                        </div>
                                        <?php if ($index % 2 === 1): ?> <!-- Close the row after two days -->
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (count($days) % 2 !== 0): ?> <!-- Close the last row if there is an odd number of days -->
                            </div>
                        <?php endif; ?>

                        </div>
                    </div>



                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).on('click', '.btn-edit', function() {
        var sessionId = $(this).data('id'); // ID de la séance à éditer

        // Récupération des données de la séance via AJAX
        $.ajax({
            url: 'fetch_session_data.php', // Endpoint pour récupérer les données de la séance
            method: 'GET',
            data: {
                id: sessionId
            },
            success: function(response) {
                try {
                    var session = JSON.parse(response); // Parser la réponse JSON

                    // Vérifier si les données nécessaires sont présentes
                    if (!session) {
                        alert('Données de session introuvables.');
                        return;
                    }

                    // Remplir les champs du formulaire
                    $('#edit-libelle').val(session.libelle); // Nom de la séance
                    $('#Edit-maxAttendees').val(session.max_attendees); // Nom de la séance
                    $('#Edit_session_id').val(session.id); // Nom de la séance
                    $('#edit-activity').val(session.activite_id).trigger('change'); // Activité
                    $('#edit-coach').val(session.coach_id).trigger('change'); // Coach
                    $('#Edit-location').val(session.location_id).trigger('change'); // Lieu
                    $('#edit-gender').val(session.genre).trigger('change'); // Genre




                    // Gestion de l'aperçu du logo
                    if (session.logo) {
                        $('#edit-logoPreview').attr('src', session.logo).show();
                    } else {
                        $('#edit-logoPreview').hide();
                    }


                    // Tableau des jours de la semaine
                    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

                    // Vérifier et définir la valeur pour chaque jour
                    jours.forEach(jour => {
                        if (session.times[jour]) {
                            const time = session.times[jour].split(':')[0] + ':' + session.times[jour].split(':')[1]; // Extraire l'heure et les minutes
                            $(`#Edit-${jour}`).val(time); // Définir la valeur de la liste déroulante
                        }
                    });


                    // Populate the days (check if the day exists in session.days)
                    var days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                    days.forEach(function(day) {
                        var daySelect = $('#edit-' + day); // Identifying the select input for the day
                        if (session.days.includes(day)) {
                            daySelect.val(session.times[day]).prop('disabled', false); // Set time and enable
                        } else {
                            daySelect.val('').prop('disabled', true); // Clear value and disable
                        }
                    });

                    // Afficher le modal d'édition
                    $('#EditActivityModal').modal('show');
                } catch (error) {
                    console.error('Erreur lors du traitement des données:', error);
                    alert('Erreur lors du chargement des données.');
                }
            },
            error: function() {
                alert('Erreur lors de la récupération des données de la séance.');
            }
        });
    });

    // Gestion du changement des sélecteurs des jours
    $(document).on('change', '.edit-day-select', function() {
        var day = $(this).attr('id').replace('edit-', ''); // Identifier le jour
        var timeSelect = $('#edit-' + day); // Identifier le sélecteur de temps correspondant

        if ($(this).val()) {
            // If the day is selected, enable the time select field
            timeSelect.prop('disabled', false);
        } else {
            // If no day is selected, disable the time select field
            timeSelect.prop('disabled', true).val('');
        }
    });
</script>




<!-- JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Days in French
        const days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

        // Specific times for all days
        const times = [
            "07:00", "08:00", "09:00", "10:00", "11:00",
            "12:30", "13:00" ,"13:30", "14:00" , "14:30" , "15:00" , "15:30" , "16:00" , "16:30", "17:30", "18:30",
            "19:30", "20:00", "20:30"
        ];

        // Function to generate time options for each select
        function generateTimeOptions() {
            return times.map(time => `<option value="${time}">${time}</option>`).join('');
        }

        // Apply the time options to each day's select element
        days.forEach(day => {
            const checkbox = document.getElementById(day);
            const select = document.getElementById(day + 'Hours');

            // Insert time options in the select element
            select.innerHTML = generateTimeOptions();

            // Enable/disable time select based on the checkbox state
            checkbox.addEventListener('change', function() {
                select.disabled = !this.checked;
                if (this.checked) {
                    select.value = '07:00'; // Set default to the first option (07:00)
                } else {
                    select.value = ''; // Reset the selection when unchecked
                }
            });
        });
    });
</script>



<script>
    // Script to set the session ID in the delete modal
    $(document).ready(function() {
        $('.btn-delete').click(function() {
            var sessionId = $(this).data('id');
            $('#deleteSessionId').val(sessionId); // Set session ID in the hidden input
        });
    });
</script>

<script>
    $('#Edit-location').on('change', function() {
        // Get the max-attendees data attribute of the selected location
        var maxAttendees = $(this).find('option:selected').data('max-edit-attendees');

        // Log the maxAttendees value to the console for debugging
        console.log("Max Attendees: " + maxAttendees);

        // Set the value of the maxAttendees input field to the retrieved value
        $('#Edit-maxAttendees').val(maxAttendees);
    });

    $(document).ready(function() {
        // Update maxAttendees when location is selected
        $('#EditSessionForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the form from submitting the traditional way

            // Collect form data
            var formData = new FormData(this);

            // Optional: Log the formData to debug
            console.log([...formData]);

            // Send the data via AJAX
            $.ajax({
                url: 'Edit_session.php', // Replace with your endpoint URL
                type: 'POST', // Or PUT if applicable
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Handle success (e.g., display a success message or reload part of the page)
                    console.log('Session updated successfully:', response);
                    $('#EditActivityModal').modal('hide'); // Close the modal

                    // Refresh the page after a successful update
                    // location.reload();
                },
                error: function(error) {
                    // Handle error (e.g., display error message)
                    console.error('Error updating session:', error);
                }
            });
        });
    });
</script>
<script>
    // Update maxAttendees when location is selected
    $('#location').on('change', function() {
        var maxAttendees = $(this).find('option:selected').data('max-attendees');

        $('#maxAttendees').val(maxAttendees); // Set the maxAttendees field
    });

    // Handle form submission via AJAX
    $('#addSessionForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this); // Collect form data including file uploads

        $.ajax({
            url: 'add_session.php', // Replace with the URL to your PHP handler
            type: 'POST',
            data: formData,
            contentType: false, // Important for file upload
            processData: false, // Important for file upload
            success: function(response) {
                // Handle success (e.g., close the modal, display a success message)
                $('#addActivityModal').modal('hide');
                alert('Séance ajoutée avec succès!');
            },
            error: function() {
                // Handle error
                alert('Une erreur est survenue. Veuillez réessayer.');
            }
        });
    });
</script>


<script>
    document.getElementById('location').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var maxAttendees = selectedOption.getAttribute('data-max-attendees');
        document.getElementById('maxAttendees').value = maxAttendees ? maxAttendees : '';
    });
</script>


<script>
    // Automatically set maxAttendees based on selected location
    document.getElementById('location').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var maxAttendees = selectedOption.getAttribute('data-max-attendees');
        document.getElementById('maxAttendees').value = maxAttendees || '';
    });
</script>


<?php
require "../inc/footer.php";
?>
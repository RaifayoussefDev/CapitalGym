<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Définir les créneaux horaires
$start_times = [
    "07:00",
    "08:00",
    "09:00",
    "10:00",
    "11:00",
    "12:30",
    "13:30",
    "16:30",
    "17:30",
    "18:30",
    "19:30",
    "20:00",
    "20:30"
];

// Générer les plages horaires
$time_slots = [];
for ($i = 0; $i < count($start_times) - 1; $i++) {
    $time_slots[] = $start_times[$i] . " - " . $start_times[$i + 1];
}

// Get the day from the URL, or default to the current day
$day = isset($_GET['day']) ? $_GET['day'] : strtolower(date('l'));

// Convert English day names to French if needed
$daysInFrench = [
    'monday' => 'lundi',
    'tuesday' => 'mardi',
    'wednesday' => 'mercredi',
    'thursday' => 'jeudi',
    'friday' => 'vendredi',
    'saturday' => 'samedi',
    'sunday' => 'dimanche',
];

// Ensure the day is in French
$day = $daysInFrench[$day] ?? $day;

// Validate if the day is valid (lundi, mardi, mercredi...)
$validDays = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

if (!in_array($day, $validDays)) {
    // Default to today's day in French if invalid
    $day = $daysInFrench[strtolower(date('l'))];
}

// Requête SQL pour récupérer les sessions
$sessions_sql = "
    SELECT 
        sp.id AS id_sp,
        s.id,
        u.nom, 
        u.prenom, 
        s.libelle, 
        sp.day, 
        sp.start_time, 
        sp.end_time, 
        s.genre, 
        s.logo, 
        l.name AS location_name
    FROM 
        sessions s
    JOIN 
        session_planning sp ON s.id = sp.session_id
    JOIN 
        locations l ON s.location_id = l.id
    JOIN 
        coaches c ON s.coach_id = c.id
    JOIN 
        users u ON c.user_id = u.id where sp.day='$day' and l.name not in ('Reaxing' , 'I-motion' , 'Coaching Privé');
";

$sessions_result = $conn->query($sessions_sql);
$sessions = [];
if ($sessions_result->num_rows > 0) {
    while ($row = $sessions_result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

// Requête SQL pour récupérer les salles
$locations_sql = "SELECT * FROM `locations` WHERE id not in (13,12,18);";
$locations_result = $conn->query($locations_sql);
$locations = [];
if ($locations_result->num_rows > 0) {
    while ($row = $locations_result->fetch_assoc()) {
        $locations[] = $row['name'];
    }
}

// Fonction pour vérifier si une session commence dans un créneau horaire
function sessionStartsInTimeSlot($session, $time_slot)
{
    $start_time = strtotime($session['start_time']);
    $time_slot_parts = explode(' - ', $time_slot);
    $slot_start_time = strtotime($time_slot_parts[0]);
    $slot_end_time = strtotime($time_slot_parts[1]);
    return $start_time >= $slot_start_time && $start_time < $slot_end_time;
}

// Fonction pour calculer le rowspan
function calculateRowspan($session, $time_slots)
{
    $start_time = strtotime($session['start_time']);
    $end_time = strtotime($session['end_time']);
    $rowspan = 0;

    foreach ($time_slots as $time_slot) {
        $time_slot_parts = explode(' - ', $time_slot);
        $slot_start_time = strtotime($time_slot_parts[0]);
        $slot_end_time = strtotime($time_slot_parts[1]);

        if ($start_time < $slot_end_time && $end_time > $slot_start_time) {
            $rowspan++;
        }
    }

    return $rowspan;
}

// Fermer la connexion
$conn->close();
?>


<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Select2 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Ensure select2 is loaded and initialized properly
        if ($.fn.select2) {
            $('.select2').select2();
        }

        // Fetch users and populate the select element
        function populateUsers() {
            $.ajax({
                url: 'get_users.php', // Ensure this URL is correct
                type: 'GET',
                success: function(response) {
                    const $select = $('#users');
                    $select.empty(); // Clear existing options

                    // Add the default placeholder option
                    $select.append(new Option('Sélectionner l\'utilisateur', ''));

                    // Populate the dropdown with user options
                    $.each(response, function(index, user) {
                        $select.append(new Option(user.name, user.id));
                    });

                    $select.select2(); // Reinitialize Select2
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        populateUsers(); // Populate users on page load

        // Handle the session click event
        $(document).on('click', '.session', function() {
            const id = $(this).data('id');
            const id_sp = $(this).data('idsp');
            $.ajax({
                url: 'get_session.php',
                type: 'GET',
                data: {
                    id: id,
                    id_sp
                },
                success: function(response) {
                    const session = JSON.parse(response);

                    $('#reserveModal #activityName').text(session.activity_name || 'N/A');
                    $('#reserveModal #sessionDate').text(session.date || 'N/A');
                    $('#reserveModal #sessionTime').text((session.start_time || '') + ' - ' + (session.end_time || ''));
                    $('#reserveModal #sessionLocation').text(session.location || 'N/A');
                    $('#reserveModal #sessionCoach').text(session.coach_name || 'N/A');
                    $('#reserveModal #maxAttendees').text(session.max_attendees || 'N/A');
                    $('#reserveModal #remainingSlots').text(session.remaining_slots || 'N/A');
                    $('#reserveModal #sessionId').val(session.id || '');
                    $('#reserveModal #sessionIdSp').val(session.id_sp || '');

                    if (session.is_reserved) {
                        $('#reserveButton').hide();
                        $('#cancelButton').show();
                    } else {
                        $('#reserveButton').show();
                        $('#cancelButton').hide();
                    }

                    $('#reserveModal').modal('show');
                }
            });
        });

        // Handle form submission for session reservation
        $('#reserveForm').on('submit', function(event) {
            event.preventDefault();
            const sessionId = $('#sessionId').val();
            const sessionIdSp = $('#sessionIdSp').val();
            const userProfil = <?php echo $_SESSION['profil']; ?>;
            let userId = $('#users').val();

            // Check if userId is empty and profile is 2
            if (userId === "" && userProfil === 2) {
                userId = <?php echo $_SESSION['id']; ?>;
            } else {
                userId = $('#users').val();
            }

            $.ajax({
                url: 'reserve_session.php',
                type: 'GET',
                data: {
                    session_id: sessionId,
                    user_ID: userId,
                    session_IdSp: sessionIdSp
                },
                success: function(response) {
                    if (response.trim() === 'success') {
                        $('#reserveModal').modal('hide');
                    } else {
                        $('#reserveModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    $('#alert-error').text('Une erreur est survenue. Veuillez réessayer.').show();
                }
            });
        });

        // Handle session cancellation
        $('#cancelButton').on('click', function() {
            const sessionId = $('#sessionId').val();

            $.ajax({
                url: 'cancel_session.php',
                type: 'GET',
                data: {
                    session_id: sessionId
                },
                success: function(response) {
                    if (response.trim() === 'success') {
                        $('#reserveModal').modal('hide');
                    } else {
                        $('#reserveModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    $('#alert-error').text('Une erreur est survenue. Veuillez réessayer.').show();
                }
            });
        });

        // Handle the view reserved users button click
        $('#viewReservedUsersButton').on('click', function() {
            const sessionId = $('#sessionId').val();

            $.ajax({
                url: 'fetch_reserved_users.php',
                type: 'GET',
                data: {
                    session_id: sessionId
                },
                success: function(response) {
                    const users = JSON.parse(response);
                    const tbody = $('#reservedUsersTableBody');
                    tbody.empty(); // Clear the table body

                    users.forEach(function(user) {
                        const row = '<tr>' +
                            '<td>' + user.nom + '</td>' +
                            '<td>' + user.prenom + '</td>' +
                            '<td>' + user.email + '</td>' +
                            '<td>' + user.matricule + '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                    $('#reserveModal').modal('hide');
                    $('#reservedUsersModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Une erreur est survenue. Veuillez réessayer.');
                }
            });
        });

        // Toggle the view reserved users button visibility based on sessionId
        $('#reserveModal').on('show.bs.modal', function() {
            if ($('#sessionId').val()) {
                $('#viewReservedUsersButton').show();
            } else {
                $('#viewReservedUsersButton').hide();
            }
        });

        // Close alert messages on click
        $(document).on('click', '.alert .btn-close', function() {
            $(this).closest('.alert').fadeOut('slow');
        });
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

    /* Table styles */
    .table-responsive {
        margin-top: 20px;
    }

    /* Table background with dark overlay */
    .table {
        position: relative;
        /* background-image: url('../assets/img/capitalsoft/logo_light.png'); */
        background-repeat: no-repeat;
        background-size: cover;
        /* Ensures the image covers the entire table */
        background-position: center;
        width: 100%;
        /* Ensure the table takes up 100% width */
        height: auto;
        /* Adjust height as per content */
        color: white;
        /* Set text color to white */
    }

    /* Dark overlay with opacity */
    .table::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Dark overlay with 50% opacity */
        z-index: 1;
    }

    /* Table content should be above the overlay */
    .table td,
    .table th {
        position: relative;
        z-index: 2;
    }

    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
        padding: 15px;
        height: 70px;
        border: none;
        background-color: none;
        font-weight: 200;
    }


    /* Event cell styles */
    .session {
        background-color: #f0f0f0;
        /* Default gray color */
        color: #333;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
    }

    .session:hover {
        background-color: #f0cf6e;
        transform: scale(1.02);
    }

    /* Header styles */
    .table thead th {
        background-color: #262a2d;
        ;
        color: white;
        /* border-radius: 50px; */
    }

    /* Animation classes */
    .session.fade-in-right {
        animation: fadeInRight 1s ease-in-out;
    }

    .session.fade-out-left {
        animation: fadeOutLeft 1s ease-in-out;
    }
</style>


<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Réserver une Séance</h3>
        </div>
    </div>
    <div id="alert-success" class="alert alert-success alert-dismissible fade mt-3" role="alert" style="display: none;">
        Opération réussie !
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div id="alert-error" class="alert alert-danger alert-dismissible fade mt-3" role="alert" style="display: none;">
        Une erreur est survenue. Veuillez réessayer.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php
    // Liste des jours en français
    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    echo "<div class='d-flex justify-content-center gap-2'>";
    foreach ($jours as $jour) {
        echo "<a href='index.php?day=$jour' class='btn btn-dark'>" . ucfirst($jour) . "</a>";
    }
    echo "</div>";
    ?>

    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Horaire</th>
                    <?php foreach ($locations as $location): ?>
                        <th><?= htmlspecialchars($location) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Suivi des rowspan pour chaque salle
                $rowspan_tracking = [];

                // Parcourir les créneaux horaires
                foreach ($time_slots as $time_slot):
                    echo "<tr>";
                    echo "<td class='bg-dark text-white'>$time_slot</td>";

                    foreach ($locations as $location):
                        if (isset($rowspan_tracking[$location]) && $rowspan_tracking[$location] > 0) {
                            $rowspan_tracking[$location]--;
                            continue;
                        }

                        // Filtrer les sessions pour la salle et le créneau horaire
                        $filtered_sessions = array_filter($sessions, function ($session) use ($location, $time_slot) {
                            return $session['location_name'] === $location && sessionStartsInTimeSlot($session, $time_slot);
                        });

                        if (!empty($filtered_sessions)) {
                            foreach ($filtered_sessions as $session) {
                                $rowspan = calculateRowspan($session, $time_slots);


                                echo "<td class='session bg-dark text-white' style='text-align: center; vertical-align: middle;text-transform: uppercase;border:2px solid #fff' rowspan='$rowspan' data-id='{$session['id']}' data-idsp='{$session['id_sp']}' data-bs-toggle='modal' data-bs-target='#reserveModal'>";
                                echo "<strong>{$session['libelle']}</strong><br>";

                                echo "</td>";

                                // Mettre à jour le suivi du rowspan
                                $rowspan_tracking[$location] = $rowspan - 1;
                            }
                        } else {
                            echo "<td></td>";
                        }

                    endforeach;

                    echo "</tr>";
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
</div>



<div class="modal fade" id="reserveModal" tabindex="-1" aria-labelledby="reserveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reserveModalLabel">Réserver une Séance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reserveForm">
                    <?php
                    $session_profil = $_SESSION['profil'];
                    if ($session_profil == 2) {; ?>
                        <div class="mb-3">
                            <label for="users">Adhérents</label>
                            <select name="users" id="users" class="form-control select2" style="width: 100%;"></select>
                        </div>
                    <?php
                    }; ?>
                    <div class="mb-3">
                        <label for="activityName" class="form-label">Activité</label>
                        <div id="activityName"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sessionDate" class="form-label">Date</label>
                        <div id="sessionDate"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sessionTime" class="form-label">Heure</label>
                        <div id="sessionTime"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sessionLocation" class="form-label">Lieu</label>
                        <div id="sessionLocation"></div>
                    </div>
                    <div class="mb-3">
                        <label for="sessionCoach" class="form-label">Coach</label>
                        <div id="sessionCoach"></div>
                    </div>
                    <div class="mb-3">
                        <label for="maxAttendees" class="form-label">Nombre de places</label>
                        <div id="maxAttendees"></div>
                    </div>
                    <div class="mb-3">
                        <label for="remainingSlots" class="form-label">Disponibles</label>
                        <div id="remainingSlots"></div>
                    </div>
                    <input type="hidden" id="sessionId" name="session_id">
                    <input type="hidden" id="sessionIdSp" name="session_id_Sp">

                    <button type="submit" class="btn btn-primary" id="reserveButton">Réserver</button>
                    <button type="button" id="cancelButton" class="btn btn-danger" style="display: none;">Annuler la Réservation</button>
                    <button type="button" id="viewReservedUsersButton" class="btn btn-info" style="display: none;">Consulter les utilisateurs réservés</button>
                </form>
            </div>

            <!-- Include jQuery and Select2 -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select" />

        </div>
    </div>
</div>

<div class=" modal fade" id="reservedUsersModal" tabindex="-1" aria-labelledby="reservedUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservedUsersModalLabel">Utilisateurs réservés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Matricule</th>
                        </tr>
                    </thead>
                    <tbody id="reservedUsersTableBody">
                        <!-- User data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php
require "../inc/footer.php";
?>
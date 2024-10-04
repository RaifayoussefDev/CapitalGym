<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
$coach_select_sql = "SELECT coaches.id AS coach_id, users.id as users_id , users.nom, users.prenom, users.matricule FROM coaches JOIN users ON coaches.user_id = users.id";
$coach_select_result = $conn->query($coach_select_sql);

$conn->close();
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5em 1em;
        margin: 0 0.25em;
        border: 1px solid #007bff;
        border-radius: 0.25em;
        color: #007bff;
        background: white;
        text-decoration: none;
    }
</style>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Emploi du Temps des Coachs</h3>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="col-md-12">
                        <label for="coach-select" class="me-2">Choisir un coach :</label>
                        <select id="coach-select" name="coach-select" class="form-control">
                            <option value="">-- Choisir un coach --</option>
                            <?php while ($coach = $coach_select_result->fetch_assoc()) : ?>
                                <option value="<?php echo $coach['users_id']; ?>">
                                    <?php echo htmlspecialchars($coach['nom'] . ' ' . $coach['prenom'] . ' (' . $coach['matricule'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <BR></BR>
                    <div class="table-responsive">
                        <table id="multi-filter-select-planning" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Time Slot</th>
                                    <th>Matricule</th>
                                    <th>Lundi</th>
                                    <th>Mardi</th>
                                    <th>Mercredi</th>
                                    <th>Jeudi</th>
                                    <th>Vendredi</th>
                                    <th>Samedi</th>
                                    <th>Dimanche</th>
                                </tr>
                            </thead>
                            <tbody id="planning-table-body">
                                <!-- Rows will be populated dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Heure</th>
                                    <th>Matricule</th>
                                    <th>Lundi</th>
                                    <th>Mardi</th>
                                    <th>Mercredi</th>
                                    <th>Jeudi</th>
                                    <th>Vendredi</th>
                                    <th>Samedi</th>
                                    <th>Dimanche</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
    $(document).ready(function() {
        $('#coach-select').on('change', function() {
            var coach_id = $(this).val();

            if (coach_id) {
                // Make an AJAX request to fetch the planning
                $.ajax({
                    url: 'fetch_planning.php',
                    type: 'POST',
                    data: {
                        coach_id: coach_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        // Clear the table body
                        $('#planning-table-body').empty();

                        if (data.length > 0) {
                            $.each(data, function(index, coach) {
                                $('#planning-table-body').append(
                                    '<tr>' +
                                    '<td style="background-color: #262a2d;color:white;font-weight: bold;width:10% ">' + coach.time_slot + '</td>' +
                                    '<td>' + coach.coach_matricule + '</td>' +
                                    '<td>' + (coach.Lundi || '-') + '</td>' +
                                    '<td>' + (coach.Mardi || '-') + '</td>' +
                                    '<td>' + (coach.Mercredi || '-') + '</td>' +
                                    '<td>' + (coach.Jeudi || '-') + '</td>' +
                                    '<td>' + (coach.Vendredi || '-') + '</td>' +
                                    '<td>' + (coach.Samedi || '-') + '</td>' +
                                    '<td>' + (coach.Dimanche || '-') + '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            $('#planning-table-body').append('<tr><td colspan="10">Aucun planning disponible</td></tr>');
                        }
                    }
                });
            } else {
                // If no coach selected, show a default message
                $('#planning-table-body').html('<tr><td colspan="10">Sélectionnez un coach pour voir le planning</td></tr>');
            }
        });
    });
</script>

<?php require "../inc/footer_report.php"; ?>
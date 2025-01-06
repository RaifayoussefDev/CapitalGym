<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Retrieve dates from the form (POST method)
$date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
$date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;

// Base SQL query
$users_sql = "SELECT 
    r.date_reservation, 
    u.matricule, 
    u.nom, 
    u.prenom,
    l.name AS local, 
    a.nom AS activite_nom,
    sp.day,
    sp.start_time,
    p.pack_name AS package_name
FROM 
    reservations r
JOIN 
    users u ON u.id = r.user_id
JOIN 
    abonnements ab ON ab.user_id = u.id
JOIN 
    packages p ON p.id = ab.type_abonnement
JOIN 
    session_planning sp ON sp.id = r.session_planning_id
JOIN 
    sessions s ON s.id = sp.session_id
JOIN 
    locations l ON l.id = s.location_id
JOIN 
    activites a ON a.id = s.activite_id";

// Add date filter if both dates are provided
if (!empty($date_debut) && !empty($date_fin)) {
    $users_sql .= " WHERE r.date_reservation BETWEEN '$date_debut' AND '$date_fin'";
}

// Execute query
$users_result = $conn->query($users_sql);

// Process results
$users = [];
if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    // Debugging: Output SQL query if no results are found
    echo "No results found. Query: " . $users_sql;
}

$conn->close();
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5em 1em;
        /* Adjust padding for buttons */
        margin: 0 0.25em;
        /* Space between buttons */
        border: 1px solid #007bff;
        /* Border color */
        border-radius: 0.25em;
        /* Rounded corners */
        color: #007bff;
        /* Text color */
        background: white;
        /* Background color */
        text-decoration: none;
        /* Remove underline */
    }
</style>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Liste des Reservations</h3>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="table-responsive">
                        <form id="filter-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_debut">Date Début</label>
                                        <input type="date" name="date_debut" id="date_debut" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_fin">Date Fin</label>
                                        <input type="date" name="date_fin" id="date_fin" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table id="multi-filter-select-reservation" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom et Prénom</th>
                                    <th>DATE DE RESERVATION</th>
                                    <th>JOUR</th>
                                    <th>HEURE</th>
                                    <th>COURS</th>
                                    <th>SALLE</th>
                                    <th>Type d'abonnement</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateDebutInput = document.getElementById('date_debut');
        const dateFinInput = document.getElementById('date_fin');

        // Initialize DataTable
        const table = $("#multi-filter-select-reservation").DataTable({
            pageLength: 12,
            dom: 'Bfrtip', // Enable buttons
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>',
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>',
                    className: 'btn btn-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    className: 'btn btn-primary'
                }
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    const column = this;
                    const select = $('<select class="form-select"><option value=""></option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function() {
                            const val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? `^${val}$` : '', true, false).draw();
                        });

                    column.data().unique().sort().each(function(d) {
                        if (d) select.append(`<option value="${d}">${d}</option>`);
                    });
                });
            }
        });

        // Function to fetch and update the table
        const fetchReservations = () => {
            const dateDebut = dateDebutInput.value;
            const dateFin = dateFinInput.value;

            fetch('fetch_reservations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `date_debut=${dateDebut}&date_fin=${dateFin}`
                })
                .then(response => response.json())
                .then(data => {
                    table.clear(); // Clear the DataTable

                    if (data.length > 0) {
                        data.forEach(row => {
                            table.row.add([
                                row.matricule || 'N/A',
                                `${row.nom || ''} ${row.prenom || ''}`.trim(),
                                new Date(row.date_reservation).toLocaleDateString() || 'N/A',
                                row.day || 'N/A',
                                row.start_time || 'N/A',
                                row.activite_nom || 'N/A',
                                row.local || 'N/A',
                                row.package_name || 'N/A'
                            ]);
                        });
                    } else {
                        table.row.add(['Aucun résultat trouvé', '', '', '', '', '', '', '']);
                    }

                    table.draw(); // Redraw the table
                })
                .catch(error => console.error('Error:', error));
        };

        // Fetch data on page load
        fetchReservations();

        // Fetch data when date inputs change
        dateDebutInput.addEventListener('change', fetchReservations);
        dateFinInput.addEventListener('change', fetchReservations);
    });
</script>


<?php require "../inc/footer_report.php";

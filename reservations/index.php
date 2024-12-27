<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
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
    activites a ON a.id = s.activite_id;
";
$users_result = $conn->query($users_sql);

$users = [];
if ($users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
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
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom et Prénom</th>
                                    <th>DATE DE RESERVATION</th>
                                    <th>JOUR</th>
                                    <th>HEURE</th>
                                    <th>COACH</th>
                                    <th>SALLE</th>
                                    <th>Type d'abonnement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['matricule']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?></td>
                                            <td class="text-capitalize">
                                                <?php
                                                echo htmlspecialchars(date("d/m/Y", strtotime($user['date_reservation'])));
                                                ?>
                                            </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['day']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['start_time']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['local']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['activite_nom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['package_name']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6">Aucun chèque disponible</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom et Prénom</th>
                                    <th>DATE DE RESERVATION</th>
                                    <th>JOUR</th>
                                    <th>HEURE</th>
                                    <th>COACH</th>
                                    <th>SALLE</th>
                                    <th>Type d'abonnement</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../inc/footer_report.php";

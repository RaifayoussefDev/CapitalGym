<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
$users_sql = "SELECT u.id, nom , prenom , phone ,cin ,n_dossier, email , u.genre, p.pack_name , etat , matricule , a.date_fin ,a.date_debut FROM `users` u , abonnements a , packages p WHERE u.id=a.user_id and a.type_abonnement=p.id AND a.id = (
    SELECT MAX(a2.id)
    FROM abonnements a2
    WHERE a2.user_id = u.id
);";
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
        <h3 class="fw-bold mb-3">Abonnement</h3>
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
                                    <th>Numéro GSM</th>
                                    <th>N° Dossier</th>
                                    <th>Type d'abonnement</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr data-user-id="<?php echo $user['id']; ?>">
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['matricule']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['n_dossier']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['pack_name']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['date_debut']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['date_fin']); ?></td>
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
                                    <th>Numéro GSM</th>
                                    <th>N° Dossier</th>
                                    <th>Type d'abonnement</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
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

<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
$users_sql = "SELECT 
    u.id, 
    u.nom, 
    u.prenom, 
    u.phone, 
    u.cin, 
    u.email,
    u.n_dossier,
    DATE_FORMAT(u.date_naissance, '%d/%m/%Y') AS date_naissance,
    u.genre, 
    p.pack_name, 
    u.etat, 
    u.matricule, 
    GROUP_CONCAT(ua.activite_id ORDER BY ua.activite_id ASC) AS activites_list,
GROUP_CONCAT(ua.periode_activites ORDER BY ua.activite_id ASC) AS activites_periode
FROM 
    users u
JOIN 
    abonnements a ON u.id = a.user_id
JOIN 
    packages p ON a.type_abonnement = p.id
LEFT JOIN 
    user_activites ua ON ua.user_id = u.id
WHERE
 a.id = (
    SELECT MAX(a2.id)
    FROM abonnements a2
    WHERE a2.user_id = u.id
)
GROUP BY 
    u.id;";
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
        <h3 class="fw-bold mb-3">Liste des adhérents</h3>
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
                                    <th>CIN</th>
                                    <th>Numéro GSM</th>
                                    <th>N° Dossier</th>
                                    <th>Genre</th>
                                    <th>Date de Naissance</th>
                                    <th>Email</th>
                                    <th>Type d'abonnement</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr data-user-id="<?php echo $user['id']; ?>">
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['matricule']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['cin']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['n_dossier']); ?></td>
                                            <td class="text-capitalize">
                                                <?php
                                                if ($user['genre'] === 'M') {
                                                    echo 'Homme';
                                                } elseif ($user['genre'] === 'F') {
                                                    echo 'Femme';
                                                } else {
                                                    echo 'Non spécifié'; // If genre is not M or F
                                                }
                                                ?>
                                            </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['date_naissance']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td class="text-capitalize">
                                                <?php
                                                if ($user['pack_name'] === 'Familial') {
                                                    // Convert `activites_list` and `activites_periode` to arrays
                                                    $activites_list = $user['activites_list'];
                                                    $activites_periode = $user['activites_periode'];

                                                    // Check specific conditions for Familial Gold and Familial Platinum
                                                    if (empty($activites_list)) {
                                                        echo "Familial Silver";
                                                    } elseif ($activites_list === '53' && $activites_periode = '12'){
                                                        echo "Familial Gold";
                                                    } elseif ($activites_list === '53,54,55,56' && $activites_periode = '12,10,10,10') {
                                                        echo "Familial Platinum";
                                                    } else {
                                                        // Default display for other cases with "Familial" pack name
                                                        echo "Familial";
                                                    }
                                                } else {
                                                    // For non-familial packs, simply display the pack name
                                                    echo htmlspecialchars(ucfirst($user['pack_name']));
                                                }
                                                ?>
                                            </td>


                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['etat']); ?></td>
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
                                    <th>CIN</th>
                                    <th>Numéro GSM</th>
                                    <th>N° Dossier</th>
                                    <th>Genre</th>
                                    <th>Email</th>
                                    <th>Type d'abonnement</th>
                                    <th>Statut</th>
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

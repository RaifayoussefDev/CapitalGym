<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
$users_sql = "SELECT u.id, nom , prenom , phone ,cin , email , u.genre, p.pack_name , etat , matricule FROM `users` u , abonnements a , packages p WHERE u.id=a.user_id and a.type_abonnement=p.id;";
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
                                    <th>Genre</th>
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
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td class="text-capitalize">
                                                <?php
                                                if ($user['pack_name'] === 'familial') {
                                                    // Extraire les valeurs des activités et des périodes sous forme de tableaux
                                                    $activites_list = !empty($user['activites_list']) ? explode(',', $user['activites_list']) : [];
                                                    $activites_periode = !empty($user['activites_periode']) ? explode(',', $user['activites_periode']) : [];

                                                    // Vérifier les conditions spécifiques
                                                    if (empty($activites_list)) {
                                                        echo "Familial Silver";
                                                    } elseif (count($activites_list) === 1 && $activites_list[0] == 53 && $activites_periode[0] == 12) {
                                                        echo "Familial Gold";
                                                    } elseif (
                                                        count($activites_list) === 4 &&
                                                        $activites_list === ['53', '54', '55', '56'] &&
                                                        $activites_periode === ['12', '10', '10', '10']
                                                    ) {
                                                        echo "Familial Platinum";
                                                    } else {
                                                        // Si aucune condition spécifique n'est remplie, afficher le nom du pack tel quel
                                                        echo "Familial";
                                                    }
                                                } else {
                                                    // Si le pack n'est pas "familial", afficher simplement le nom du pack
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

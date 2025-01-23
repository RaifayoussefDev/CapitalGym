<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch users using your SQL query
$users_sql = "SELECT u.id, u.nom,u.n_dossier , u.prenom,u.phone, p.pack_name, a.date_abonnement,tp.type, py.date_paiement, u.matricule, a.date_debut, a.date_fin,
    SUM(
        CASE 
            -- If payment is by cheque and cheque status is 'payer' or 'en cours', include montant_paye in the sum
            WHEN py.type_paiement_id = 3 AND ch.statut IN ('payer', 'en cours') THEN py.montant_paye
            -- If cheque status is 'non payer', do not include montant_paye in the sum
            WHEN py.type_paiement_id = 3 AND ch.statut = 'non payer' THEN 0
            -- For all other payment types, include montant_paye in the sum
            ELSE py.montant_paye
        END
    ) AS montant_total, py.total,
    sp.nom AS saisie_par_nom, sp.prenom AS saisie_par_prenom -- Retrieve the name and surname of 'saisie_par'
FROM users u
JOIN abonnements a ON u.id = a.user_id
JOIN packages p ON a.type_abonnement = p.id
JOIN payments py ON a.id = py.abonnement_id
JOIN type_paiements tp ON py.type_paiement_id=tp.id
LEFT JOIN cheque ch ON py.id = ch.payment_id -- Join with cheque table to check status for cheque payments
LEFT JOIN users sp ON u.saisie_par = sp.id -- Self join to get the 'saisie_par' user details
GROUP BY u.id, u.nom, u.prenom, p.pack_name, u.matricule, a.date_debut, a.date_fin, sp.nom, sp.prenom;";
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
        <h3 class="fw-bold mb-3">Chiffre d'affaire</h3>
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
                                    <th>N° Dossier</th>
                                    <th>Date Abonnement</th>
                                    <th>Commerçant</th>
                                    <th>Type d'abonnement</th>
                                    <th>Montant abonnement</th>
                                    <th>Montant encaissé</th>
                                    <th>Reliquat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr data-user-id="<?php echo $user['id']; ?>">
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['matricule']); ?> </td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['n_dossier']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['date_abonnement']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['saisie_par_nom']); ?> <?php echo htmlspecialchars($user['saisie_par_prenom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['pack_name']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['total']); ?> MAD</td>    
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['montant_total']); ?> MAD</td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($user['total']) - htmlspecialchars($user['montant_total']); ?> MAD</td>
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
                                    <th>N° Dossier</th>
                                    <th>Date Abonnement</th>
                                    <th>Commerçant</th>
                                    <th>Type d'abonnement</th>
                                    <th>Montant abonnement</th>
                                    <th>Montant encaissé</th>
                                    <th>Reliquat</th>
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

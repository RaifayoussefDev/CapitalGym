<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch cheques using your SQL query
$cheques_sql = "
    SELECT C.id, u.nom, u.prenom, `nomTitulaire`, `numeroCheque`, montant_paye, `dateEmission`, `banqueEmettrice`, `numeroCompte`, `id_utilisateur`, `payment_id`, `statut`
    FROM `cheque` C
    JOIN users u ON C.id_utilisateur = u.id
    JOIN abonnements a ON C.abonnement_id = a.id
    JOIN payments p ON p.id = C.payment_id;
";
$cheques_result = $conn->query($cheques_sql);

$cheques = [];
if ($cheques_result->num_rows > 0) {
    while ($row = $cheques_result->fetch_assoc()) {
        $cheques[] = $row;
    }
}

$conn->close();
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5em 1em; /* Adjust padding for buttons */
    margin: 0 0.25em; /* Space between buttons */
    border: 1px solid #007bff; /* Border color */
    border-radius: 0.25em; /* Rounded corners */
    color: #007bff; /* Text color */
    background: white; /* Background color */
    text-decoration: none; /* Remove underline */
}



</style>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Rapport des chèques</h3>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Numéro de Chèque</th>
                                    <th>Adhérent</th>
                                    <th>Date d'Émission</th>
                                    <th>Banque</th>
                                    <th>Montant Payer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cheques) > 0) : ?>
                                    <?php foreach ($cheques as $cheque) : ?>
                                        <tr data-cheque-id="<?php echo $cheque['id']; ?>">
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['numeroCheque']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['nom']); ?> <?php echo htmlspecialchars($cheque['prenom']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['dateEmission']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['banqueEmettrice']); ?></td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['montant_paye']); ?> MAD</td>
                                            <td class="text-capitalize"><?php echo htmlspecialchars($cheque['statut']); ?></td>
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
                                    <th>Numéro de Chèque</th>
                                    <th>Adhérent</th>
                                    <th>Date d'Émission</th>
                                    <th>Banque</th>
                                    <th>Montant Payer</th>
                                    <th>Status</th>
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
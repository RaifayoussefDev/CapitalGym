<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch cheques using your SQL query
$cheques_sql = "
    SELECT C.id, u.nom,u.prenom, `nomTitulaire`, `numeroCheque`, montant_paye , `dateEmission`, `banqueEmettrice`, `numeroCompte`, `id_utilisateur`, `payment_id`, `statut` FROM `cheque` c , users u , abonnements a , payments p  WHERE c.id_utilisateur=u.id and c.abonnement_id=a.id and p.id=c.payment_id;
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

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Gestion des Chèques</h3>
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

                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cheques) > 0) : ?>
                                    <?php foreach ($cheques as $cheque) : ?>
                                        <tr data-cheque-id="<?php echo $cheque['id']; ?>">
                                            <td><?php echo htmlspecialchars($cheque['numeroCheque']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['nom']); ?> <?php echo htmlspecialchars($cheque['prenom']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['dateEmission']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['banqueEmettrice']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['montant_paye']); ?> MAD</td>
                                            <td>
                                                <?php
                                                $statut = htmlspecialchars($cheque['statut']);
                                                // Define the badge class based on the status
                                                $badgeClass = '';
                                                if ($statut === 'payer') {
                                                    $badgeClass = 'badge bg-success'; // Green badge for 'payer'
                                                } elseif ($statut === 'non payer') {
                                                    $badgeClass = 'badge bg-danger'; // Red badge for 'non payer'
                                                } else {
                                                    $badgeClass = 'badge bg-secondary'; // Default badge for other statuses
                                                }
                                                ?>
                                                <!-- Display the status with the badge and capitalize -->
                                                <span id="status_<?php echo $cheque['id']; ?>" class="<?php echo $badgeClass; ?> text-capitalize">
                                                    <?php echo $statut; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <button class="btn btn-warning" onclick="openStatusModal(<?php echo $cheque['id']; ?>, '<?php echo htmlspecialchars($cheque['statut']); ?>')">Changer Statut</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7">Aucun chèque disponible</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Numéro de Chèque</th>
                                    <th>Adhérent</th>
                                    <th>Date d'Émission</th>
                                    <th>Banque</th>
                                    <th>Status</th>
                                    <th style="visibility: hidden;">Actions</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for changing cheque status -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Changer Statut du Chèque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="chequeId" name="cheque_id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Nouveau Statut</label>
                        <select id="newStatus" name="status" class="form-control" required>
                            <option value="en cours">En cours</option>
                            <option value="payer">Payer</option>
                            <option value="non payer">Non payer</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="updateChequeStatus()">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to open the modal and set the cheque ID
    function openStatusModal(chequeId, currentStatus) {
        $('#chequeId').val(chequeId);
        $('#newStatus').val(currentStatus);

        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    }

    function updateChequeStatus() {
        let formData = $('#statusForm').serialize();

        $.ajax({
            url: 'update_cheque_status.php', // PHP script to handle status update
            method: 'POST',
            data: formData,
            success: function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    // Display a success notification
                    $.notify({
                        message: 'Statut mis à jour avec succès!'
                    }, {
                        type: 'success',
                        delay: 2000,
                        placement: {
                            from: "top",
                            align: "right"
                        }
                    });

                    // Find the row and status span by cheque ID
                    let chequeId = data.chequeId;
                    let newStatus = data.newStatus;
                    let badgeClass = '';

                    // Update the badge class based on the new status
                    if (newStatus === 'payer') {
                        badgeClass = 'badge bg-success'; // Green badge for 'payer'
                    } else if (newStatus === 'non payer') {
                        badgeClass = 'badge bg-danger'; // Red badge for 'non payer'
                    } else {
                        badgeClass = 'badge bg-secondary'; // Default badge for other statuses
                    }

                    // Update the status span with the new status and class
                    let statusSpan = $(`#status_${chequeId}`);
                    statusSpan.text(newStatus); // Update the text
                    statusSpan.attr('class', badgeClass + ' text-capitalize'); // Update the class

                    // Hide the modal
                    var statusModalEl = document.getElementById('statusModal');
                    var modal = bootstrap.Modal.getInstance(statusModalEl);
                    modal.hide();
                } else {
                    // Display an error notification
                    $.notify({
                        message: 'Erreur lors de la mise à jour du statut: ' + data.message
                    }, {
                        type: 'danger',
                        delay: 2000,
                        placement: {
                            from: "top",
                            align: "right"
                        }
                    });
                }
            },
            error: function(error) {
                console.error('Error updating cheque status:', error);
                // Display an error notification
                $.notify({
                    message: 'Erreur lors de la mise à jour du statut. Veuillez réessayer plus tard.'
                }, {
                    type: 'danger',
                    delay: 2000,
                    placement: {
                        from: "top",
                        align: "right"
                    }
                });
            }
        });
    }
</script>

<?php
require "../inc/footer.php";
?>
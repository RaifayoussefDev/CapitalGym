<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch materiels
$materiels_sql = "SELECT m.id, m.nom, l.name as location_name, l.id as location_id , m.date_achat FROM materiel m LEFT JOIN locations l ON m.emplacement = l.id;";
$materiels_result = $conn->query($materiels_sql);

$materiels = [];
if ($materiels_result->num_rows > 0) {
    while ($row = $materiels_result->fetch_assoc()) {
        $materiels[] = $row;
    }
}

// Fetch locations for the dropdown
$locations_sql = "SELECT id, name FROM locations";
$locations_result = $conn->query($locations_sql);

$locations = [];
if ($locations_result->num_rows > 0) {
    while ($row = $locations_result->fetch_assoc()) {
        $locations[] = $row;
    }
}

$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Function to handle the fade-in and fade-out animations
        function animateAlert(alertId) {
            var alert = $('#' + alertId);
            alert.addClass('fade-in-right');
            setTimeout(function() {
                alert.addClass('fade-out-left');
                setTimeout(function() {
                    alert.alert('close');
                }, 1000); // Time for fade-out animation
            }, 5000); // Display time before starting fade-out
        }

        // Apply the animations to the alerts
        if ($('#alert-success').length) {
            animateAlert('alert-success');
        } else if ($('#alert-error').length) {
            animateAlert('alert-error');
        }
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
</style>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Matériels</h3>
        </div>
        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addMaterielModal">
            <i class="fa fa-plus"></i> Ajouter Matériel
        </button>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="materielTable" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Emplacement</th>
                                    <th>Date d'Achat</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($materiels) > 0) : ?>
                                    <?php foreach ($materiels as $materiel) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($materiel['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($materiel['location_name']); ?></td>
                                            <td><?php echo htmlspecialchars($materiel['date_achat']); ?></td>
                                            <td>
                                                <!-- Edit button with correct data attributes -->
                                                <button
                                                    class="btn btn-warning btn-edit"
                                                    data-id="<?php echo $materiel['id']; ?>"
                                                    data-nom="<?php echo htmlspecialchars($materiel['nom']); ?>"
                                                    data-emplacement="<?php echo htmlspecialchars($materiel['location_id']); ?>"
                                                    data-date_achat="<?php echo htmlspecialchars($materiel['date_achat']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editMaterielModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Delete button with correct data attribute -->
                                                <button
                                                    class="btn btn-danger btn-delete"
                                                    data-id="<?php echo $materiel['id']; ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteMaterielModal">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4">No materiels available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Materiel Modal -->
    <div class="modal fade" id="addMaterielModal" tabindex="-1" aria-labelledby="addMaterielModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMaterielModalLabel">Ajouter Matériel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/add_materiel.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="materielName">Nom du matériel</label>
                            <input type="text" class="form-control" id="materielName" name="nom" required>
                        </div>

                        <div class="form-group">
                            <label for="materielEmplacement">Emplacement</label>
                            <select class="form-control" id="materielEmplacement" name="emplacement" required>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="materielDateAchat">Date d'Achat</label>
                            <input type="date" class="form-control" id="materielDateAchat" name="date_achat" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Materiel Modal -->
    <div class="modal fade" id="editMaterielModal" tabindex="-1" aria-labelledby="editMaterielModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMaterielModalLabel">Modifier Matériel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/edit_materiel.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editMaterielId" name="id">
                        <div class="form-group">
                            <label for="editMaterielName">Nom du matériel</label>
                            <input type="text" class="form-control" id="editMaterielName" name="nom" required>
                        </div>

                        <div class="form-group">
                            <label for="editMaterielEmplacement">Emplacement</label>
                            <select class="form-control" id="editMaterielEmplacement" name="emplacement" required>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editMaterielDateAchat">Date d'Achat</label>
                            <input type="date" class="form-control" id="editMaterielDateAchat" name="date_achat" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Materiel Modal -->
    <div class="modal fade" id="deleteMaterielModal" tabindex="-1" aria-labelledby="deleteMaterielModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMaterielModalLabel">Supprimer Matériel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce matériel ?</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteMaterielForm" action="actions/delete_materiel.php" method="post">
                        <input type="hidden" id="deleteMaterielId" name="id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Open edit modal and populate form fields
        $('#materielTable').on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var nom = $(this).data('nom');
            var emplacement = $(this).data('emplacement');
            var dateAchat = $(this).data('date_achat');

            $('#editMaterielId').val(id);
            $('#editMaterielName').val(nom);
            $('#editMaterielEmplacement').val(emplacement);
            $('#editMaterielDateAchat').val(dateAchat);
        });

        // Open delete modal and set ID
        $('#materielTable').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            $('#deleteMaterielId').val(id);
        });
    });
</script>

<?php
require "../inc/footer.php";
?>
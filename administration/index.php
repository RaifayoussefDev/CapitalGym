<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch activities
$activites_sql = "SELECT id, nom, prix,sex FROM activites";
$activites_result = $conn->query($activites_sql);

$activites = [];
if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
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
</style>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Activités</h3>
        </div>
        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addActivityModal">
            <i class="fa fa-plus"></i> Ajouter Activité
        </button>
    </div>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success') : ?>
        <div id="alert-success" class="alert alert-success alert-dismissible fade show" role="alert">
            Operation was successful!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error') : ?>
        <div id="alert-error" class="alert alert-danger alert-dismissible fade show" role="alert">
            An error occurred. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class=" table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prix</th>
                                    <th>Genre</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($activites) > 0) : ?>
                                    <?php foreach ($activites as $activite) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activite['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($activite['prix']); ?> MAD</td>
                                            <td>
                                                <?php
                                                if ($activite['sex'] === 'M') {
                                                    echo 'Hommes';
                                                } elseif ($activite['sex'] === 'F') {
                                                    echo 'Femmes';
                                                } elseif ($activite['sex'] === 'MF') {
                                                    echo 'Mix';
                                                } else {
                                                    echo 'N/A'; // Handle any unexpected values
                                                }
                                                ?>
                                            </td>
                                            <td style="width: 13%;">
                                                <button class="btn btn-warning btn-edit" data-id="<?php echo $activite['id']; ?>" data-nom="<?php echo htmlspecialchars($activite['nom']); ?>" data-prix="<?php echo htmlspecialchars($activite['prix']); ?>" data-sex="<?php echo htmlspecialchars($activite['sex']); ?>" data-bs-toggle="modal" data-bs-target="#editActivityModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-delete" data-id="<?php echo $activite['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteActivityModal">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4">No activities available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Activity Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addActivityModalLabel">Ajouter Activité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/add_activity.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="activityName">Nom de l'activité</label>
                            <input type="text" class="form-control" id="activityName" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="activityPrice">Prix</label>
                            <input type="number" class="form-control" id="activityPrice" name="prix" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="activitySex">Genre</label>
                            <select class="form-control" id="activitySex" name="sex" required>
                                <option value="M">Hommes</option>
                                <option value="F">Femmes</option>
                                <option value="MF" selected>Mix</option>
                            </select>
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

    <!-- Edit Activity Modal -->
    <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editActivityModalLabel">Modifier Activité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/edit_activity.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editActivityId" name="id">
                        <div class="form-group">
                            <label for="editActivityName">Nom de l'activité</label>
                            <input type="text" class="form-control" id="editActivityName" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="editActivityPrice">Prix</label>
                            <input type="number" class="form-control" id="editActivityPrice" name="prix" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editActivitySex">Sexe</label>
                            <select class="form-control" id="editActivitySex" name="sex" required>
                                <option value="M">Hommes</option>
                                <option value="F">Femmes</option>
                                <option value="MF">Mixte</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Delete Activity Modal -->
    <div class="modal fade" id="deleteActivityModal" tabindex="-1" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteActivityModalLabel">Supprimer Activité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="actions/delete_activity.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="deleteActivityId" name="id">
                        <p>Êtes-vous sûr de vouloir supprimer cette activité ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editActivityModal = document.getElementById('editActivityModal');
        const deleteActivityModal = document.getElementById('deleteActivityModal');

        editActivityModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nom = button.getAttribute('data-nom');
            const prix = button.getAttribute('data-prix');
            const sex = button.getAttribute('data-sex'); // Get the sex attribute

            const modalTitle = editActivityModal.querySelector('.modal-title');
            const inputId = editActivityModal.querySelector('#editActivityId');
            const inputNom = editActivityModal.querySelector('#editActivityName');
            const inputPrix = editActivityModal.querySelector('#editActivityPrice');
            const inputSex = editActivityModal.querySelector('#editActivitySex'); // Select the sex dropdown

            modalTitle.textContent = `Modifier Activité: ${nom}`;
            inputId.value = id;
            inputNom.value = nom;
            inputPrix.value = prix;
            inputSex.value = sex; // Set the sex dropdown value to pre-select the correct option
        });

        deleteActivityModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const inputId = deleteActivityModal.querySelector('#deleteActivityId');
            inputId.value = id;
        });
    });
</script>

<?php
require "../inc/footer.php";
?>
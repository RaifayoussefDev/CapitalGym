<?php
require "../inc/app.php";
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage"; // Replace with your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch chèques
$cheques_sql = "SELECT nomTitulaire, numeroCheque, dateEmission, banqueEmettrice, numeroCompte, u.nom , u.prenom , a.type FROM cheque c , users u , abonnements a WHERE c.id_utilisateur=u.id and c.abonnement_id=a.id;";
$cheques_result = $conn->query($cheques_sql);

$cheques = [];
if ($cheques_result->num_rows > 0) {
    while ($row = $cheques_result->fetch_assoc()) {
        $cheques[] = $row;
    }
}

// Close the connection
$conn->close();
?>

    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Suivis des Chèques</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="chequeTable" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom Titulaire</th>
                                        <th>Numéro du chèque</th>
                                        <th>Date d'encaissement</th>
                                        <th>Banque Émettrice</th>
                                        <th>Numéro de compte</th>
                                        <th>Adhérent</th>
                                        <th>Abonnement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cheques) > 0) : ?>
                                        <?php foreach ($cheques as $cheque) : ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cheque['nomTitulaire']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['numeroCheque']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['dateEmission']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['banqueEmettrice']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['numeroCompte']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['nom']); ?> <?php echo htmlspecialchars($cheque['prenom']); ?></td>
                                                <td><?php echo htmlspecialchars($cheque['type']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7">No chèques available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#chequeTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print','pdf'
                ]
            });

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


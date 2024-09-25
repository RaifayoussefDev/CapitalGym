<?php
require "../inc/app.php";
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage";

// Start session and get the profile
$profil = $_SESSION['profil'];

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch activities
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_result = $conn->query($activites_sql);

// Fetch packages
$package_sql = "SELECT * FROM `packages` ORDER BY `pack_name` ASC";
$package_result = $conn->query($package_sql);

// Fetch payment types
$type_paiements_sql = "SELECT id, type FROM type_paiements";
$type_paiements_result = $conn->query($type_paiements_sql);

$activites = [];
$packages = [];
$type_paiements = [];

if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}
if ($package_result->num_rows > 0) {
    while ($row = $package_result->fetch_assoc()) {
        $packages[] = $row;
    }
}
if ($type_paiements_result->num_rows > 0) {
    while ($row = $type_paiements_result->fetch_assoc()) {
        $type_paiements[] = $row;
    }
}

// Fetch users with their subscription details and activities
$sql = "
    SELECT u.id, etat, nom, prenom, matricule, email, phone, cin, photo, pack_name, date_fin 
    FROM users u
    JOIN abonnements a ON u.id = a.user_id
    JOIN packages p ON p.id = a.type_abonnement;
";

$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Adhérents</h3>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"></h4>
                        <button class="btn btn-dark btn-round ms-auto" id="btn-read-card">
                            <i class="fa fa-credit-card"></i> Lire la carte
                        </button>
                    </div>
                </div>

                <div class="modal fade custom-modal" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form to Add Balance -->
                                <form id="balanceForm">
                                    <input type="hidden" id="userId" name="user_id">
                                    <p id="userName"></p>
                                    <div class="mb-3">
                                        <label for="balance" class="form-label">Ajouter Balance</label>
                                        <input type="number" id="balance" name="balance" class="form-control" placeholder="Enter amount" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Ajouter Balance</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Table to Display Users -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom et Prénom</th>
                                    <th>Email</th>
                                    <th>Télephone</th>
                                    <th>Type d'abonnement</th>
                                    <th>Date Fin d'abonnement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td>
                                                <img src="../assets/img/capitalsoft/profils/<?php echo !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                                            </td>
                                            <td><?php echo htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenom']) . " (" . htmlspecialchars($user['matricule']) . ")"; ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($user['pack_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['date_fin']); ?></td>
                                            <td>
                                                <!-- Button to open modal and add balance -->
                                                <button class="btn btn-info" onclick="openBalanceModal(<?php echo $user['id']; ?>, '<?php echo $user['nom']; ?>', '<?php echo $user['prenom']; ?>')">Ajouter Balance</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7">No data available</td>
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

<script>
    // Function to open balance modal with user data
    function openBalanceModal(userId, nom, prenom) {
        $('#userId').val(userId);
        $('#userName').text(`Nom: ${nom} ${prenom}`);

        // Use Bootstrap 5's JavaScript API to show the modal
        var myModal = new bootstrap.Modal(document.getElementById('userModal'));
        myModal.show(); // Show the modal
    }

    // Function to add balance via form submission
    $('#balanceForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the form from refreshing the page

        let formData = $(this).serialize(); // Serialize the form data

        $.ajax({
            url: 'add_balance.php', // PHP script to handle balance update
            method: 'POST',
            data: formData,
            success: function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    alert('Balance updated successfully!');

                    // Hide modal after successful balance update
                    var myModalEl = document.getElementById('userModal');
                    var modal = bootstrap.Modal.getInstance(myModalEl);
                    modal.hide(); // Close the modal

                    // Clear the envoi_app table after successful update
                    clearEnvoiAppTable();
                } else {
                    alert('Error updating balance: ' + data.message);
                }
            },
            error: function(error) {
                alert('Error updating balance.');
                console.error('Balance update error:', error);
            }
        });
    });

    // Function to check card status and clean envoi_app
    function checkCardStatus() {
        $.ajax({
            url: 'read_card.php', // PHP script to check if the card exists
            method: 'POST',
            success: function(response) {
                let data = JSON.parse(response);

                if (data.success) {
                    let user = data.data;
                    // If card data is found, display user details and clean the table
                    clearEnvoiAppTable();
                    openBalanceModal(user.id, user.nom, user.prenom);
                } else {
                    console.log("Aucune carte détectée.");
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la requête:', error);
            }
        });
    }

    // Function to clear envoi_app table
    function clearEnvoiAppTable() {
        $.ajax({
            url: 'clear_envoi_app.php', // PHP script to clear envoi_app table
            method: 'POST',
            success: function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    console.log('envoi_app table cleared successfully.');
                } else {
                    console.error('Failed to clear envoi_app table:', data.message);
                }
            },
            error: function(error) {
                console.error('Error clearing envoi_app table:', error);
            }
        });
    }

    // Check for card status every 1 second (1000ms)
    setInterval(checkCardStatus, 1000);
</script>


<?php
require "../inc/footer.php";
?>
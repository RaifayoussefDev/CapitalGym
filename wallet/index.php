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
SELECT 
    u.id, 
    u.etat, 
    u.nom, 
    u.prenom, 
    u.matricule, 
    u.email, 
    u.phone, 
    u.cin, 
    u.photo, 
    p.pack_name, 
    a.date_fin, 
    w.balance 
FROM 
    users u
JOIN 
    abonnements a ON u.id = a.user_id
JOIN 
    packages p ON p.id = a.type_abonnement
LEFT JOIN 
    wallet w ON w.user_id = u.id;";

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
        <div class="col-sm-12 col-md-12">
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
                                <!-- Display the user's name and current balance -->
                                <p id="userName"></p> <!-- User's name -->
                                <p id="userBalance" class="fw-bold"></p> <!-- User's current balance -->
                                <!-- Form to Add Balance -->
                                <form id="balanceForm">
                                    <input type="hidden" id="userId" name="user_id">
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
                                    <th>wallet</th>
                                    <th>Type d'abonnement</th>
                                    <th>Date Fin d'abonnement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr data-user-id="<?php echo $user['id']; ?>">
                                            <td>
                                                <img src="../assets/img/capitalsoft/profils/<?php echo !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                                            </td>
                                            <td><?php echo htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenom']) . " (" . htmlspecialchars($user['matricule']) . ")"; ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo isset($user['balance']) ? htmlspecialchars($user['balance']) . " MAD" : "0 MAD"; ?></td>
                                            <td><?php echo htmlspecialchars($user['pack_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['date_fin']); ?></td>
                                            <td>
                                                <!-- Button to open modal and add balance -->
                                                <button class="btn btn-info" onclick="openBalanceModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nom']); ?>', '<?php echo htmlspecialchars($user['prenom']); ?>', <?php echo $user['balance']; ?>)">Ajouter Balance</button>
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
    let isModalOpen = false; // Flag to check if the modal is already open

    // Function to open balance modal with user data
    function openBalanceModal(userId, nom, prenom, balance) {
        if (!isModalOpen) { // Only open if no modal is currently open
            $('#userId').val(userId);
            $('#userName').text(`Nom: ${nom} ${prenom}`);
            $('#userBalance').text(`Balance: ${balance} MAD`); // Display balance

            // Use Bootstrap 5's JavaScript API to show the modal
            var myModal = new bootstrap.Modal(document.getElementById('userModal'));
            myModal.show(); // Show the modal

            isModalOpen = true; // Set flag to true when modal is opened

            // Listen for when the modal is closed to reset the flag
            $('#userModal').on('hidden.bs.modal', function() {
                isModalOpen = false; // Reset the flag when the modal is closed
            });
        } else {
            console.log("Modal is already open, not opening another one.");
        }
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

                    // Fetch updated user data to refresh the balance
                    fetchUpdatedUserBalance(data.userId); // Pass the user ID to fetch the updated balance
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

    // Function to fetch updated user balance and refresh the table
    function fetchUpdatedUserBalance(userId) {
        console.log("Fetching updated balance for user ID:", userId);
        $.ajax({
            url: 'get_user_balance.php', // PHP script to retrieve updated user data
            method: 'POST',
            data: {
                user_id: userId // Send the user ID to get the updated balance
            },
            success: function(response) {
                let data = JSON.parse(response);
                console.log(data); // Log the response for debugging
                if (data.success) {
                    // Update the balance in the table
                    let userRow = $(`#multi-filter-select tbody tr[data-user-id="${userId}"]`);
                    userRow.find('td:nth-child(5)').text(data.balance + ' MAD'); // Update the balance cell
                } else {
                    console.error('Error fetching updated balance:', data.message);
                }
            },
            error: function(error) {
                console.error('Error fetching updated balance.', error);
            }
        });
    }


    // Function to check card status and potentially open the modal
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
                    openBalanceModal(user.id, user.nom, user.prenom, user.balance); // Pass balance
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
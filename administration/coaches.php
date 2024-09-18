<?php
require "../inc/app.php";
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your database password
$dbname = "privilage"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve coaches' details with associated user and activity information
$sql = "
SELECT 
    coaches.id,
    users.nom AS user_nom,
    users.prenom AS user_prenom,
    users.matricule,
    users.email,
    activites.nom AS activite_nom,
    users.photo
FROM 
    coaches
JOIN 
    users ON coaches.user_id = users.id
JOIN 
    activites ON coaches.activite_id = activites.id
";

$result = $conn->query($sql);

$coaches = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $coaches[] = $row;
    }
} else {
    $coaches = [];
}

// Récupérer les activités
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_result = $conn->query($activites_sql);
$activites = [];
$type_paiements = [];

if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}
$conn->close();
?>
<style>
    .drop-area {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s ease;
    }

    .drop-area.dragover {
        border-color: #000;
    }
</style>
<script>
    function displayPhoto(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('drop-area').addEventListener('click', function() {
        document.getElementById('profile-photo').click();
    });

    document.getElementById('drop-area').addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });

    document.getElementById('drop-area').addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });

    document.getElementById('drop-area').addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        var files = e.dataTransfer.files;
        document.getElementById('profile-photo').files = files;
        displayPhoto(document.getElementById('profile-photo'));
    });
</script>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Coachs</h3>
        </div>
    </div>
    <div class="row">
        <!-- Your existing cards here -->
        <div class="col-sm-6 col-md-8">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"></h4>
                        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addCoachModal">
                            <i class="fa fa-plus"></i>
                            Ajouter Coachs
                        </button>
                    </div>
                </div>
                <div class="modal fade" id="addCoachModal" tabindex="-1" aria-labelledby="addCoachModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addCoachModalLabel">
                                    <span class="fw-mediumbold">New</span>
                                    <span class="fw-light">Coach</span>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="coach-form" action="actionsCoaches/add_coach.php" method="post" enctype="multipart/form-data">
                                    <h5>Informations Personnelles</h5>
                                    <section>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <img id="preview" src="#" alt="Aperçu de la photo de profil" style="display:none; max-width: 90px; height: auto" />
                                                    <label>Photo de Profil</label>
                                                    <div id="drop-area" class="drop-area">
                                                        <p>Glissez et déposez une photo ici ou cliquez pour sélectionner une photo</p>
                                                        <input type="file" class="form-control" id="profile-photo" name="profile_photo" accept="image/*" onchange="displayPhoto(this)" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>CIN</label>
                                                    <input type="text" class="form-control" id="cin" name="cin" placeholder="CIN" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Matricule</label>
                                                    <input type="text" class="form-control" id="matricule" name="matricule" placeholder="Matricule" readonly />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nom</label>
                                                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Prénom</label>
                                                    <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Téléphone</label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Numéro de téléphone" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date de Naissance</label>
                                                    <input type="date" class="form-control" id="date_n" name="date_naissance" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Genre</label>
                                                    <select name="genre" class="form-select form-control-lg" id="genre">
                                                        <option value="M">Masculin</option>
                                                        <option value="F">Féminin</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                    <h5>Activités</h5>
                                    <section>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Type d'Activité</label>
                                                    <select name="activite_id" class="form-control">
                                                        <?php foreach ($activites as $activite) : ?>
                                                            <option value="<?= $activite['id'] ?>"><?= htmlspecialchars($activite['nom']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <div class="wizard-action">

                                        <div class="pull-right">
                                            <button type="submit" class="btn btn-secondary">Valider</button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom</th>

                                    <th>Matricule</th>
                                    <th>Email</th>
                                    <th>Activité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom</th>
                                    <th>Matricule</th>
                                    <th>Email</th>
                                    <th>Activité</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php foreach ($coaches as $coach) : ?>
                                    <tr>
                                        <td style="width:50px">
                                            <img src="../assets/img/capitalsoft/profils/<?php echo !empty($coach['photo']) ? htmlspecialchars($coach['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                                        </td>
                                        <td><?php echo htmlspecialchars($coach['user_nom']); ?> <?php echo htmlspecialchars($coach['user_prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['matricule']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['email']); ?></td>
                                        <td><?php echo htmlspecialchars($coach['activite_nom']); ?></td>
                                        <td>
                                            <!-- Actions links or buttons -->
                                            <!-- Example: View and Edit links -->
                                            <a href="consult.php?id_coach=<?php echo htmlspecialchars($coach['id']); ?>" class="btn btn-info btn-consult">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="modif.php?id_coach=<?php echo htmlspecialchars($coach['id']); ?>" class="btn btn-warning btn-modify">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-body">
                    <div class="card-head-row card-tools-still-right">
                        <div class="card-title">Coachs</div>
                    </div>
                    <div class="card-list py-4">
                        <?php if (count($coaches) > 0) : ?>
                            <?php foreach ($coaches as $coach) : ?>
                                <div class="item-list">
                                    <div class="avatar">
                                        <img src="../assets/img/capitalsoft/profils/<?php echo !empty($coach['photo']) ? htmlspecialchars($coach['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="avatar-img rounded-circle border border-white">
                                    </div>
                                    <div class="info-user ms-3">
                                        <div class="username"><?php echo htmlspecialchars($coach['user_nom']); ?> <?php echo htmlspecialchars($coach['user_prenom']); ?></div>
                                        <div class="status"><?php echo htmlspecialchars($coach['activite_nom']); ?></div>
                                    </div>
                                    <a class="btn btn-icon btn-link op-8 me-1" href="mailto:<?php echo htmlspecialchars($coach['email']); ?>">
                                        <i class="far fa-envelope"></i>
                                    </a>

                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="item-list">
                                <div class="info-user ms-3">
                                    <div class="username">Aucun coach trouvé.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>



    </div>
    <!-- Consult Modal -->
    <div class="modal fade" id="consultModal" tabindex="-1" role="dialog" aria-labelledby="consultModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultModalLabel">Consulter Utilisateur</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- User details will be populated here -->
                    <div id="consultDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modify Modal -->
    <div class="modal fade" id="modifyModal" tabindex="-1" role="dialog" aria-labelledby="modifyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modifyModalLabel">Modifier Utilisateur</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Modify form will be populated here -->
                    <form id="modifyForm">
                        <!-- Include all fields needed to modify user details -->
                        <!-- This form should be similar to your insertion form -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cinInput = document.getElementById('cin');
        const nomInput = document.getElementById('nom');
        const matriculeInput = document.getElementById('matricule');

        function generateMatricule() {
            const cin = cinInput.value.trim();
            const nom = nomInput.value.trim();
            if (cin && nom) {
                matriculeInput.value = cin + nom.charAt(0).toUpperCase();
            }
        }

        cinInput.addEventListener('input', generateMatricule);
        nomInput.addEventListener('input', generateMatricule);
    });
</script>
<?php
require "../inc/footer.php";
?>
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
// Ensure the coach ID is provided via GET parameter
if (!isset($_GET['id_coach'])) {
    header("Location: index.php"); // Redirect to coach list if ID is not provided
    exit();
}

$coach_id = $_GET['id_coach'];

// Retrieve coach details from database based on coach ID
$sql = "
SELECT 
    coaches.id,
    users.cin,
    users.matricule,
    users.nom AS user_nom,
    users.prenom AS user_prenom,
    users.email,
    users.phone,
    users.date_naissance,
    users.genre,
    users.photo,
    coaches.activite_id
FROM 
    coaches
JOIN 
    users ON coaches.user_id = users.id
WHERE 
    coaches.id = $coach_id
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $coach = $result->fetch_assoc();
} else {
    // Handle case where coach ID is not found
    header("Location: index.php"); // Redirect to coach list
    exit();
}

// Récupérer les activités
$activites_sql = "SELECT id, nom FROM activites";
$activites_result = $conn->query($activites_sql);
$activites = [];

if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}

$conn->close();
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Modifier Coach</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <h4 class="card-title">Modifier Coach</h4>
                </div>
                <div class="card-body">
                    <!-- Form for modifying coach -->
                    <form id="modify-coach-form" action="actionsCoaches/update_coach.php" method="post" enctype="multipart/form-data">
                        <!-- Hidden field to pass coach ID -->
                        <input type="hidden" name="coach_id" value="<?= $coach['id'] ?>">

                        <h5>Informations Personnelles</h5>
                        <section>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CIN</label>
                                        <input type="text" class="form-control" id="cin" name="cin" value="<?= htmlspecialchars($coach['cin']) ?>" placeholder="CIN" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Matricule</label>
                                        <input type="text" class="form-control" id="matricule" name="matricule" value="<?= htmlspecialchars($coach['matricule']) ?>" placeholder="Matricule" readonly />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($coach['user_nom']) ?>" placeholder="Nom" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($coach['user_prenom']) ?>" placeholder="Prénom" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($coach['email']) ?>" placeholder="Email" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($coach['phone']) ?>" placeholder="Numéro de téléphone" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date de Naissance</label>
                                        <input type="date" class="form-control" id="date_n" name="date_naissance" value="<?= htmlspecialchars($coach['date_naissance']) ?>" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Genre</label>
                                        <select name="genre" class="form-select form-control-lg" id="genre" required>
                                            <option value="M" <?= $coach['genre'] === 'M' ? 'selected' : '' ?>>Masculin</option>
                                            <option value="F" <?= $coach['genre'] === 'F' ? 'selected' : '' ?>>Féminin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <img id="preview" src="../assets/img/capitalsoft/profils/<?= !empty($coach['photo']) ? htmlspecialchars($coach['photo']) : 'admin.webp'; ?>" alt="Aperçu de la photo de profil" style="max-width: 90px; height: auto;" />
                                        <label>Photo de Profil</label>
                                        <div id="drop-area" class="drop-area">
                                            <p>Glissez et déposez une photo ici ou cliquez pour sélectionner une photo</p>
                                            <input type="file" class="form-control" id="profile-photo" name="profile_photo" accept="image/*" onchange="displayPhoto(this)" />
                                        </div>
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
                                        <select name="activite_id" class="form-control" required>
                                            <?php foreach ($activites as $activite) : ?>
                                                <option value="<?= $activite['id'] ?>" <?= $activite['id'] === $coach['activite_id'] ? 'selected' : '' ?>><?= htmlspecialchars($activite['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="wizard-action">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>
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

<?php require "../inc/footer.php"; ?>
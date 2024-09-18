<?php
require "../inc/app.php";

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password
$dbname = "privilage"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute queries for activities
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_stmt = $conn->prepare($activites_sql);
$activites_stmt->execute();
$activites_result = $activites_stmt->get_result();

// Prepare and execute queries for payment types
$type_paiements_sql = "SELECT id, type FROM type_paiements";
$type_paiements_stmt = $conn->prepare($type_paiements_sql);
$type_paiements_stmt->execute();
$type_paiements_result = $type_paiements_stmt->get_result();

$activites = [];
$type_paiements = [];

if ($activites_result->num_rows > 0) {
    while ($row = $activites_result->fetch_assoc()) {
        $activites[] = $row;
    }
}

if ($type_paiements_result->num_rows > 0) {
    while ($row = $type_paiements_result->fetch_assoc()) {
        $type_paiements[] = $row;
    }
}

// Retrieve user ID from URL and user details
if (isset($_GET['id_user'])) {
    $user_id = intval($_GET['id_user']);

    $sql = "
    SELECT 
        u.id,
        u.matricule,
        u.nom,
        u.prenom,
        u.email,
        u.phone,
        u.cin,
        u.photo,
        u.date_naissance,
        u.genre,
        a.type AS abonnement_type,
        GROUP_CONCAT(DISTINCT ua.activite_id ORDER BY ua.activite_id ASC SEPARATOR ',') AS activites_ids,
        GROUP_CONCAT(DISTINCT act.nom ORDER BY act.nom ASC SEPARATOR ', ') AS activites,
        COALESCE(a.date_fin, '') AS date_fin_abn,
        COALESCE(SUM(p.montant_paye), 0) AS montant_paye,
        tp.type AS type_paiement,
        COALESCE(SUM(p.reste), 0) AS reste
    FROM 
        users u
    JOIN 
        abonnements a ON u.id = a.user_id
    LEFT JOIN 
        user_activites ua ON a.id = ua.abonnement_id
    LEFT JOIN 
        activites act ON ua.activite_id = act.id
    LEFT JOIN 
        payments p ON a.id = p.abonnement_id
    LEFT JOIN 
        type_paiements tp ON p.type_paiement_id = tp.id
    WHERE 
        u.id = ?
    GROUP BY 
        u.id, a.type, a.date_fin, tp.type;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "Aucun utilisateur trouvé.";
        exit;
    }
} else {
    echo "Aucun utilisateur sélectionné.";
    exit;
}

$conn->close();
?>
<style>
    .page-inner {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #977438;
    color: white;
    padding: 10px 15px;
    border-bottom: 1px solid #e0e0e0;
    border-radius: 8px 8px 0 0;
}

.card-title {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.card-body {
    padding: 15px;
    background-color: white;
    border-radius: 0 0 8px 8px;
}

.card h5 {
    margin-top: 20px;
    font-size: 16px;
    color: #977438;
    font-weight: bold;
    border-bottom: 1px solid #977438;
    padding-bottom: 5px;
}

.section {
    margin-bottom: 20px;
}

.section legend {
    font-size: 18px;
    font-weight: bold;
    color: #977438;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
    color: #495057;
}

.form-group span {
    display: block;
    font-size: 14px;
    color: #6c757d;
}

.selectgroup-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.selectgroup-item {
    margin-bottom: 10px;
}

.selectgroup-button {
    background-color: #e0e0e0;
    border: 1px solid #977438;
    color: #977438;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 14px;
    cursor: default;
    display: inline-block;
}

.selectgroup-button:hover {
    background-color: #977438;
    color: white;
}

img {
    border-radius: 8px;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .row {
        flex-direction: column;
    }

    .form-group {
        width: 100%;
    }
}

</style>
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Consultation Adhérent</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Consultation Adhérent</h4>
                    </div>
                </div>
                <div class="card-body">
                    <h5>Information Personnel</h5>
                    <section>
                        <legend></legend>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?php if (!empty($user['photo'])) : ?>
                                        <img src="../assets/img/capitalsoft/profils/<?= htmlspecialchars($user['photo']) ?>" alt="Photo de Profil" style="width: 150px; height: 150px; object-fit: cover;" />
                                    <?php else : ?>
                                        <span>Aucune photo de profil disponible</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>CIN:</label>
                                    <span><?= htmlspecialchars($user['cin']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Matricule:</label>
                                    <span><?= htmlspecialchars($user['matricule']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom:</label>
                                    <span><?= htmlspecialchars($user['nom']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prénom:</label>
                                    <span><?= htmlspecialchars($user['prenom']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email:</label>
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Téléphone:</label>
                                    <span><?= htmlspecialchars($user['phone']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de naissance:</label>
                                    <span><?= htmlspecialchars($user['date_naissance']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Genre:</label>
                                    <span><?= $user['genre'] === 'M' ? 'Mâle' : 'Femelle' ?></span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <h5>Abonnement</h5>
                    <section>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type d'abonnement:</label>
                                    <span><?= htmlspecialchars($user['abonnement_type']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="Type_dactivite">Type d’activité:</label>
                                <div class="selectgroup selectgroup-pills">
                                    <?php
                                    $user_activities = explode(',', $user['activites_ids']);
                                    foreach ($activites as $activite) :
                                        if (in_array($activite['id'], $user_activities)) :
                                    ?>
                                            <label class="selectgroup-item">
                                                <span class="selectgroup-button"><?= htmlspecialchars($activite['nom']) ?></span>
                                            </label>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de fin d’abonnement:</label>
                                    <span><?= htmlspecialchars($user['date_fin_abn']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Montant Payé:</label>
                                    <span><?= htmlspecialchars($user['montant_paye']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de Paiement:</label>
                                    <span><?= htmlspecialchars($user['type_paiement']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reste:</label>
                                    <span><?= htmlspecialchars($user['reste']) ?></span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require "../inc/footer.php";
?>

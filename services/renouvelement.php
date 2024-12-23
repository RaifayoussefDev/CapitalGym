<?php
ob_start();
require "../inc/app.php";
require "../inc/conn_db.php";
$profil = $_SESSION['profil'];
$_SESSION['current_page'] = 'adherent';
$_SESSION['user_insert'] = 0;
$id_user = $_GET['id_user'];
$package_sql = "SELECT * FROM `packages` where package_type_id not like 9 ORDER BY packages.annual_price DESC";
$package_result = $conn->query($package_sql);


// if($profil != 1){
//     header('location:../Adherents/');
// }

// Récupérer les types de paiement
$type_paiements_sql = "SELECT id, type FROM type_paiements";
$type_paiements_result = $conn->query($type_paiements_sql);

$activites = [];
$packages = [];
$type_paiements = [];


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

// Récupérer les utilisateurs avec les détails de l'abonnement et les activités
$sql = "
SELECT 
    u.id, 
    u.matricule, 
    u.id_card, 
    u.nom, 
    u.prenom, 
    u.Note, 
    u.email, 
    u.phone, 
    u.adresse, 
    u.num_urgence, 
    u.photo, 
    u.fonction, 
    u.employeur, 
    a.id AS id_abonnement, 
    u.date_naissance, 
    a.type_abonnement, 
    u.cin, 
    u.genre, 
    p.package_type_id, 
    a.offres_promotionnelles, 
    a.description, 
    p.id AS id_pack, 
    SUM(py.montant_paye) AS montant_paye_total, -- Total montant_paye for the user
    py.id AS payement_id, 
    py.total AS total, 
    p.pack_name AS pack_name, 
    py.reste AS reste, 
    a.date_debut AS date_debut, 
    a.date_fin AS date_fin
FROM 
    users u 
JOIN 
    abonnements a ON u.id = a.user_id 
JOIN 
    packages p ON p.id = a.type_abonnement 
JOIN 
    payments py ON py.abonnement_id = a.id 
WHERE 
    u.role_id = 3 
    AND u.id = '$id_user'
GROUP BY 
    u.id, a.id, p.id;
";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $users = [];
    echo $sql;
}



$sqlc = "select * from users where role_id = 4";
$resultc = $conn->query($sqlc);
if ($resultc->num_rows > 0) {
    while ($rowc = $resultc->fetch_assoc()) {
        $commercials[] = $rowc;
    }
} else {
    $commercials = [];
}


$sqla = "SELECT * FROM `activites` where type not like ''";
$resulta = $conn->query($sqla);
if ($resulta->num_rows > 0) {
    while ($rowa = $resulta->fetch_assoc()) {
        $activites[] = $rowa;
    }
}


$sql_user_activities = "SELECT * FROM `user_activites` WHERE user_id = $id_user";
$result_user_activities = $conn->query($sql_user_activities);

$user_activities = [];
if ($result_user_activities->num_rows > 0) {
    while ($row_user_activity = $result_user_activities->fetch_assoc()) {
        $user_activities[$row_user_activity['activite_id']] = $row_user_activity;
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

    .custom-modal .modal-dialog {
        max-width: 90%;
        /* Ajustez la largeur selon vos besoins */
    }

    #camera {
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }

    #canvas {
        border: 1px solid #ccc;
    }

    #preview {
        display: block;
        margin-top: 10px;
    }

    .badge {
        background-color: #fff;
        border: 2px solid #007bff;
        border-radius: 8px;
        width: 8.6cm;
        height: 5.4cm;
        display: flex;
        justify-content: space-between;
        padding: 16px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .badge .badge-content {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
    }

    .badge .info {
        flex: 1;
    }

    .badge .photo-container {
        text-align: right;
    }

    .badge .photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
    }

    .badge .name {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }

    .badge .matricule {
        margin: 0;
        color: #666;
        font-size: 14px;
        margin-top: 4px;
    }

    .credit-card {
        border-radius: 15px;
        padding: 20px;
        max-width: 350px;
        height: 200px;
        position: relative;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    }

    .credit-card .logo {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 50px;
    }

    .credit-card .card-details {
        margin-top: 50px;
    }

    .credit-card .card-details .name {
        font-size: 18px;
        font-weight: bold;
    }

    .credit-card .card-details .info {
        font-size: 14px;
    }
</style>
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function displayPhoto(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        var dropArea = document.getElementById('drop-area');
        var profilePhotoInput = document.getElementById('profile-photo');

        if (dropArea && profilePhotoInput) {
            dropArea.addEventListener('click', function() {
                profilePhotoInput.click();
            });

            dropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            dropArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
                var files = e.dataTransfer.files;
                profilePhotoInput.files = files;
                displayPhoto(profilePhotoInput);
            });
        } else {
            console.error("Drop area or profile photo input not found.");
        }
    });
</script>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Listes des Adhérents</h3>
        </div>
    </div>
    <div class="row">
        <!-- Your existing cards here -->
        <div class="col-sm-12 col-md-12">
            <div class="card card-stats card-round">
                <div class="wizard-content" id="tab-wizard">
                    <?php foreach ($users as $user) {; ?>
                        <form id="example-form" action="edit_adherent.php?id_user=<?php echo $user['id']; ?>" method="post" class="tab-wizardProce wizard-circle wizard" enctype="multipart/form-data">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <H1 class="text-secondary d-none" id="matricule" name="matricule"></H1>
                                    <input id="matricule_input" name="matricule_input" class="d-none" value="" />
                                </div>
                            </div>
                            <input type="text" name="id_abonnement" style="display: none;" value="<?php echo $user['id_abonnement']; ?>">
                            <h5>Information Personnel</h5>
                            <section>
                                <legend></legend>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <img
                                                    id="preview"
                                                    src="<?php echo isset($user['photo']) && !empty($user['photo']) ? '../assets/img/capitalsoft/profils/' . htmlspecialchars($user['photo']) : '#'; ?>"
                                                    alt="<?php echo empty($user['photo']) ? 'Aucune photo disponible' : 'Aperçu de la photo'; ?>"
                                                    style="max-width: 200px; height: auto; <?php echo empty($user['photo']) ? 'display:none;' : ''; ?>" />
                                                <video id="camera" width="320" height="240" autoplay style="display:none;"></video>
                                                <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                            </div>
                                            <div class="col-md-8">
                                                <label>Photo de profil</label>
                                                <div id="drop-area" class="drop-area">
                                                    <p>Glissez et déposez une photo ici ou cliquez pour sélectionner une photo</p>
                                                    <input type="file" class="form-control" id="profile-photo" name="profile_photo" accept="image/*" onchange="displayPhoto(this)" />
                                                    <button type="button" class="btn btn-dark mt-2" id="capture-button">Prendre une photo</button>
                                                    <button type="button" id="save-photo" class="btn btn-dark mt-2" style="display:none;">Sauvegarder la photo</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Champs remplis automatiquement -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>CIN <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cin" name="cin" value="<?php echo $user['cin']; ?>" required />
                                            <small class="text-danger" id="cin-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $user['nom']; ?>" required />
                                            <small class="text-danger" id="nom-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $user['prenom']; ?>" required />
                                            <small class="text-danger" id="prenom-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required />
                                            <small class="text-danger" id="email-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Téléphone <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required />
                                            <small class="text-danger" id="phone-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date de naissance</label>
                                            <input type="date" class="form-control" id="date_n" name="date_naissance" value="<?php echo $user['date_naissance']; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Genre</label>
                                            <select name="genre" class="form-select form-control-lg" id="genre">
                                                <option value="M" <?php echo ($user['genre'] == 'M') ? 'selected' : ''; ?>>Homme</option>
                                                <option value="F" <?php echo ($user['genre'] == 'F') ? 'selected' : ''; ?>>Femme</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo $user['adresse']; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fonction</label>
                                            <input type="text" class="form-control" id="fonction" name="fonction" value="<?php echo $user['fonction']; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Numéro de GSM en cas d’urgence</label>
                                            <input type="tel" class="form-control" id="num_urgence" name="num_urgence" value="<?php echo $user['num_urgence']; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Employeur</label>
                                            <input type="text" class="form-control" id="employeur" name="employeur" value="<?php echo $user['employeur']; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Note</label>
                                            <textarea class="form-control" id="note" name="note" placeholder="Note"><?php echo htmlspecialchars($user['Note']); ?></textarea>
                                        </div>
                                    </div>
                                    <!-- Ajoutez ici des informations supplémentaires comme les abonnements, packs, paiements etc. -->

                                </div>
                            </section>

                            <h5>Abonnement</h5>
                            <section>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Conventions d'adhésion</label>
                                            <select name="convention" id="convention" class="form-select form-control-lg" onchange="filterCategories()">
                                                <option value="YES" <?= $user['package_type_id'] == 8 ? 'selected' : '' ?>>Oui</option>
                                                <option value="NO" <?= $user['package_type_id'] != 8 ? 'selected' : '' ?>>Non</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Catégorie d'adhésion</label>
                                            <select name="categorie_adherence" id="categorie_adherence" class="form-select form-control-lg" onchange="updateAbonnementOptions()">
                                                <?php
                                                // Séparer l'option sélectionnée des autres
                                                $selectedOption = null;
                                                foreach ($packages as $package) {
                                                    if ($package['id'] == $user['type_abonnement']) {
                                                        $selectedOption = $package;
                                                        break;
                                                    }
                                                }

                                                // Afficher l'option sélectionnée en premier
                                                if ($selectedOption): ?>
                                                    <option value="<?= $selectedOption['id']; ?>"
                                                        data-daily="<?= $selectedOption['Daily_price']; ?>"
                                                        data-annual="<?= $selectedOption['annual_price']; ?>"
                                                        data-semestrial="<?= $selectedOption['semestrial_price']; ?>"
                                                        data-trimestrial="<?= $selectedOption['trimestrial_price']; ?>"
                                                        data-monthly="<?= $selectedOption['monthly_price']; ?>"
                                                        data-type="<?= $selectedOption['package_type_id']; ?>"
                                                        selected>
                                                        <?= $selectedOption['pack_name']; ?>
                                                    </option>
                                                <?php endif; ?>

                                                <?php
                                                // Afficher les autres options
                                                foreach ($packages as $package):
                                                    if ($package['id'] != $user['type_abonnement']): ?>
                                                        <option value="<?= $package['id']; ?>"
                                                            data-daily="<?= $package['Daily_price']; ?>"
                                                            data-annual="<?= $package['annual_price']; ?>"
                                                            data-semestrial="<?= $package['semestrial_price']; ?>"
                                                            data-trimestrial="<?= $package['trimestrial_price']; ?>"
                                                            data-monthly="<?= $package['monthly_price']; ?>"
                                                            data-type="<?= $package['package_type_id']; ?>">
                                                            <?= $package['pack_name']; ?>
                                                        </option>
                                                <?php endif;
                                                endforeach; ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Type d'abonnement</label>
                                            <select name="type_abonnement" id="type_abonnement" class="form-select form-control-lg">
                                                <!-- Options dynamically added by JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <input type="date" id="date_debut" style="display: none;" value="<?php echo $user['date_debut']; ?>">
                                    <input type="date" id="date_fin" style="display: none;" value="<?php echo $user['date_fin']; ?>">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Offres promotionnelles</label>
                                            <input type="text" class="form-control" id="offre_promo" name="offre_promo" placeholder="Offre promotionnelle" value="<?php echo $user['offres_promotionnelles']; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" id="description" class="form-control" placeholder="Ajouter une description"><?php echo $user['description']; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Activités</label>
                                            <?php foreach ($activites as $activite) {
                                                $isChecked = isset($user_activities[$activite['id']]);
                                                $selectedPeriod = $isChecked ? $user_activities[$activite['id']]['periode_activites'] : null;
                                            ?>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input
                                                                class="form-check-input activite-checkbox"
                                                                type="checkbox"
                                                                name="value[]"
                                                                value="<?php echo $activite['id']; ?>"
                                                                data-price="<?php echo $activite['prix']; ?>"
                                                                id="<?php echo $activite['nom']; ?>"
                                                                <?php echo $isChecked ? 'checked' : ''; ?> />
                                                            <label class="form-check-label" for="<?php echo $activite['nom']; ?>">
                                                                <?php echo $activite['nom']; ?>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="type_activite[]" value="<?php echo $activite['type']; ?>">

                                                    <div class="col-md-6 <?php echo $isChecked ? '' : ''; ?>">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <?php echo ($activite['type'] == 'par mois') ? 'Période d\'activité' : 'Nombre des séances'; ?>
                                                            </label>
                                                            <select name="periode_activite[<?php echo $activite['id']; ?>]" class="form-select form-control-lg period-select" <?php echo $isChecked ? '' : 'disabled'; ?>>
                                                                <?php if ($activite['type'] == 'par mois') { ?>
                                                                    <option value="1" <?php echo ($selectedPeriod == 1) ? 'selected' : ''; ?>>Mensuel</option>
                                                                    <option value="3" <?php echo ($selectedPeriod == 3) ? 'selected' : ''; ?>>Trimestriel</option>
                                                                    <option value="6" <?php echo ($selectedPeriod == 6) ? 'selected' : ''; ?>>Semestriel</option>
                                                                    <option value="12" <?php echo ($selectedPeriod == 12) ? 'selected' : ''; ?>>Annuel</option>
                                                                <?php } else { ?>
                                                                    <?php for ($i = 1; $i <= 10; $i++) { ?>
                                                                        <option value="<?php echo $i; ?>" <?php echo ($selectedPeriod == $i) ? 'selected' : ''; ?>>
                                                                            <?php echo $i . ' séance' . ($i > 1 ? 's' : ''); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date Début d'abonnement</label>
                                            <input type="date" name="date_debut_paiement" id="date_debut_paiement" class="form-control" value="<?php echo $user['date_debut']; ?>" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date de fin d’abonnement</label>
                                            <div class="input-group">
                                                <input type="date" id="date_fin_abn" name="date_fin_abn" class="form-control" value="<?php echo $user['date_fin']; ?>" readonly />
                                                <div id="adjustButtons">
                                                    <button type="button" class="btn btn-dark" onclick="adjustEndDate(1)">+</button>
                                                    <button type="button" class="btn btn-dark" onclick="adjustEndDate(-1)">-</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </section>
                            <h5>Paiement</h5>
                            <section>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Total :</label>
                                            <input type="text" name="total99" id="total"
                                                value="<?php echo htmlspecialchars($user['total']); ?>"
                                                class="form-control"
                                                <?php if ($profil != 1) echo 'readonly'; ?> />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Reste :</label>
                                            <input
                                                type="text"
                                                name="reste"
                                                id="reste"
                                                value="<?php echo isset($user['total'], $user['montant_paye_total']) ? $user['total'] - $user['montant_paye_total'] : 0; ?>"
                                                class="form-control"
                                                readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Montant Payé :</label>
                                            <input
                                                type="text"
                                                name="montant_paye_input"
                                                id="montant_paye_input"
                                                value="<?php echo $user['montant_paye_total']; ?>"
                                                class="form-control"
                                                <?php echo ($profil != 1) ? 'readonly' : ''; ?> />
                                        </div>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-secondary" id="add_mode_payement">Ajouter un mode de paiement</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="payment_modes_container"></div>
                                <div class="row d-none">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Total des activites :</label>
                                            <input type="text" name="total_activites" id="total_activites" class="form-control" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Reste des activites</label>
                                            <input type="text" name="reste_activites" id="reste_activites" class="form-control" readonly />
                                        </div>
                                    </div>
                                </div>
                            </section>


                            <h5>Badge</h5>
                            <section>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="container">
                                            <?php
                                            // Determine the background color and text color based on the user's pack
                                            $backgroundColor = 'gray'; // Default for 'Platinum'
                                            $textColor = 'white';      // Default for 'Platinum'

                                            if ($user['pack_name'] == 'platinium') {
                                                $backgroundColor = 'black';
                                                $textColor = 'white';
                                            } elseif ($user['pack_name'] == 'gold') {
                                                $backgroundColor = 'gold';
                                                $textColor = 'black';
                                            }
                                            ?>

                                            <div class="credit-card" style="background-color: <?php echo $backgroundColor; ?>; color: <?php echo $textColor; ?>;">
                                                <!-- Logo -->
                                                <img src="../assets/img/capitalsoft/logo_light.png" alt="Logo" class="logo" style="width: 80px; position: absolute; top: 20px; right: 20px;">

                                                <!-- Card details -->
                                                <div class="card-details" style="margin-top: 50px;">
                                                    <div class="row">
                                                        <div class="col-md-12 name" style="text-transform: capitalize;">
                                                            <?php echo $user['nom']; ?> <?php echo $user['prenom']; ?>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 info">
                                                            <?php echo $user['matricule']; ?>
                                                        </div>
                                                    </div>
                                                    <!-- <?php
                                                            // if ($user['id_card'] != null or $user['id_card'] != '') { 
                                                            ?>
                                                        <div class="row">
                                                            <div class="col-md-12 info bg-white" >
                                                                Badge: <?php //echo $user['id_card']; 
                                                                        ?>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    //}
                                                    ?> -->
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input field for the badge number and change button -->
                                    <div class="col-md-6">
                                        <div class="container">
                                            <!-- Input field for the badge number -->
                                            <div class="row d-none">
                                                <div class="col-md-12">
                                                    <label for="badge-number">Badge Number:</label>
                                                    <input type="text" id="badge-number" name="badge_number" class="form-control" placeholder="Enter badge number" value="<?php echo $user['id_card']; ?>" readonly>
                                                </div>
                                            </div>

                                            <!-- Button to change the badge number -->
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <button type="button" id="change-badge-btn" onclick="get_newbadge()" class="btn btn-primary">Change Badge</button>
                                                </div>
                                            </div>

                                            <!-- Area for displaying timer and messages -->
                                            <div id="message" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <template id="payment_mode_template">
                                <div class="payment-mode">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Type de payement</label>
                                                <select name="type_paiement[]" class="form-select form-control-lg type_paiement">
                                                    <!-- Populate this with PHP -->
                                                    <?php foreach ($type_paiements as $type_paiement) : ?>
                                                        <option value="<?= $type_paiement['id'] ?>"><?= htmlspecialchars($type_paiement['type']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Montant Payé</label>
                                                <input type="text" name="montant_paye[]" class="form-control montant_paye" onchange="calculateReste()" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="section_cheque d-none">
                                        <h2 class="label_cheque d-none">Chèque</h2>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nomTitulaire">Nom du titulaire :</label>
                                                    <input type="text" name="nomTitulaire[]" class="form-control" placeholder="Entrez le nom du titulaire du chèque" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="numeroCheque">Numéro du chèque :</label>
                                                    <input type="text" name="numeroCheque[]" class="form-control" placeholder="Entrez le numéro du chèque" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="dateEmission">Date d'encaissement :</label>
                                                    <input type="date" name="dateEmission[]" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-none">
                                                <div class="form-group">
                                                    <label for="numeroCompte">Numéro de compte :</label>
                                                    <input type="text" name="numeroCompte[]" class="form-control" value="19 29 989898989898988998989898" placeholder="Entrez le numéro de compte" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="banqueEmettrice">Banque émettrice :</label>
                                                    <select name="banqueEmettrice[]" class="form-control">
                                                        <option value="" disabled selected>Choisissez une banque</option>
                                                        <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                                                        <option value="Banque Populaire">Banque Populaire</option>
                                                        <option value="BMCE Bank">BMCE Bank</option>
                                                        <option value="Banque Centrale Populaire">Banque Centrale Populaire</option>
                                                        <option value="Crédit Agricole du Maroc">Crédit Agricole du Maroc</option>
                                                        <option value="Crédit du Maroc">Crédit du Maroc</option>
                                                        <option value="CIH Bank">CIH Bank</option>
                                                        <option value="Société Générale">Société Générale</option>
                                                        <option value="Bank of Africa">Bank of Africa</option>
                                                        <option value="BMCI">BMCI</option>
                                                        <option value="Al Barid Bank">Al Barid Bank</option>
                                                        <option value="CDG Capital">CDG Capital</option>
                                                        <option value="Dar Assafaa">Dar Assafaa</option>
                                                        <option value="Umnia Bank">Umnia Bank</option>
                                                        <option value="Banque Atlantique">Banque Atlantique</option>
                                                        <option value="Wafa Assurance">Wafa Assurance</option>
                                                        <option value="Société Marocaine de Crédit Bailleurs">Société Marocaine de Crédit Bailleurs</option>
                                                        <option value="Société Générale Maroc">Société Générale Maroc</option>
                                                        <option value="RMA Watanya">RMA Watanya</option>
                                                        <option value="Tanger Med Bank">Tanger Med Bank</option>
                                                        <option value="Banque de l'Habitat">Banque de l'Habitat</option>
                                                        <option value="Maghreb Arab Bank">Maghreb Arab Bank</option>
                                                        <option value="Banque Marocaine pour le Commerce et l'Industrie">Banque Marocaine pour le Commerce et l'Industrie</option>
                                                        <option value="Crédit du Maroc">Crédit du Maroc</option>
                                                        <option value="Banque de Développement du Maroc">Banque de Développement du Maroc</option>
                                                        <option value="MAMDA - MCMA">MAMDA - MCMA</option>
                                                        <option value="Société de Crédit à la Consommation">Société de Crédit à la Consommation</option>
                                                        <option value="Wafa Bank">Wafa Bank</option>
                                                        <option value="CFG Bank">CFG Bank</option>
                                                        <option value="BANK ASSAFA">BANK ASSAFA</option>
                                                                    <option value="Banque UMB">Banque UMB</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="section_virement d-none">
                                        <h2 class="label_virement">Virement</h2>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nomEmetteur">Nom de l'émetteur :</label>
                                                    <input type="text" name="nomEmetteur[]" class="form-control" placeholder="Entrez le nom de l'émetteur du virement" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="dateImitation">Date d'imitation :</label>
                                                    <input type="date" name="dateImitation[]" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="reference">Référence :</label>
                                                    <input type="text" name="reference[]" class="form-control" placeholder="Entrez la référence du virement" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="banqueEmettrice">Banque émettrice :</label>
                                                    <select name="banqueEmettrice[]" class="form-control">
                                                        <option value="" disabled selected>Choisissez une banque</option>
                                                        <option value="Attijariwafa Bank">Attijariwafa Bank</option>
                                                        <option value="Banque Populaire">Banque Populaire</option>
                                                        <option value="BMCE Bank">BMCE Bank</option>
                                                        <option value="Banque Centrale Populaire">Banque Centrale Populaire</option>
                                                        <option value="Crédit Agricole du Maroc">Crédit Agricole du Maroc</option>
                                                        <option value="Crédit du Maroc">Crédit du Maroc</option>
                                                        <option value="CIH Bank">CIH Bank</option>
                                                        <option value="Société Générale">Société Générale</option>
                                                        <option value="Bank of Africa">Bank of Africa</option>
                                                        <option value="BMCI">BMCI</option>
                                                        <option value="Al Barid Bank">Al Barid Bank</option>
                                                        <option value="CDG Capital">CDG Capital</option>
                                                        <option value="Dar Assafaa">Dar Assafaa</option>
                                                        <option value="Umnia Bank">Umnia Bank</option>
                                                        <option value="Banque Atlantique">Banque Atlantique</option>
                                                        <option value="Wafa Assurance">Wafa Assurance</option>
                                                        <option value="Société Marocaine de Crédit Bailleurs">Société Marocaine de Crédit Bailleurs</option>
                                                        <option value="Société Générale Maroc">Société Générale Maroc</option>
                                                        <option value="RMA Watanya">RMA Watanya</option>
                                                        <option value="Tanger Med Bank">Tanger Med Bank</option>
                                                        <option value="Banque de l'Habitat">Banque de l'Habitat</option>
                                                        <option value="Maghreb Arab Bank">Maghreb Arab Bank</option>
                                                        <option value="Banque Marocaine pour le Commerce et l'Industrie">Banque Marocaine pour le Commerce et l'Industrie</option>
                                                        <option value="Crédit du Maroc">Crédit du Maroc</option>
                                                        <option value="Banque de Développement du Maroc">Banque de Développement du Maroc</option>
                                                        <option value="MAMDA - MCMA">MAMDA - MCMA</option>
                                                        <option value="Société de Crédit à la Consommation">Société de Crédit à la Consommation</option>
                                                        <option value="Wafa Bank">Wafa Bank</option>
                                                        <option value="CFG Bank">CFG Bank</option>
                                                        <option value="BANK ASSAFA">BANK ASSAFA</option>
                                                                    <option value="Banque UMB">Banque UMB</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </form>
                    <?php }; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function get_newbadge() {
        const badgeInput = document.getElementById('badge-number');

        // Initial notification with countdown message
        let secondsRemaining = 5;

        // Create the notification and store the reference
        let notifyElement = $.notify({
            title: "<h3>Attention</h3>",
            message: `Merci d'ajouter le nouveau badge au lecteur. Temps restant : ${secondsRemaining} secondes...`
        }, {
            type: 'info',
            placement: {
                from: "top",
                align: "right"
            },
            delay: 6000, // Keep the notification visible for 6 seconds
            z_index: 1051
        });

        // Set a timer to update the countdown every second
        const countdownInterval = setInterval(() => {
            secondsRemaining--;

            // Update the existing notification's message
            notifyElement.update('message', `Temps restant : ${secondsRemaining} secondes...`);

            if (secondsRemaining === 0) {
                clearInterval(countdownInterval); // Stop the countdown when it reaches 0
            }
        }, 1000); // Update every second

        // Set a timeout for 5 seconds to check the badge value
        setTimeout(function() {
            // Make an AJAX request to check if there's a value for the badge in the database
            fetch('read_card_app.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.valeur) {
                        // If a badge value is found, display it in the input field
                        badgeInput.value = data.data.valeur;

                        // Update the existing notification with a success message
                        notifyElement.update({
                            'type': 'success',
                            'title': "<h3>Badge trouvé</h3>",
                            'message': `Le badge a été correctement détecté.`
                        });

                        // Call the clear_envoi_app.php to clear the table
                        fetch('clear_envoi_app.php')
                            .then(clearResponse => clearResponse.json())
                            .then(clearData => {
                                if (clearData.success) {
                                    console.log("Table vidée avec succès.");
                                } else {
                                    console.error("Échec du vidage de la table.");
                                }
                            })
                            .catch(error => {
                                console.error("Erreur lors du vidage de la table :", error);
                            });

                    } else {
                        // Handle if the badge is already assigned or not detected
                        if (data.message === "Le badge est déjà affecté à un utilisateur.") {
                            // Badge is already assigned to a user
                            notifyElement.update({
                                'type': 'danger',
                                'title': "<h3>Erreur</h3>",
                                'message': "Le badge est déjà affecté à un utilisateur."
                            });
                        } else {
                            // If no badge value is found, update the notification to prompt the user
                            notifyElement.update({
                                'type': 'danger',
                                'title': "<h3>Aucun badge détecté</h3>",
                                'message': "Veuillez réessayer."
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur :', error);
                    // Update the existing notification with an error message
                    notifyElement.update({
                        'type': 'danger',
                        'title': "<h3>Erreur</h3>",
                        'message': "Une erreur est survenue. Veuillez réessayer."
                    });
                });
        }, 5000); // 5 seconds timer
    }
</script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize the event listeners for checkboxes and dropdowns
        const activityCheckboxes = document.querySelectorAll('.activite-checkbox');
        const periodSelects = document.querySelectorAll('.period-select');

        // Attach the 'change' event listener for each activity checkbox
        activityCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                togglePeriodSelect(checkbox);
                calculateTotalPrice(); // Recalculate the total price whenever checkbox state changes
            });
        });

        // Attach the 'change' event listener for each period dropdown
        periodSelects.forEach(function(select) {
            select.addEventListener('change', calculateTotalPrice);
        });
    });

    function togglePeriodSelect(checkbox) {
        // Find the corresponding period select element
        const periodSelect = checkbox.closest('.row').querySelector('.period-select');

        // Disable or enable the select based on the checkbox state
        if (checkbox.checked) {
            periodSelect.disabled = false; // Enable the select if the checkbox is checked
        } else {
            periodSelect.disabled = true; // Disable the select if the checkbox is unchecked
            periodSelect.selectedIndex = 0; // Optionally reset the select to the first option
        }
    }

    function calculateTotalPrice() {
        // Get the inputs for displaying the total price
        const totalActivitesInput = document.getElementById('total_activites');
        const resteActivitesInput = document.getElementById('reste_activites');

        // Get all the activity checkboxes
        const selectedActivites = document.querySelectorAll('.activite-checkbox:checked');

        // Initialize totalPrice to 0
        let totalPrice = 0;

        // Loop through each selected activity
        selectedActivites.forEach(function(activite) {
            const activitePrice = parseFloat(activite.getAttribute('data-price')) || 0;

            // Get the period select for the current activity
            const periodSelect = activite.closest('.row').querySelector('.period-select');
            const periode = parseFloat(periodSelect.value) || 0;

            // Calculate the total price for this activity
            if (!isNaN(activitePrice)) {
                totalPrice += activitePrice * periode; // Multiply the activity price by the selected period
            }
        });

        // Update the inputs with the new total
        totalActivitesInput.value = totalPrice.toFixed(2);
        resteActivitesInput.value = totalPrice.toFixed(2);

    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsPerPage = 6;
        let currentPage = 1;
        let usersData = [];
        let filteredUsers = [];

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filteredUsers = usersData.filter(user =>
                `${user.nom} ${user.prenom}`.toLowerCase().includes(searchTerm)
            );
            currentPage = 1; // Reset to first page after search
            displayUsers();
            setupPagination();
        });

        // Display users on the current page
        function displayUsers() {
            const userContainer = document.getElementById('user-list');
            userContainer.innerHTML = '';

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const usersToShow = filteredUsers.slice(start, end);

            if (usersToShow.length === 0) {
                userContainer.innerHTML = '<div class="item-list"><div class="info-user ms-3"><div class="username">Aucun utilisateur trouvé.</div></div></div>';
                return;
            }

            usersToShow.forEach(user => {
                const userItem = `
                    <div class="item-list d-flex align-items-center mb-2">
                        <div class="avatar">
                            <img src="../assets/img/capitalsoft/profils/${user.photo || 'admin.webp'}" alt="Profile Picture" class="avatar-img rounded-circle border border-white" width="50" height="50">
                        </div>
                        <div class="info-user ms-3">
                            <div class="username">${user.nom} ${user.prenom}</div>
                            <div class="status">${user.pack_name}</div>
                        </div>
                        <a class="btn btn-icon btn-link op-8 me-1" href="mailto:${user.email}">
                            <i class="far fa-envelope"></i>
                        </a>
                        ${user.etat === 'actif' ? `
                            <a class="btn btn-icon btn-link btn-danger op-8" href="block.php?id_user=${user.id}">
                                <i class="fas fa-ban"></i>
                            </a>` : `
                            <a class="btn btn-icon btn-link btn-success op-8" href="deblock.php?id_user=${user.id}">
                                <i class="fas fa-check"></i>
                            </a>`}
                    </div>
                `;
                userContainer.insertAdjacentHTML('beforeend', userItem);
            });
        }

        // Set up pagination
        function setupPagination() {
            const paginationContainer = document.getElementById('pagination');
            paginationContainer.innerHTML = '';

            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);

            if (totalPages <= 1) return; // No need for pagination if only 1 page

            // Create Previous button
            const prevClass = currentPage === 1 ? 'disabled' : '';
            paginationContainer.insertAdjacentHTML('beforeend', `
                <li class="page-item ${prevClass}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                </li>
            `);

            // Create page numbers
            for (let i = 1; i <= totalPages; i++) {
                const activeClass = currentPage === i ? 'active' : '';
                paginationContainer.insertAdjacentHTML('beforeend', `
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            // Create Next button
            const nextClass = currentPage === totalPages ? 'disabled' : '';
            paginationContainer.insertAdjacentHTML('beforeend', `
                <li class="page-item ${nextClass}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>
            `);

            // Add event listeners to pagination links
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const newPage = parseInt(this.getAttribute('data-page'));
                    if (newPage > 0 && newPage <= totalPages) {
                        currentPage = newPage;
                        displayUsers();
                        setupPagination();
                    }
                });
            });
        }
    });
</script>
<script>
    // Function to update the "type_abonnement" options based on selected package
    function updateAbonnementOptions() {
        var selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0];
        var typeAbonnementSelect = document.getElementById('type_abonnement');
        var adjustButtons = document.getElementById('adjustButtons');

        typeAbonnementSelect.innerHTML = ''; // Clear previous options

        // Get package price attributes
        var daily = selectedPackage.getAttribute('data-daily');
        var monthly = selectedPackage.getAttribute('data-monthly');
        var trimestrial = selectedPackage.getAttribute('data-trimestrial');
        var semestrial = selectedPackage.getAttribute('data-semestrial');
        var annual = selectedPackage.getAttribute('data-annual');

        // Add options based on available prices
        if (daily) {
            var option = new Option('Journée', '0.03');
            typeAbonnementSelect.add(option);
        }
        if (monthly) {
            var option = new Option('Mensuel', '1');
            typeAbonnementSelect.add(option);
        }
        if (trimestrial) {
            var option = new Option('Trimestriel', '3');
            typeAbonnementSelect.add(option);
        }
        if (semestrial) {
            var option = new Option('Semestriel', '6');
            typeAbonnementSelect.add(option);
        }
        if (annual) {
            var option = new Option('Annuel', '12');
            typeAbonnementSelect.add(option);
        }

        // Automatically select "Annuel" if the duration is greater than or equal to one year
        calculateDurationAndSelectAbonnement();

        calculateTotal();
        generateMatricule();
        calculateDateFin();
    }

    function calculateDurationAndSelectAbonnement() {
        var dateDebut = document.getElementById('date_debut').value;
        var dateFin = document.getElementById('date_fin').value;
        var typeAbonnementSelect = document.getElementById('type_abonnement');
        var adjustButtons = document.getElementById('adjustButtons');

        if (dateDebut && dateFin) {
            var startDate = new Date(dateDebut);
            var endDate = new Date(dateFin);

            // Calculate the duration in months
            var durationInMonths = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth());

            // Select "Annuel" if the duration is 12 months or more
            if (durationInMonths >= 12) {
                for (var i = 0; i < typeAbonnementSelect.options.length; i++) {
                    if (typeAbonnementSelect.options[i].value === '12') { // "12" represents "Annuel"
                        typeAbonnementSelect.selectedIndex = i;
                        adjustButtons.style.display = 'inline'; // Show buttons if "Annuel" is selected
                        break;
                    }
                }
            } else {
                adjustButtons.style.display = 'none';
            }
        } else {
            adjustButtons.style.display = 'none';
        }
    }


    function calculateTotal() {
        var selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0];
        var selectedAbonnement = document.getElementById('type_abonnement').value;

        var price;
        if (selectedAbonnement === '0.03') {
            price = selectedPackage.getAttribute('data-daily');
        } else if (selectedAbonnement === '1') {
            price = selectedPackage.getAttribute('data-monthly');
        } else if (selectedAbonnement === '3') {
            price = selectedPackage.getAttribute('data-trimestrial');
        } else if (selectedAbonnement === '6') {
            price = selectedPackage.getAttribute('data-semestrial');
        } else if (selectedAbonnement === '12') {
            price = selectedPackage.getAttribute('data-annual');
        }

        if (price) {
            document.getElementById('total').value = price;
            updateReste();
        } else {
            document.getElementById('total').value = '';
            document.getElementById('reste').value = '';
        }
    }

    function updateReste() {
        var total = parseFloat(document.getElementById('total').value) || 0;
        var montantPaye = parseFloat(document.getElementById('montant_paye_input').value) || 0;
        document.getElementById('reste').value = total - montantPaye;
    }

    // Attach event listeners
    document.getElementById('total').addEventListener('input', updateReste);
    document.getElementById('montant_paye_input').addEventListener('input', updateReste);

    function filterCategories() {
        var conventionSelect = document.getElementById('convention');
        var categorieSelect = document.getElementById('categorie_adherence');
        var options = categorieSelect.options;

        // Get the selected value
        var selectedConvention = conventionSelect.value;

        for (var i = 0; i < options.length; i++) {
            var option = options[i];

            if (selectedConvention === "YES") {
                // Show only options with package_type_id = 8 when "Oui" is selected
                if (option.getAttribute('data-type') === '8') {
                    option.style.display = 'block'; // Show package type 8
                } else {
                    option.style.display = 'none'; // Hide all other options
                }
            } else {
                // Show all options except those with package_type_id = 8 when "Non" is selected
                if (option.getAttribute('data-type') === '8') {
                    option.style.display = 'none'; // Hide package type 8
                } else {
                    option.style.display = 'block'; // Show all other options
                }
            }
        }

        // Optionally, reset the selected value to the first visible option
        resetSelectedCategory();
    }

    function resetSelectedCategory() {
        var categorieSelect = document.getElementById('categorie_adherence');
        // Reset selected value to the first visible option
        for (var i = 0; i < categorieSelect.options.length; i++) {
            if (categorieSelect.options[i].style.display !== 'none') {
                categorieSelect.selectedIndex = i;
                break;
            }
        }
        updateAbonnementOptions(); // Update abonnement options
    }

    // Call filterCategories on page load to set initial state based on the default selection
    document.addEventListener('DOMContentLoaded', function() {
        filterCategories();
    });


    // Function to calculate the total amount based on the selected abonnement type and package


    function toggleChequeSection(typePaiementSelect, sectionCheque, labelCheque) {
        if (!typePaiementSelect || !sectionCheque || !labelCheque) return;

        if (typePaiementSelect.value === "3") { // Assuming "3" is the value for cheque payment
            sectionCheque.classList.remove("d-none");
            labelCheque.classList.remove("d-none");
        } else {
            sectionCheque.classList.add("d-none");
            labelCheque.classList.add("d-none");
        }
    }

    function toggleVirementSection(typePaiementSelect, sectionVirement, labelCheque) {
        if (!typePaiementSelect || !sectionVirement || !labelCheque) return;

        if (typePaiementSelect.value === "4") { // Assuming "3" is the value for cheque payment
            sectionVirement.classList.remove("d-none");
            labelCheque.classList.remove("d-none");
        } else {
            sectionCheque.classList.add("d-none");
            sectionVirement.classList.add("d-none");
        }
    }

    let paymentModeIndex = 0; // Initialize the payment mode index

    function addPaymentMode() {
        if (!resteInput || !paymentModesContainer) return;

        const remainingAmount = parseFloat(resteInput.value.replace(' MAD', '')) || 0;

        if (remainingAmount <= 0) {
            alert("Le montant total a été atteint. Vous ne pouvez pas ajouter un autre mode de paiement.");
            return;
        }

        const newPaymentMode = document.importNode(paymentModeTemplate, true);

        newPaymentMode.querySelector('.type_paiement').name = `type_paiement[${paymentModeIndex}]`;
        newPaymentMode.querySelector('.montant_paye').name = `montant_paye[${paymentModeIndex}]`;
        newPaymentMode.querySelector('input[name="nomTitulaire[]"]').name = `nomTitulaire[${paymentModeIndex}]`;
        newPaymentMode.querySelector('input[name="numeroCheque[]"]').name = `numeroCheque[${paymentModeIndex}]`;
        newPaymentMode.querySelector('input[name="dateEmission[]"]').name = `dateEmission[${paymentModeIndex}]`;
        newPaymentMode.querySelector('input[name="numeroCompte[]"]').name = `numeroCompte[${paymentModeIndex}]`;
        newPaymentMode.querySelector('select[name="banqueEmettrice[]"]').name = `banqueEmettrice[${paymentModeIndex}]`;

        const typePaiementSelect = newPaymentMode.querySelector('.type_paiement');
        const montantPayeInput = newPaymentMode.querySelector('.montant_paye');
        const sectionCheque = newPaymentMode.querySelector('.section_cheque');
        const sectionVirement = newPaymentMode.querySelector('.section_virement');
        const labelCheque = newPaymentMode.querySelector('.label_cheque');

        // Event listener for montant_paye input change
        montantPayeInput.addEventListener('input', function() {
            const enteredAmount = parseFloat(montantPayeInput.value) || 0;
            if (enteredAmount > remainingAmount) {
                montantPayeInput.value = remainingAmount.toFixed(2);
                alert("Le montant saisi dépasse le reste à payer.");
            }
            calculateReste(); // Call calculateReste whenever the amount changes
        });

        // Event listener for type_paiement select change
        typePaiementSelect.addEventListener('change', function() {
            toggleChequeSection(typePaiementSelect, sectionCheque, labelCheque);
            toggleVirementSection(typePaiementSelect, sectionVirement, labelCheque);
        });

        // Append the new payment mode to the container
        paymentModesContainer.appendChild(newPaymentMode);

        // Increment the payment mode index
        paymentModeIndex++;

        // Recalculate the remaining amount
        calculateReste();
    }


    function calculateReste() {
        const totalInput = document.getElementById('total');
        const resteInput = document.getElementById('reste');
        const montantPayeInputs = document.querySelectorAll('.montant_paye');
        const montantPayeInput_2 = document.getElementById('montant_paye_input'); // Fixed selector here

        if (!totalInput || !resteInput || !montantPayeInput_2) return; // Added check for montantPayeInput_2

        const totalText = totalInput.value.replace(' MAD', '').trim(); // Remove extra spaces
        const total = parseFloat(totalText) || 0;

        let totalPaid = 0;
        montantPayeInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            totalPaid += value;
        });

        const additionalPaid = parseFloat(montantPayeInput_2.value) || 0; // Ensure montantPayeInput_2 is parsed as a number

        const remaining = total - totalPaid - additionalPaid;
        resteInput.value = remaining.toFixed(2) + ' MAD';

        calculateTotalPrice()
    }



    function generateMatricule() {
        const matriculeInput = document.getElementById('matricule'); // Matricule display (for visual purposes)
        const matriculeHiddenInput = document.getElementById('matricule_input'); // Hidden input to store matricule
        const selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0]; // Package selection

        fetch('../Adherents/get_latest_id.php')
            .then(response => response.json())
            .then(data => {
                const latestId = parseInt(data.latest_id, 10); // Ensure latestId is treated as an integer
                const baseMatricule = 1000 + latestId; // Add 1000 to the latest ID
                const newMatricule = selectedPackage.textContent.trim().charAt(0).toUpperCase();

                if (latestId) {
                    const matricule = baseMatricule + newMatricule;
                    matriculeInput.innerHTML = matricule;
                    matriculeHiddenInput.value = matricule;
                } else {
                    console.error('Error generating matricule.');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function calculateDateFin() {
        var typeAbonnement = document.getElementById('type_abonnement');
        var dateDebutPaiement = document.getElementById('date_debut_paiement');
        var dateFinAbn = document.getElementById('date_fin_abn');

        if (!typeAbonnement || !dateDebutPaiement || !dateFinAbn) return;

        var months = parseFloat(typeAbonnement.value) || 1; // Default to 1 month if no abonnement is selected
        var startDate = new Date(dateDebutPaiement.value);

        if (!isNaN(startDate.getTime())) {
            startDate.setMonth(startDate.getMonth() + months);
            dateFinAbn.value = startDate.toISOString().split('T')[0]; // Set date_fin_abn in 'YYYY-MM-DD' format
        }
    }

    function setTodayAsDefaultDate() {
        var dateDebutPaiement = document.getElementById('date_debut_paiement');
        var today = new Date().toISOString().split('T')[0]; // Get today's date in 'YYYY-MM-DD' format
        dateDebutPaiement.value = today;
        calculateDateFin(); // Automatically calculate the end date
    }

    // Function to adjust the end date by a number of months
    // Global variable to keep track of the number of months added
    var monthsAdded = 0; // Track the number of months added

    function adjustEndDate(months) {
        var dateFinAbn = document.getElementById('date_fin_abn');

        if (!dateFinAbn) {
            console.error("Element with id 'date_fin_abn' not found.");
            return; // Exit if element is not found
        }

        // Temporarily remove 'readonly' so we can modify the value
        dateFinAbn.removeAttribute('readonly');

        var endDate = new Date(dateFinAbn.value);

        // Adding months
        if (months > 0) {
            if (monthsAdded < 12) {
                endDate.setMonth(endDate.getMonth() + months); // Add months
                monthsAdded++; // Increment months added count
            } else {
                alert("Vous ne pouvez pas ajouter plus de 12 mois.");
            }
        }
        // Subtracting months
        else {
            if (monthsAdded > 0) {
                endDate.setMonth(endDate.getMonth() + months); // Subtract months
                monthsAdded--; // Decrement months added count
            } else {
                alert("Aucun mois à diminuer.");
            }
        }

        // Set the updated value back to the input
        dateFinAbn.value = endDate.toISOString().split('T')[0];

        // Optionally, you can add the readonly attribute back if needed
        dateFinAbn.setAttribute('readonly', true);
    }





    // Add event listeners when the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('type_abonnement').addEventListener('change', calculateDateFin);
        document.getElementById('date_debut_paiement').addEventListener('change', calculateDateFin);
        document.getElementById('categorie_adherence').addEventListener('change', updateAbonnementOptions);

        setTodayAsDefaultDate(); // Set today's date when the page loads
        updateAbonnementOptions(); // Set the abonnement options on page load
    });
</script>



<?php
if ($profil == 4) {; ?>
    <script>
        function validateFieldCOm(input) {
            const field = input.name; // Get the name of the field
            const value = input.value; // Get the value of the field
            const errorMessage = document.getElementById(`cin-error-Com`);

            return fetch(`validate_field.php?field=${field}&value=${value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // Show the error message and change the text to "already exists"
                        document.getElementById('cinCom').style.border = '1 px solid red';
                        errorMessage.style.display = 'block';
                        errorMessage.textContent = `Cin existe déjà !`;
                        valid = false
                        return false;
                    }
                    // Hide the error message if the value is valid
                    errorMessage.style.display = 'none';
                    valid = true
                    return true;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
<?php
}; ?>
<script>
    // Function to validate the entire form before submission
    function validateForm() {
        const cin = document.getElementById('cin').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;

        Promise.all([
            validateField('cin', cin),
            validateField('email', email),
            validateField('phone', phone)
        ]).then(results => {
            if (results.every(result => result === true)) {
                document.getElementById('myForm').submit();
            }
        });
    }

    // Attach the generateMatricule function when the page loads
    window.onload = generateMatricule;
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeAbonnement = document.getElementById('type_abonnement');
        const activities = document.querySelectorAll('input[name="activites[]"]');
        const totalInput = document.getElementById('total');
        const dateFinAbn = document.getElementById('date_fin_abn');
        const montantPayeInputs = document.querySelectorAll('.montant_paye');
        const resteInput = document.getElementById('reste');
        const cinInput = document.getElementById('cin');
        const nomInput = document.getElementById('nom');
        const matriculeInput = document.getElementById('matricule');
        const paymentModesContainer = document.getElementById('payment_modes_container');
        const addPaymentModeButton = document.getElementById('add_mode_payement');
        const paymentModeTemplate = document.getElementById('payment_mode_template').content;
        const dateDebutPaiement = document.getElementById('date_debut_paiement');
        const type_abonnement = document.getElementById('')
        const latest_id = document.getElementById('matricule_input');
        var selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0];



        let paymentModeIndex = 0;


        function addPaymentMode() {
            if (!resteInput || !paymentModesContainer) return;

            const remainingAmount = parseFloat(resteInput.value.replace(' MAD', '')) || 0;

            if (remainingAmount <= 0) {
                alert("Le montant total a été atteint. Vous ne pouvez pas ajouter un autre mode de paiement.");
                return;
            }

            const newPaymentMode = document.importNode(paymentModeTemplate, true);

            newPaymentMode.querySelector('.type_paiement').name = `type_paiement[${paymentModeIndex}]`;
            newPaymentMode.querySelector('.montant_paye').name = `montant_paye[${paymentModeIndex}]`;
            newPaymentMode.querySelector('input[name="nomTitulaire[]"]').name = `nomTitulaire[${paymentModeIndex}]`;
            newPaymentMode.querySelector('input[name="numeroCheque[]"]').name = `numeroCheque[${paymentModeIndex}]`;
            newPaymentMode.querySelector('input[name="dateEmission[]"]').name = `dateEmission[${paymentModeIndex}]`;
            newPaymentMode.querySelector('input[name="numeroCompte[]"]').name = `numeroCompte[${paymentModeIndex}]`;
            newPaymentMode.querySelector('select[name="banqueEmettrice[]"]').name = `banqueEmettrice[${paymentModeIndex}]`;

            const typePaiementSelect = newPaymentMode.querySelector('.type_paiement');
            const montantPayeInput = newPaymentMode.querySelector('.montant_paye');
            const sectionCheque = newPaymentMode.querySelector('.section_cheque');
            const sectionVirement = newPaymentMode.querySelector('.section_virement');
            const labelCheque = newPaymentMode.querySelector('.label_cheque');

            montantPayeInput.addEventListener('input', function() {
                const enteredAmount = parseFloat(montantPayeInput.value) || 0;
                if (enteredAmount > remainingAmount) {
                    montantPayeInput.value = remainingAmount.toFixed(2);
                    alert("Le montant saisi dépasse le reste à payer.");
                }
                // calculateReste();
            });

            typePaiementSelect.addEventListener('change', function() {
                toggleChequeSection(typePaiementSelect, sectionCheque, labelCheque);
            });
            typePaiementSelect.addEventListener('change', function() {
                toggleVirementSection(typePaiementSelect, sectionVirement, labelCheque);
            });

            paymentModesContainer.appendChild(newPaymentMode);

            paymentModeIndex++;

            calculateReste();
        }

        // Event Listeners
        if (typeAbonnement) typeAbonnement.addEventListener('change', () => {
            calculateTotal();
            calculateDateFin();
        });

        activities.forEach(activity => {
            activity.addEventListener('change', calculateTotal);
        });

        if (cinInput) cinInput.addEventListener('input', generateMatricule);
        if (nomInput) nomInput.addEventListener('input', generateMatricule);

        if (addPaymentModeButton) {
            addPaymentModeButton.addEventListener('click', function(e) {
                e.preventDefault();
                addPaymentMode();
            });
        }

        const numeroCompteInput = document.getElementById('numeroCompte');
        if (numeroCompteInput) {
            numeroCompteInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
                formatNumeroCompte();
            });
        }

        calculateTotal();
        calculateDateFin();
        calculateReste();
    });
</script>
<script>
    function displayPhoto(input) {
        const preview = document.getElementById('preview');

        // Reset the src and hide it first to prevent double loading
        preview.src = '';
        preview.style.display = 'none';

        // If a new file is selected, update the preview
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Hide the image preview if there's no photo initially
    document.addEventListener('DOMContentLoaded', function() {
        const preview = document.getElementById('preview');
        if (preview.src === '#') {
            preview.style.display = 'none';
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const captureButton = document.getElementById('capture-button');
        const savePhotoButton = document.getElementById('save-photo');
        const camera = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const preview = document.getElementById('preview');
        const profilePhotoInput = document.getElementById('profile-photo');

        let streaming = false;

        // Function to start the webcam
        function startCamera() {
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(stream) {
                    camera.srcObject = stream;
                    streaming = true;
                })
                .catch(function(err) {
                    console.error("Error accessing the camera: ", err);
                });
        }

        // Function to capture a photo from the webcam
        function capturePhoto() {
            if (!streaming) return;

            const context = canvas.getContext('2d');
            canvas.width = camera.videoWidth;
            canvas.height = camera.videoHeight;
            context.drawImage(camera, 0, 0, canvas.width, canvas.height);

            const dataURL = canvas.toDataURL('assets/img/capitalsoft/');
            preview.src = dataURL;
            preview.style.display = 'block';
            savePhotoButton.style.display = 'inline';

            // Stop the camera stream
            let stream = camera.srcObject;
            if (stream) {
                let tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
            }
            streaming = false;
        }

        // Function to handle the photo save button
        function savePhoto() {
            const dataURL = canvas.toDataURL('assets/img/capitalsoft/');
            const file = dataURLToFile(dataURL, 'profile_photo.png');
            const fileInput = new DataTransfer();
            fileInput.items.add(file);
            profilePhotoInput.files = fileInput.files;
        }

        // Convert DataURL to File
        function dataURLToFile(dataURL, filename) {
            let arr = dataURL.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);
            while (n--) u8arr[n] = bstr.charCodeAt(n);
            return new File([u8arr], filename, {
                type: mime
            });
        }

        // Event Listeners
        if (captureButton) {
            captureButton.addEventListener('click', function() {
                startCamera();
            });
        }

        if (savePhotoButton) {
            savePhotoButton.addEventListener('click', function() {
                savePhoto();
            });
        }

        camera.addEventListener('loadeddata', function() {
            capturePhoto();
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let documentIndex = 1; // Start from the first index

        document.getElementById('add-document').addEventListener('click', function() {
            documentIndex++; // Increment index for new fields

            // Create a new row for the document fields
            const newRow = document.createElement('div');
            newRow.className = 'row';
            newRow.innerHTML = `
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="libelle_document_${documentIndex}">Document</label>
                        <input type="text" class="form-control" name="libelle_document[]" id="libelle_document_${documentIndex}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="file_document_${documentIndex}">Document</label>
                        <input type="file" class="form-control" name="file_document[]" id="file_document_${documentIndex}" multiple>
                    </div>
                </div>
            `;

            // Append the new row to the form_document container
            document.getElementById('form_document').appendChild(newRow);
        });
    });
</script>

<?php
require "../inc/footer.php";
?>
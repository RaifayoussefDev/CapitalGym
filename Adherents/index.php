<?php
require "../inc/app.php";
$servername = "localhost";
$username = "root";
$password = ""; // Remplacez par votre mot de passe
$dbname = "privilage"; // Remplacez par le nom de votre base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les activités
$activites_sql = "SELECT id, nom, prix FROM activites";
$activites_result = $conn->query($activites_sql);

$package_sql = "SELECT *  FROM `packages` ORDER BY `packages`.`pack_name` ASC";
$package_result = $conn->query($package_sql);

// Récupérer les types de paiement
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

// Récupérer les utilisateurs avec les détails de l'abonnement et les activités
$sql = "
SELECT 
    u.id,
    u.nom,
    u.prenom,
    u.email,
    u.phone,
    u.etat,
    u.photo,
    a.type AS abonnement_type,
    GROUP_CONCAT(DISTINCT act.nom ORDER BY act.nom ASC SEPARATOR ', ') AS activites,
    GROUP_CONCAT(DISTINCT CONCAT(act.nom, ' (', act.prix, ')') ORDER BY act.nom ASC SEPARATOR ', ') AS activites_prix
FROM 
    users u
JOIN 
    user_activites ua ON u.id = ua.user_id
JOIN 
    abonnements a ON ua.abonnement_id = a.id
LEFT JOIN 
    activites act ON ua.activite_id = act.id
GROUP BY 
    u.id, a.type
ORDER BY 
    u.nom, u.prenom;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $users = [];
}


$sqlp = "SELECT users.id , users.photo, users.nom , users.prenom , users.email , users.phone , abonnements.type AS abonnement_type, abonnements.date_fin , payments.total , payments.reste , type_paiements.type AS type_paiement, payments.montant_paye 
         FROM `users` 
         JOIN `abonnements` ON users.id = abonnements.user_id 
         JOIN `payments` ON abonnements.id = payments.abonnement_id 
         JOIN `type_paiements` ON payments.type_paiement_id = type_paiements.id";

$resultp = $conn->query($sqlp);

if ($resultp->num_rows > 0) {
    while ($rowp = $resultp->fetch_assoc()) {
        $payements[] = $rowp;
    }
} else {
    $payements = [];
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
            <h3 class="fw-bold mb-3">Adhérents</h3>
        </div>
    </div>
    <div class="row">
        <!-- Your existing cards here -->
        <div class="col-sm-6 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"></h4>
                        <button class="btn btn-dark btn-round ms-auto" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i>
                            Ajouter Adhérent
                        </button>
                    </div>
                </div>
                <div class="modal fade custom-modal" id="addRowModal" tabindex="-1" aria-labelledby="addRowModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg"> <!-- Added 'modal-lg' for large size -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRowModalLabel">
                                    <span class="fw-mediumbold"> Nouveau </span>
                                    <span class="fw-light"> Adhérent </span>
                                </h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="wizard-content" id="tab-wizard">
                                    <form id="example-form" action="add_user.php" method="post" class="tab-wizard wizard-circle wizard" enctype="multipart/form-data">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <H1 class="text-secondary" id="matricule" name="matricule"></H1>
                                            </div>
                                        </div>
                                        <h5>Information Personnel</h5>
                                        <section>
                                            <legend></legend>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <img id="preview" src="#" alt="Aperçu de la photo" style="display:none; max-width: 200px; height: auto" />
                                                            <video id="camera" width="320" height="240" autoplay style="display:none;"></video>
                                                            <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label>Photo de profil</label>
                                                            <div id="drop-area" class="drop-area">
                                                                <p>Glissez et déposez une photo ici ou cliquez pour sélectionner une photo</p>
                                                                <input type="file" class="form-control" id="profile-photo" name="profile_photo" accept="image/*" onchange="displayPhoto(this)" />
                                                                <button type="button" class="btn btn-dark mt-2" id="capture-button">Prendre une photo avec la caméra</button>
                                                                <button type="button" id="save-photo" class="btn btn-dark mt-2" style="display:none;">Sauvegarder la photo</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>CIN <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="cin" name="cin" placeholder="CIN" onchange="validateField(this)" required />
                                                        <small class="text-danger" id="cin-error" style="display:none;">Ce champ est obligatoire</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Nom <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" required />
                                                        <small class="text-danger" id="nom-error" style="display:none;">Ce champ est obligatoire</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Prénom <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom" required />
                                                        <small class="text-danger" id="prenom-error" style="display:none;">Ce champ est obligatoire</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Email <span class="text-danger">*</span></label>
                                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required />
                                                        <small class="text-danger" id="email-error" style="display:none;">Ce champ est obligatoire</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Téléphone <span class="text-danger">*</span></label>
                                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Numéro de téléphone" required />
                                                        <small class="text-danger" id="phone-error" style="display:none;">Ce champ est obligatoire</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date de naissance</label>
                                                        <input type="date" class="form-control" id="date_n" name="date_naissance" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Genre</label>
                                                        <select name="genre" class="form-select form-control-lg" id="genre" onchange="fetchActivitiesByGender()">
                                                            <option value="M" selected>Mâle</option>
                                                            <option value="F">Femelle</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Adresse</label>
                                                        <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Adresse" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Fonction</label>
                                                        <input type="text" class="form-control" id="fonction" name="fonction" placeholder="Fonction" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Numéro de GSM en cas d’urgence</label>
                                                        <input type="tel" class="form-control" id="num_urgence" name="num_urgence" placeholder="Numéro d'urgence" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Employeur</label>
                                                        <input type="text" class="form-control" id="employeur" name="employeur" placeholder="Employeur" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-dark" id="add-document">Ajouter Un Document</button>
                                                </div>
                                                <div class="row" id="form_document">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="libelle_document">Document</label>
                                                            <input type="text" class="form-control" name="libelle_document[]" id="libelle_document">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="file_document">Document</label>
                                                            <input type="file" class="form-control" name="file_document[]" id="file_document" multiple>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </section>

                                        <h5>Abonnement</h5>
                                        <section>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Catégorie d'adhésion</label>
                                                        <select name="categorie_adherence" id="categorie_adherence" class="form-select form-control-lg" onchange="updateAbonnementOptions()">
                                                            <?php
                                                            foreach ($packages as $package) { ?>
                                                                <option value="<?= $package['id']; ?>"
                                                                    data-annual="<?= $package['annual_price']; ?>"
                                                                    data-semestrial="<?= $package['semestrial_price']; ?>"
                                                                    data-trimestrial="<?= $package['trimestrial_price']; ?>"
                                                                    data-monthly="<?= $package['monthly_price']; ?>">
                                                                    <?= $package['pack_name']; ?>
                                                                </option>
                                                            <?php
                                                            }; ?>
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
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Conventions d'adhésion</label>
                                                        <select name="convention" class="form-select form-control-lg">
                                                            <option value="YES">Oui</option>
                                                            <option value="NO">Non</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Offres promotionnelles</label>
                                                        <input type="text" class="form-control" id="offre_promo" name="offre_promo" placeholder="Offre promotionnelle" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <textarea name="description" id="description" class="form-control" placeholder="Ajouter une description"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Date Début d'abonnement</label>
                                                        <input type="date" id="date_debut_paiement" name="date_debut_paiement" class="form-control" required />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="Type_dactivite">Type d’activité</label>
                                                    <div class="selectgroup selectgroup-pills">
                                                        <?php foreach ($activites as $activite) : ?>
                                                            <label class="selectgroup-item">
                                                                <input type="checkbox" name="activites[]" value="<?= $activite['id'] ?>" class="selectgroup-input" />
                                                                <span class="selectgroup-button"><?= htmlspecialchars($activite['nom']) ?></span>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Date de fin d’abonnement</label>
                                                        <input type="date" name="date_fin_abn" id="date_fin_abn" class="form-control" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <h5>Paiement</h5>
                                        <section>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Total :</label>
                                                        <input type="text" name="total" id="total" class="form-control" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Reste :</label>
                                                        <input type="text" name="reste" id="reste" class="form-control" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button type="button" class="btn btn-secondary" id="add_mode_payement">Ajouter un mode de paiement</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="payment_modes_container"></div>
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
                                                            <input type="text" name="montant_paye[]" class="form-control montant_paye" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <h2 class="label_cheque d-none">Chèque</h2>
                                                <div class="section_cheque d-none">
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
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </form>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
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
                                    <th>Nom et Prénom</th>
                                    <th>Email</th>
                                    <th>Télephone</th>
                                    <th>Type d'abonnement</th>
                                    <th>Activités</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Nom et Prénom</th>
                                    <th>Email</th>
                                    <th>Télephone</th>
                                    <th>Type d'abonnement</th>
                                    <th>Activités</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php if (count($users) > 0) : ?>
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td style="width:50px">
                                                <img src="../assets/img/capitalsoft/profils/<?php echo !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($user['abonnement_type']); ?></td>
                                            <td><?php echo htmlspecialchars($user['activites']); ?></td>
                                            <td>
                                                <a href="consult.php?id_user=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-info btn-consult">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="modif.php?id_user=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-warning btn-modify">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
        <div class="col-sm-6 col-md-8">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title"></h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Reste</th>
                                    <th>Avance</th>
                                    <th>Total</th>
                                    <th>Type d'abonnement</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Reste</th>
                                    <th>Avance</th>
                                    <th>Total</th>
                                    <th>Type d'abonnement</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php if (count($payements) > 0) : ?>
                                    <?php foreach ($payements as $payement) : ?>
                                        <?php
                                        $rowClass = ($payement['total'] != $payement['montant_paye']) ? 'table-danger' : '';
                                        ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td><?php echo htmlspecialchars($payement['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($payement['prenom']); ?></td>
                                            <td><?php echo htmlspecialchars($payement['reste']); ?></td>
                                            <td><?php echo htmlspecialchars($payement['montant_paye']); ?></td>
                                            <td><?php echo htmlspecialchars($payement['total']); ?></td>
                                            <td><?php echo htmlspecialchars($payement['abonnement_type']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6">No data available</td>
                                    </tr>
                                <?php endif; ?>
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
                        <div class="card-title">Adherents</div>
                        <div class="card-tools">
                            <!-- <div class="dropdown">
                                <button class="btn btn-icon btn-clean me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="card-list py-4">
                        <?php if (count($users) > 0) : ?>
                            <?php foreach ($users as $user) : ?>
                                <div class="item-list">
                                    <div class="avatar">
                                        <img src="../assets/img/capitalsoft/profils/<?php echo !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'admin.webp'; ?>" alt="Profile Picture" class="avatar-img rounded-circle border border-white">
                                    </div>
                                    <div class="info-user ms-3">
                                        <div class="username"><?php echo htmlspecialchars($user['nom']); ?> <?php echo htmlspecialchars($user['prenom']); ?></div>
                                        <div class="status"><?php echo htmlspecialchars($user['activites']); ?></div>
                                    </div>
                                    <a class="btn btn-icon btn-link op-8 me-1" href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                        <i class="far fa-envelope"></i>
                                    </a>
                                    <?php if (htmlspecialchars($user['etat']) == 'actif') : ?>
                                        <a class="btn btn-icon btn-link btn-danger op-8" href="block.php?id_user=<?php echo htmlspecialchars($user['id']); ?>">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else : ?>
                                        <a class="btn btn-icon btn-link btn-success op-8" href="deblock.php?id_user=<?php echo htmlspecialchars($user['id']); ?>">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="item-list">
                                <div class="info-user ms-3">
                                    <div class="username">Aucun utilisateur trouvé.</div>
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
    // Function to update the "type_abonnement" options based on selected package
    function updateAbonnementOptions() {
        var selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0];
        var typeAbonnementSelect = document.getElementById('type_abonnement');
        typeAbonnementSelect.innerHTML = ''; // Clear previous options

        var monthly = selectedPackage.getAttribute('data-monthly');
        var trimestrial = selectedPackage.getAttribute('data-trimestrial');
        var semestrial = selectedPackage.getAttribute('data-semestrial');
        var annual = selectedPackage.getAttribute('data-annual');

        // Add options based on available prices
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


    }

    // Add event listener to update the abonnement options when the package changes
    document.getElementById('categorie_adherence').addEventListener('change', updateAbonnementOptions);

    // Call the function once to set the initial state
    updateAbonnementOptions();
</script>
<script>
    // Function to generate the matricule based on the latest ID
    function generateMatricule() {
        // Fetch the latest ID via an AJAX call to a PHP script
        fetch('get_latest_id.php')
            .then(response => response.json())
            .then(data => {
                const latestId = parseInt(data.latest_id, 10); // Ensure latestId is treated as an integer
                const matricule = 1000 + latestId; // Add 1000 to the latest ID
                document.getElementById('matricule').innerHTML = matricule;
            })
            .catch(error => console.error('Error:', error));
    }





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

        let paymentModeIndex = 0;

        // Object for storing activity costs
        const activityCosts = {
            <?php foreach ($activites as $activite) : ?> '<?= $activite['id'] ?>': <?= number_format($activite['prix'], 2, '.', '') ?>,
            <?php endforeach; ?>
        };

        function calculateTotal() {
            if (!typeAbonnement || !totalInput) return;

            let total = 0;
            const months = parseInt(typeAbonnement.value) || 1;

            activities.forEach(activity => {
                if (activity.checked) {
                    total += (activityCosts[activity.value] || 0) * months;
                }
            });

            totalInput.value = total.toFixed(2) + ' MAD';
            calculateReste(); // Update the remaining amount after calculating the total
        }

        function calculateReste() {
            if (!totalInput || !resteInput) return;

            const totalText = totalInput.value.replace(' MAD', '');
            const total = parseFloat(totalText) || 0;

            let totalPaid = 0;
            montantPayeInputs.forEach(input => {
                const value = parseFloat(input.value) || 0;
                totalPaid += value;
            });

            const remaining = total - totalPaid;
            resteInput.value = remaining.toFixed(2) + ' MAD';
        }

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

        function calculateDateFin() {
            if (!dateDebutPaiement || !dateFinAbn) return;

            const months = parseInt(typeAbonnement.value) || 1;
            let startDate = new Date();

            if (dateDebutPaiement.value) {
                startDate = new Date(dateDebutPaiement.value);
            }

            startDate.setMonth(startDate.getMonth() + months);
            dateFinAbn.value = startDate.toISOString().split('T')[0];
        }

        function formatNumeroCompte() {
            const input = document.getElementById('numeroCompte');
            if (!input) return;

            let value = input.value.replace(/\s+/g, '').replace(/\D/g, '');
            let formattedValue = '';

            if (value.length > 0) formattedValue += value.substring(0, 3) + ' ';
            if (value.length > 3) formattedValue += value.substring(3, 6) + ' ';
            if (value.length > 6) formattedValue += value.substring(6, 22) + ' ';
            if (value.length > 22) formattedValue += value.substring(22, 24);

            input.value = formattedValue.trim();
        }

        function generateMatricule() {
            if (!cinInput || !nomInput || !matriculeInput) return;

            const cin = cinInput.value.trim();
            const nom = nomInput.value.trim();
            if (cin && nom) {
                matriculeInput.innerHTML = cin + nom.charAt(0).toUpperCase();
            }
        }

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
            const labelCheque = newPaymentMode.querySelector('.label_cheque');

            montantPayeInput.addEventListener('input', function() {
                const enteredAmount = parseFloat(montantPayeInput.value) || 0;
                if (enteredAmount > remainingAmount) {
                    montantPayeInput.value = remainingAmount.toFixed(2);
                    alert("Le montant saisi dépasse le reste à payer.");
                }
                calculateReste();
            });

            typePaiementSelect.addEventListener('change', function() {
                toggleChequeSection(typePaiementSelect, sectionCheque, labelCheque);
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
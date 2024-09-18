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

// Récupérer les types de paiement
$type_paiements_sql = "SELECT id, type FROM type_paiements";
$type_paiements_result = $conn->query($type_paiements_sql);

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
                <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="addRowModalLabel" aria-hidden="true">
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
                                        <h5>Information Personnel</h5>
                                        <section>
                                            <legend></legend>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <img id="preview" src="#" alt="Aperçu de la photo" style="display:none; max-width: 90px; height: auto" />
                                                        <label>Photo de profil</label>
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
                                                        <label>Date de naissance</label>
                                                        <input type="date" class="form-control" id="date_n" name="date_naissance" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Genre</label>
                                                        <select name="genre" class="form-select form-control-lg" id="genre" onchange="fetchActivitiesByGender()">
                                                            <option value="M">Mâle</option>
                                                            <option value="F">Femelle</option>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </section>
                                        <h5>Abonnement</h5>
                                        <section>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="date_debut_paiement">Date Début de d'abonnement</label>
                                                        <input type="date" id="date_debut_paiement" name="date_debut_paiement" class="form-control" required>
                                                    </div>

                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Type d'abonnement</label>
                                                        <select name="type_abonnement" id="type_abonnement" class="form-select form-control-lg">
                                                            <option value="1">Mensuel</option>
                                                            <option value="3">Trimestriel</option>
                                                            <option value="6">Semestriel</option>
                                                            <option value="12">Annuel</option>
                                                        </select>
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
                                                <!-- <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Renouvellement automatique</label>
                                                        <select name="renouvellement" id="renouvellement" class="form-select form-control-lg">
                                                            <option value="OUI">OUI</option>
                                                            <option value="NON">NON</option>
                                                        </select>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </section>
                                        <h5>Payement</h5>
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


                                        <!-- Template for new payment mode -->
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
                                        <div class="modal fade" id="success-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center font-18">
                                                        <h3 class="mb-20"></h3>
                                                        <div class="mb-30 text-center">
                                                            <img src="../vendors/images/success.png" />
                                                        </div>
                                                        Êtes-vous sûr(e) de vouloir valider ce traitement ?
                                                    </div>
                                                    <div class="modal-footer justify-content-center">
                                                        <button type="button" class="btn btn-orange" data-dismiss="modal">
                                                            Annuler
                                                        </button>
                                                        <button type="submit" class="btn btn-orange" name="Valider_traitement" id="submitButton" onclick="afficherloader()">
                                                            Valider
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                matriculeInput.value = cin + nom.charAt(0).toUpperCase();
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

<?php
require "../inc/footer.php";
?>
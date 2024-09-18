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

// Récupérer l'ID utilisateur depuis l'URL
if (isset($_GET['id_user'])) {
    $user_id = intval($_GET['id_user']);

    // Récupérer les détails de l'utilisateur
    $sql = "
    SELECT 
        u.id,
        u.matricule,
        u.nom,
        u.prenom,
        u.email,
        u.phone,
        u.cin,
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
} else {
    echo "Aucun utilisateur sélectionné.";
    exit;
}

$conn->close();
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Modifier Adhérent</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-12">
            <div class="card card-stats card-round">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Modifier Adhérent</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="wizard-content" id="tab-wizard">
                        <form id="example-form" action="update_user.php?id_user=<?php echo $user_id ;?>" method="post" class="tab-wizard wizard-circle wizard">
                            <h5>Information Personnel</h5>
                            <section>
                                <legend></legend>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>CIN</label>
                                            <input type="text" class="form-control" id="cin" name="cin" value="<?= htmlspecialchars($user['cin']) ?>" placeholder="CIN" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Matricule</label>
                                            <input type="text" class="form-control" id="matricule" name="matricule" value="<?= htmlspecialchars($user['matricule']) ?>" placeholder="Matricule" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom</label>
                                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" placeholder="Nom" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prénom</label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" placeholder="Prénom" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Téléphone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Numéro de téléphone" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date de naissance</label>
                                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance']) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Genre</label>
                                            <select name="genre" class="form-select form-control-lg" id="genre">
                                                <option value="M" <?= $user['genre'] === 'M' ? 'selected' : '' ?>>Mâle</option>
                                                <option value="F" <?= $user['genre'] === 'F' ? 'selected' : '' ?>>Femelle</option>
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
                                            <label>Type d'abonnement</label>
                                            <select name="type_abonnement" id="type_abonnement" class="form-select form-control-lg">
                                                <option value="1" <?= $user['abonnement_type'] == '1' ? 'selected' : '' ?>>Mensuel</option>
                                                <option value="3" <?= $user['abonnement_type'] == '3' ? 'selected' : '' ?>>Trimestriel</option>
                                                <option value="6" <?= $user['abonnement_type'] == '6' ? 'selected' : '' ?>>Semestriel</option>
                                                <option value="12" <?= $user['abonnement_type'] == '12' ? 'selected' : '' ?>>Annuel</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Type_dactivite">Type d’activité</label>
                                        <div class="selectgroup selectgroup-pills">
                                            <?php
                                            $user_activities = explode(',', $user['activites_ids']);
                                            foreach ($activites as $activite) :
                                                $checked = in_array($activite['id'], $user_activities) ? 'checked' : '';
                                            ?>
                                                <label class="selectgroup-item">
                                                    <input type="checkbox" name="activites[]" value="<?= $activite['id'] ?>" class="selectgroup-input" <?= $checked ?> />
                                                    <span class="selectgroup-button"><?= htmlspecialchars($activite['nom']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date de fin d’abonnement</label>
                                            <input type="date" name="date_fin_abn" id="date_fin_abn" value="<?= htmlspecialchars($user['date_fin_abn']) ?>" class="form-control" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Renouvellement automatique</label>
                                            <select name="renouvellement" id="renouvellement" class="form-select form-control-lg">
                                                <option value="OUI">OUI</option>
                                                <option value="NON">NON</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <h5>Payement</h5>
                            <section>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Montant Payé</label>
                                            <input type="text" name="montant_paye" id="montant_paye" value="<?= htmlspecialchars($user['montant_paye']) ?>" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Avance</label>
                                            <input type="text" name="avance" id="avance" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Type de payement</label>
                                            <select name="type_paiement" id="type_paiement" class="form-select form-control-lg">
                                                <?php foreach ($type_paiements as $type_paiement) : ?>
                                                    <option value="<?= $type_paiement['id'] ?>" <?= $type_paiement['type'] == $user['type_paiement'] ? 'selected' : '' ?>><?= htmlspecialchars($type_paiement['type']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total :</label>
                                            <input type="text" name="total" id="total" class="form-control" value="<?= htmlspecialchars($user['montant_paye'] + $user['reste']) ?>" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Reste :</label>
                                            <input type="text" name="reste" id="reste" class="form-control" value="<?= htmlspecialchars($user['reste']) ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                            </section>
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
        const montantPaye = document.getElementById('montant_paye');
        const avance = document.getElementById('avance');
        const reste = document.getElementById('reste');
        const cinInput = document.getElementById('cin');
        const nomInput = document.getElementById('nom');
        const matriculeInput = document.getElementById('matricule');

        const activityCosts = {
            <?php foreach ($activites as $activite) : ?> '<?= $activite['id'] ?>': <?= number_format($activite['prix'], 2, '.', '') ?>,
            <?php endforeach; ?>
        };

        function calculateTotal() {
            let total = 0;
            let months = 1;
            switch (typeAbonnement.value) {
                case '1':
                    months = 1;
                    break;
                case '3':
                    months = 3;
                    break;
                case '6':
                    months = 6;
                    break;
                case '12':
                    months = 12;
                    break;
            }

            activities.forEach(activity => {
                if (activity.checked) {
                    total += activityCosts[activity.value] * months;
                }
            });

            totalInput.value = total.toFixed(2) + ' MAD';
            calculateReste();
        }

        function calculateDateFin() {
            const months = {
                '1': 1,
                '3': 3,
                '6': 6,
                '12': 12
            } [typeAbonnement.value] || 1;

            const currentDate = new Date();
            currentDate.setMonth(currentDate.getMonth() + months);
            dateFinAbn.value = currentDate.toISOString().split('T')[0];
        }

        function calculateReste() {
            const total = parseFloat(totalInput.value.replace(' MAD', '')) || 0;
            const paid = parseFloat(montantPaye.value) || 0;
            const advance = parseFloat(avance.value) || 0;
            const remaining = total - (paid + advance);
            reste.value = remaining.toFixed(2) + ' MAD';
        }

        function generateMatricule() {
            const cin = cinInput.value.trim();
            const nom = nomInput.value.trim();
            if (cin && nom) {
                matriculeInput.value = cin + nom.charAt(0).toUpperCase();
            }
        }

        typeAbonnement.addEventListener('change', () => {
            calculateTotal();
            calculateDateFin();
        });

        activities.forEach(activity => {
            activity.addEventListener('change', calculateTotal);
        });

        montantPaye.addEventListener('input', calculateReste);
        avance.addEventListener('input', calculateReste);
        cinInput.addEventListener('input', generateMatricule);
        nomInput.addEventListener('input', generateMatricule);
    });
</script>

<?php
require "../inc/footer.php";
?>

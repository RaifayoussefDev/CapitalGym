<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Retrieve user ID from URL and user details
if (isset($_GET['id_user'])) {
    $user_id = intval($_GET['id_user']);

    // Requête pour récupérer les informations de l'utilisateur, abonnement, paiement et type de paiement
    $sql = "SELECT * FROM users u
            JOIN abonnements a ON u.id = a.user_id
            JOIN packages p ON p.id = a.type_abonnement
            JOIN payments py ON a.id = py.abonnement_id
            JOIN type_paiements tp ON py.type_paiement_id = tp.id
            WHERE u.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "Aucun utilisateur trouvé.";
        exit;
    }

    // Requête pour récupérer les paiements associés à l'utilisateur
    $sql_payments = "SELECT * FROM payments,type_paiements WHERE payments.type_paiement_id = type_paiements.id AND user_id = ?";
    $stmt_payments = $conn->prepare($sql_payments);
    $stmt_payments->bind_param('i', $user_id);
    $stmt_payments->execute();
    $payments_result = $stmt_payments->get_result();
    $payments = $payments_result->fetch_all(MYSQLI_ASSOC);

    // Requête pour récupérer les chèques associés à l'utilisateur
    $sql_cheques = "SELECT * FROM cheque WHERE id_utilisateur = ?";
    $stmt_cheques = $conn->prepare($sql_cheques);
    $stmt_cheques->bind_param('i', $user_id);
    $stmt_cheques->execute();
    $cheques_result = $stmt_cheques->get_result();
    $cheques = $cheques_result->fetch_all(MYSQLI_ASSOC);

    // Requête pour récupérer les virements associés à l'utilisateur
    $sql_virements = "SELECT * FROM virement WHERE id_utilisateur = ?";
    $stmt_virements = $conn->prepare($sql_virements);
    $stmt_virements->bind_param('i', $user_id);
    $stmt_virements->execute();
    $virements_result = $stmt_virements->get_result();
    $virements = $virements_result->fetch_all(MYSQLI_ASSOC);




    // Requête pour récupérer les activités associées à l'utilisateur
    $sql_activites = "SELECT a.*, ua.date_inscription FROM activites a
                      JOIN user_activites ua ON a.id = ua.activite_id
                      WHERE ua.user_id = ?";
    $stmt_activites = $conn->prepare($sql_activites);
    $stmt_activites->bind_param('i', $user_id);
    $stmt_activites->execute();
    $activites_result = $stmt_activites->get_result();
    $activites = $activites_result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Aucun utilisateur sélectionné.";
    exit;
}

$conn->close();
?>

<style>
    /* Your existing CSS styles */
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type d'abonnement:</label>
                                    <span><?= htmlspecialchars($user['pack_name']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date de Debut d’abonnement:</label>
                                    <span><?= htmlspecialchars($user['date_debut']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date de fin d’abonnement:</label>
                                    <span><?= htmlspecialchars($user['date_fin']) ?></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <h5>Paiements</h5>
                    <section>
                        <div class="row">
                            <?php if (count($payments) > 0) : ?>
                                <?php foreach ($payments as $payment) : ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Montant Payé:</label>
                                            <span><?= htmlspecialchars($payment['montant_paye']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date de Paiement:</label>
                                            <span><?= htmlspecialchars($payment['date_paiement']) ?></span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Type de Paiement:</label>
                                            <span><?= htmlspecialchars($payment['type']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span>Aucun paiement trouvé.</span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <h5>Chèques</h5>
                    <section>
                        <div class="row">
                            <?php if (count($cheques) > 0) : ?>
                                <?php foreach ($cheques as $cheque) : ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nom Titulaire:</label>
                                            <span><?= htmlspecialchars($cheque['nomTitulaire']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Numéro de Chèque:</label>
                                            <span><?= htmlspecialchars($cheque['numeroCheque']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date d'Émission:</label>
                                            <span><?= htmlspecialchars($cheque['dateEmission']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Banque Emettrice:</label>
                                            <span><?= htmlspecialchars($cheque['banqueEmettrice']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span>Aucun chèque trouvé.</span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <h5>Virements</h5>
                    <section>
                        <div class="row">
                            <?php if (count($virements) > 0) : ?>
                                <?php foreach ($virements as $virement) : ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nom Émetteur:</label>
                                            <span><?= htmlspecialchars($virement['nomEmetteur']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Référence:</label>
                                            <span><?= htmlspecialchars($virement['reference']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date d'Émission:</label>
                                            <span><?= htmlspecialchars($virement['dateImitation']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Banque Émettrice:</label>
                                            <span><?= htmlspecialchars($virement['banqueEmettrice']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span>Aucun virement trouvé.</span>
                            <?php endif; ?>
                        </div>
                    </section>


                    <h5>Activités</h5>
                    <section>
                        <div class="row">
                            <?php if (count($activites) > 0) : ?>
                                <?php foreach ($activites as $activite) : ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nom de l'Activité:</label>
                                            <span><?= htmlspecialchars($activite['nom']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date d'Inscription:</label>
                                            <span><?= htmlspecialchars($activite['date_inscription']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span>Aucune activité trouvée.</span>
                            <?php endif; ?>
                        </div>
                    </section>
                    <h5>Contrat</h5>
                    <section id="contract-section" class="mt-3">
                        <?php
                        // Vérification si l'utilisateur a un contrat
                        $has_contract = !empty($user['contract_name']); // Vérifie si un nom de contrat existe

                        // Exemple d'ID utilisateur (assurez-vous que $user['id'] est défini dans votre code)
                        $user_id = $user_id;
                        $contract_name = $user['contract_name']; // Nom du contrat
                        ?>

                        <?php if ($has_contract): ?>
                            <!-- Si un contrat existe, afficher le bouton de téléchargement -->
                            <button id="downloadContractBtn" class="btn btn-secondary mt-3" onclick="downloadContract('<?php echo $contract_name; ?>')">
                                Télécharger le Contrat
                            </button>
                            <button id="prepareContractBtn" class="btn btn-success" onclick="prepareContract(<?php echo $user_id; ?>)">
                                Préparer Un Autre Contrat
                            </button>
                        <?php else: ?>
                            <!-- Si aucun contrat n'existe, afficher le bouton pour préparer le contrat -->
                            <button id="prepareContractBtn" class="btn btn-success" onclick="prepareContract(<?php echo $user_id; ?>)">
                                Préparer le Contrat
                            </button>
                        <?php endif; ?>

                        <!-- Section d'affichage du contrat -->
                        <div id="contractContent" class="mt-3" style="display: none;">
                            <!-- Le contenu du contrat sera chargé ici par JavaScript -->
                        </div>
                    </section>


                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour préparer le contrat
    function prepareContract(userId) {
        // Utiliser AJAX pour préparer le contrat et ensuite l'afficher
        fetch(`../contrat/preparer_contrat.php?id_user=${userId}`)
            .then(response => response.text())
            .then(data => {
                // Supposons que la réponse contient le nom du contrat après préparation
                const contractName = data.trim(); // Extraire le nom du contrat de la réponse

                // Masquer le bouton Préparer le Contrat une fois qu'il est préparé
                document.getElementById("prepareContractBtn").style.display = 'none';

                // Afficher le bouton de téléchargement du contrat
                const downloadBtn = document.getElementById("downloadContractBtn");
                downloadBtn.style.display = 'block'; // S'assurer que le bouton est visible

                // Mettre à jour l'attribut onclick pour télécharger le bon contrat
                downloadBtn.setAttribute("onclick", `downloadContract('${contractName}')`);

                // Rediriger vers la page consult.php après préparation du contrat
                window.location.href = 'consult.php'; // Redirection vers consult.php
            })
            .catch(error => console.error('Erreur lors de la préparation du contrat:', error));
    }



    // Fonction pour télécharger le contrat
    function downloadContract(contractName) {
        // Déclencher le téléchargement du fichier pour le contrat préparé en utilisant son nom
        window.location.href = `../contrat/${contractName}`;
    }
</script>


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
<?php
require "../inc/footer.php";
?>
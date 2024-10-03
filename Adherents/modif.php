<?php
require "../inc/app.php";

require "../inc/conn_db.php";

// Retrieve packages
$package_sql = "SELECT * FROM `packages` ORDER BY `pack_name` ASC";
$package_result = $conn->query($package_sql);
$packages = [];
if ($package_result->num_rows > 0) {
    while ($row = $package_result->fetch_assoc()) {
        $packages[] = $row;
    }
}

// Retrieve payment types
$type_paiements_sql = "SELECT id, type FROM type_paiements";
$type_paiements_result = $conn->query($type_paiements_sql);
$type_paiements = [];
if ($type_paiements_result->num_rows > 0) {
    while ($row = $type_paiements_result->fetch_assoc()) {
        $type_paiements[] = $row;
    }
}

// Retrieve user ID from the URL
if (isset($_GET['id_user'])) {
    $user_id = intval($_GET['id_user']);

    // Retrieve user details and associated subscription data
    $sql = "SELECT u.*, a.*, p.pack_name, py.*, tp.type 
            FROM users u 
            JOIN abonnements a ON u.id = a.user_id 
            JOIN packages p ON a.type_abonnement = p.id 
            JOIN payments py ON a.id = py.abonnement_id 
            JOIN type_paiements tp ON py.type_paiement_id = tp.id 
            WHERE u.id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            echo "No subscription found for this user.";
            exit;
        }
    } else {
        echo "Failed to prepare SQL statement.";
        exit;
    }
} else {
    echo "No user selected.";
    exit;
}

// Fetch commercial agents
$sqlc = "SELECT * FROM users WHERE role_id = 4";
$resultc = $conn->query($sqlc);
$commercials = [];
if ($resultc->num_rows > 0) {
    while ($rowc = $resultc->fetch_assoc()) {
        $commercials[] = $rowc;
    }
} else {
    $commercials = [];
}

// Close connection
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
                        <form id="example-form" action="update_user.php?id_user=<?php echo $user_id; ?>" method="post" class="tab-wizard wizard-circle wizard">
                            <h5>Information Personnel</h5>
                            <section>
                                <legend></legend>
                                <div class="row">
                                    <!-- Profile Photo Section -->
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php if (!empty($user['profile_photo'])): ?>
                                                    <img id="preview" src="../assets/img/capitalsoft/profils/<?php echo htmlspecialchars($user['profile_photo']) ?>" alt="Aperçu de la photo" style="max-width: 200px; height: auto" />
                                                <?php else: ?>
                                                    <img id="preview" src="#" alt="Aperçu de la photo" style="display:none; max-width: 200px; height: auto" />
                                                <?php endif; ?>
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

                                    <!-- CIN -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>CIN <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cin" name="cin" placeholder="CIN" onchange="validateField(this)" value="<?= htmlspecialchars($user['cin']) ?>" required />
                                            <small class="text-danger" id="cin-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Nom -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" required value="<?= htmlspecialchars($user['nom']) ?>" />
                                            <small class="text-danger" id="nom-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Prénom -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom" required value="<?= htmlspecialchars($user['prenom']) ?>" />
                                            <small class="text-danger" id="prenom-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($user['email']) ?>" />
                                            <small class="text-danger" id="email-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Téléphone -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Téléphone <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Numéro de téléphone" required value="<?= htmlspecialchars($user['phone']) ?>" />
                                            <small class="text-danger" id="phone-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Date de naissance -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date de naissance</label>
                                            <input type="date" class="form-control" id="date_n" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance']) ?>" />
                                        </div>
                                    </div>

                                    <!-- Genre -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Genre</label>
                                            <select name="genre" class="form-select form-control-lg" id="genre" onchange="fetchActivitiesByGender()">
                                                <option value="M" <?= $user['genre'] === 'M' ? 'selected' : '' ?>>Mâle</option>
                                                <option value="F" <?= $user['genre'] === 'F' ? 'selected' : '' ?>>Femelle</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Commercial -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Commercial <span class="text-danger">*</span></label>
                                            <select name="commercial" id="commercial" class="form-select form-control-lg" required>
                                                <?php foreach ($commercials as $commercial) : ?>
                                                    <option value="<?= htmlspecialchars($commercial['id']) ?>" <?= $commercial['id'] == $user['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($commercial['nom']) ?> <?= htmlspecialchars($commercial['prenom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-danger" id="commercial-error" style="display:none;">Ce champ est obligatoire</small>
                                        </div>
                                    </div>

                                    <!-- Adresse -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Adresse" value="<?= htmlspecialchars($user['adresse']) ?>" />
                                        </div>
                                    </div>

                                    <!-- Fonction -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fonction</label>
                                            <input type="text" class="form-control" id="fonction" name="fonction" placeholder="Fonction" value="<?= htmlspecialchars($user['fonction']) ?>" />
                                        </div>
                                    </div>

                                    <!-- Numéro de GSM en cas d’urgence -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Numéro de GSM en cas d’urgence</label>
                                            <input type="tel" class="form-control" id="num_urgence" name="num_urgence" placeholder="Numéro d'urgence" value="<?= htmlspecialchars($user['num_urgence']) ?>" />
                                        </div>
                                    </div>

                                    <!-- Employeur -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Employeur</label>
                                            <input type="text" class="form-control" id="employeur" name="employeur" placeholder="Employeur" value="<?= htmlspecialchars($user['employeur']) ?>" />
                                        </div>
                                    </div>

                                    <!-- Add Document Button -->
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-dark" id="add-document">Ajouter Un Document</button>
                                    </div>

                                    <!-- Existing Documents (if any) -->
                                    <div class="row" id="form_document">
                                        <?php
                                        // Assuming you have a way to retrieve existing documents for the user
                                        // Example: $documents = getUserDocuments($user_id);
                                        // Replace the following with actual retrieval logic

                                        // Placeholder for existing documents
                                        $documents = [
                                            // ['libelle' => 'Document 1', 'file_path' => 'path/to/document1.pdf'],
                                            // ['libelle' => 'Document 2', 'file_path' => 'path/to/document2.jpg'],
                                        ];

                                        foreach ($documents as $doc) :
                                        ?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="libelle_document">Document</label>
                                                    <input type="text" class="form-control" name="libelle_document[]" id="libelle_document" value="<?= htmlspecialchars($doc['libelle']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="file_document">Document</label>
                                                    <input type="file" class="form-control" name="file_document[]" id="file_document">
                                                    <?php if (!empty($doc['file_path'])): ?>
                                                        <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">Voir le document</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
                                                // Assuming $current_subscription holds the current subscription data for modification
                                                foreach ($packages as $package) {
                                                    // Check if the package is currently selected
                                                    $selected = (isset($current_subscription) && $current_subscription['categorie_adherence'] == $package['id']) ? 'selected' : ''; ?>
                                                    <option value="<?= $package['id']; ?>"
                                                        data-daily="<?= $package['Daily_price']; ?>"
                                                        data-annual="<?= $package['annual_price']; ?>"
                                                        data-semestrial="<?= $package['semestrial_price']; ?>"
                                                        data-trimestrial="<?= $package['trimestrial_price']; ?>"
                                                        data-monthly="<?= $package['monthly_price']; ?>"
                                                        <?= $selected; ?>>
                                                        <?= htmlspecialchars($package['pack_name']); ?>
                                                    </option>
                                                <?php }; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Type d'abonnement</label>
                                            <select name="type_abonnement" id="type_abonnement" class="form-select form-control-lg">
                                                <option value="" disabled selected>Sélectionnez un type</option>
                                                <!-- Dynamically populate this based on selection -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Conventions d'adhésion</label>
                                            <select name="convention" class="form-select form-control-lg">
                                                <option value="YES" <?= (isset($current_subscription) && $current_subscription['convention'] == 'YES') ? 'selected' : ''; ?>>Oui</option>
                                                <option value="NO" <?= (isset($current_subscription) && $current_subscription['convention'] == 'NO') ? 'selected' : ''; ?>>Non</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Offres promotionnelles</label>
                                            <input type="text" class="form-control" id="offre_promo" name="offre_promo" placeholder="Offre promotionnelle" value="<?= isset($current_subscription) ? htmlspecialchars($current_subscription['offre_promo']) : ''; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" id="description" class="form-control" placeholder="Ajouter une description"><?= isset($current_subscription) ? htmlspecialchars($current_subscription['description']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date Début d'abonnement</label>
                                            <input type="date" id="date_debut_paiement" name="date_debut_paiement" class="form-control" required value="<?= isset($current_subscription) ? $current_subscription['date_debut_paiement'] : ''; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date de fin d’abonnement</label>
                                            <div class="input-group">
                                                <input type="date" name="date_fin_abn" id="date_fin_abn" class="form-control" readonly value="<?= isset($current_subscription) ? $current_subscription['date_fin_abn'] : ''; ?>" />
                                                <button type="button" class="btn btn-dark" onclick="adjustEndDate(1)">+</button>
                                                <button type="button" class="btn btn-dark" onclick="adjustEndDate(-1)">-</button>
                                            </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsPerPage = 6;
        let currentPage = 1;
        let usersData = [];
        let filteredUsers = [];

        // Fetch user data from the server
        fetch('users.php')
            .then(response => response.json())
            .then(users => {
                usersData = users;
                filteredUsers = usersData; // Initially no filter applied
                displayUsers();
                setupPagination();
            })
            .catch(error => console.error('Error fetching users:', error));

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
        typeAbonnementSelect.innerHTML = ''; // Clear previous options

        // Get package price attributes
        var daily = selectedPackage.getAttribute('data-daily');
        var monthly = selectedPackage.getAttribute('data-monthly');
        var trimestrial = selectedPackage.getAttribute('data-trimestrial');
        var semestrial = selectedPackage.getAttribute('data-semestrial');
        var annual = selectedPackage.getAttribute('data-annual');

        const totalInput = document.getElementById('total');
        const resteInput = document.getElementById('reste');

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

        // Update total amount calculation when abonnement is selected
        typeAbonnementSelect.addEventListener('change', calculateTotal);

        calculateTotal();
        generateMatricule();
        calculateDateFin();
    }

    // Function to calculate the total amount based on the selected abonnement type and package
    function calculateTotal() {
        var selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0];
        var selectedAbonnement = document.getElementById('type_abonnement').value;

        // Get the selected package prices
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

        // If price is available, update the total and reste fields
        if (price) {
            document.getElementById('total').value = price;
            document.getElementById('reste').value = price;
        } else {
            document.getElementById('total').value = '';
            document.getElementById('reste').value = '';
        }
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

    function calculateReste() {
        const totalInput = document.getElementById('total');
        const resteInput = document.getElementById('reste');
        const montantPayeInputs = document.querySelectorAll('.montant_paye');

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

    function generateMatricule() {
        const matriculeInput = document.getElementById('matricule'); // Matricule display (for visual purposes)
        const matriculeHiddenInput = document.getElementById('matricule_input'); // Hidden input to store matricule
        const selectedPackage = document.getElementById('categorie_adherence').selectedOptions[0]; // Package selection

        fetch('get_latest_id.php')
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
    let monthsAdded = 0;

    function adjustEndDate(months) {
        var dateFinAbn = document.getElementById('date_fin_abn');
        var endDate = new Date(dateFinAbn.value);

        // Adding months
        if (months > 0) {
            if (monthsAdded < 12) { // Limit to a maximum of 12 months
                endDate.setMonth(endDate.getMonth() + months); // Adjust the month
                monthsAdded++; // Increment the counter for added months
            } else {
                alert("Vous ne pouvez pas ajouter plus de 12 mois."); // Alert for exceeding 12 months
            }
        }
        // Subtracting months
        else {
            if (monthsAdded > 0) { // Only allow decrease if at least one month was added
                endDate.setMonth(endDate.getMonth() + months); // Adjust the month
                monthsAdded--; // Decrement the counter for added months
            } else {
                alert("Aucune mois à diminuer."); // Alert if no months were added
            }
        }
        dateFinAbn.value = endDate.toISOString().split('T')[0]; // Update the input value
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
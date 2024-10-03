<?php
require "../inc/app.php";
require "../inc/conn_db.php";

// Fetch all roles
$roles_sql = "SELECT id, name FROM role";
$roles_result = $conn->query($roles_sql);

$roles = [];
if ($roles_result->num_rows > 0) {
    while ($row = $roles_result->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Set the first role ID for the default selection
$first_role_id = $roles[0]['id'];

// Fetch habilitations
$habilitations_sql = "
    SELECT h.id_habilitation, h.id_role, h.nom_page, h.statut_actif, r.name
    FROM habilitation h
    JOIN role r ON h.id_role = r.id";
$habilitations_result = $conn->query($habilitations_sql);

$habilitations = [];
if ($habilitations_result->num_rows > 0) {
    while ($row = $habilitations_result->fetch_assoc()) {
        $habilitations[] = $row;
    }
}

$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Function to filter habilitations by role
        function filterByRole(roleId) {
            $('tbody tr').each(function() {
                const rowRoleId = $(this).data('role');
                if (roleId === 'all' || roleId == rowRoleId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Set default filter to the first role
        const defaultRoleId = '<?php echo $first_role_id; ?>';
        $('#role-filter').val(defaultRoleId);

        // Trigger the filtering on page load
        filterByRole(defaultRoleId);

        // Handle role filter change
        $('#role-filter').on('change', function() {
            const selectedRoleId = $(this).val();
            filterByRole(selectedRoleId);
        });

        // Handle status change
        // Handle status change
        $('.switch-status').on('change', function() {
            const habilitationId = $(this).data('id');
            const newStatus = $(this).is(':checked') ? 1 : 0;

            // AJAX call to update status in the database
            $.ajax({
                url: 'update_habilitation_status.php',
                type: 'POST',
                data: {
                    id: habilitationId,
                    statut_actif: newStatus
                },
                success: function(response) {
                    if (response === 'success') {
                        $.notify({
                            message: 'Statut mis à jour avec succès'
                        }, {
                            type: 'success',
                            delay: 2000,
                            placement: {
                                from: "top",
                                align: "right"
                            }
                        });
                    } else {
                        $.notify({
                            message: 'Erreur lors de la mise à jour du statut: ' + response.message
                        }, {
                            type: 'danger',
                            delay: 2000,
                            placement: {
                                from: "top",
                                align: "right"
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $.notify({
                        message: 'Une erreur est survenue: ' + error
                    }, {
                        type: 'danger',
                        delay: 2000,
                        placement: {
                            from: "top",
                            align: "right"
                        }
                    });
                }
            });
        });

    });
</script>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Habilitations</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="form-group">
                        <!-- Filter Dropdown for Roles -->
                        <label for="role-filter">Filtrer par rôle</label>
                        <select id="role-filter" class="form-control">
                            <option value="all">Tous les rôles</option>
                            <?php foreach ($roles as $role) : ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom de la Page</th>
                                    <th>Rôle</th>
                                    <th>Statut Actif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($habilitations) > 0) : ?>
                                    <?php foreach ($habilitations as $habilitation) : ?>
                                        <tr data-role="<?php echo $habilitation['id_role']; ?>">
                                            <td><?php echo htmlspecialchars($habilitation['nom_page']); ?></td>
                                            <td><?php echo htmlspecialchars($habilitation['name']); ?></td>
                                            <td>
                                                <!-- Display the checkbox, checked if statut_actif is 'actif' -->
                                                <input type="checkbox" class="switch-status" data-id="<?php echo $habilitation['id_habilitation']; ?>" <?php echo $habilitation['statut_actif'] === 'actif' ? 'checked' : ''; ?> data-toggle="switch">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4">No habilitations available</td>
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

<?php
require "../inc/footer.php";
?>
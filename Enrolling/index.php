<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Management Interface</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <style>
        .is-invalid {
            border-color: #dc3545;
        }

        .error-message {
            font-size: 0.8em;
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h3>User Management</h3>

        <!-- Notification area for Bootstrap alerts -->
        <div id="notification-area"></div>

        <button id="transferDataButton" class="btn btn-primary mb-3">Transfer Data</button>

        <table id="userTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type d'Abonnement</th>
                    <th>Activites</th>
                    <th>ID Card</th>
                    <th>Actions</th>
                    <th>New ID Card</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- jQuery, Bootstrap, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#userTable').DataTable({
                "ajax": "fetch_users.php",
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "nom"
                    },
                    {
                        "data": "prenom"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "phone"
                    },
                    {
                        "data": "type_abonnement",
                        "render": function(data) {
                            return data == 2 ? "Gold" : data == 3 ? "Platinum" : "Silver";
                        }
                    },
                    {
                        "data": "activites"
                    },
                    {
                        "data": "id_card"
                    },
                    {
                        "data": "id_card",
                        "render": function(data, type, row) {
                            return `<input type="text" class="form-control id-card-input" data-id="${row.id}" placeholder="Enter ID Card" maxlength="10">`;
                        }
                    },
                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<button class="btn btn-danger btn-sm remove-badge-btn" data-id="${data}">Enlever le Badge</button>`;
                        }
                    }

                ]
            });

            // Character replacement mapping
            const charMapping = {
                '&': '1',
                'é': '2',
                '"': '3',
                "'": '4',
                '(': '5',
                '-': '6',
                'è': '7',
                '_': '8',
                'ç': '9',
                'à': '0'
            };

            // Function to replace characters
            function replaceChars(input) {
                return input.split('').map(char => charMapping[char] || char).join('');
            }

            // Function to show Bootstrap alert
            function showAlert(message, type = 'success') {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`;
                $('#notification-area').html(alertHtml);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 3000);
            }

            let timer;

            // Input field change handler
            $('#userTable tbody').on('input', '.id-card-input', function() {
                const $input = $(this); // Cache the input element
                const userId = $input.data('id');
                let idCardValue = $input.val();

                // Replace special characters in the input field in real-time
                idCardValue = replaceChars(idCardValue);
                $input.val(idCardValue);

                clearTimeout(timer);

                // Set a new timer for 2 seconds
                timer = setTimeout(() => {
                    $.ajax({
                        url: 'update_id_card.php',
                        method: 'POST',
                        data: {
                            id: userId,
                            id_card: idCardValue
                        },
                        success: function(response) {
                            if (response === "ID Card updated successfully") {
                                showAlert("ID Card updated successfully.", "success");
                                $input.prop('readonly', true).removeClass('is-invalid'); // Mark as readonly and remove error styling
                                $input.next('.error-message').remove();
                                table.ajax.reload();
                            } else {
                                $input.addClass('is-invalid'); // Show error style
                                $input.next('.error-message').remove();
                                $input.after(`<small class="error-message text-danger">${response}</small>`);
                            }
                        },
                        error: function() {
                            $input.addClass('is-invalid');
                            $input.next('.error-message').remove();
                            $input.after('<small class="error-message text-danger">Error updating ID Card.</small>');
                        }
                    });
                }, 2000);
            });

            $('#transferDataButton').on('click', function() {
                $.ajax({
                    url: 'transfer_data.php',
                    method: 'POST',
                    success: function(response) {
                        showAlert("Data transferred successfully.", "success");
                    },
                    error: function() {
                        showAlert("Error transferring data.", "danger");
                    }
                });
            });

            // Handle badge removal
            $('#userTable tbody').on('click', '.remove-badge-btn', function() {
                const userId = $(this).data('id');
                const idCardInput = $(this).closest('tr').find('.id-card-input'); // Get the ID Card input of the same row

                if (confirm('Voulez-vous vraiment enlever le badge de cet utilisateur ?')) {
                    $.ajax({
                        url: 'remove_badge.php',
                        method: 'POST',
                        data: {
                            id: userId
                        },
                        success: function(response) {
                            if (response === "Badge removed successfully") {
                                showAlert("Badge retiré avec succès.", "success");

                                // Empty the ID Card input field after removing the badge
                                idCardInput.val('');

                                table.ajax.reload(); // Reload the table
                            } else {
                                showAlert(response, "danger");
                            }
                        },
                        error: function() {
                            showAlert("Erreur lors de la suppression du badge.", "danger");
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>

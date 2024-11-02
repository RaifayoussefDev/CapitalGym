<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Management Interface</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
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
                    <th>ID Card</th>
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
                        "data": "type_abonnement"
                    },
                    {
                        "data": "id_card",
                        "render": function(data, type, row) {
                            return `<input type="text" class="form-control id-card-input" data-id="${row.id}" placeholder="Enter ID Card" maxlength="10">`;
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

                // Auto-dismiss alert after 3 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 3000);
            }

            // Timer variable for delayed update
            let timer;

            // Input field change handler
            $('#userTable tbody').on('input', '.id-card-input', function() {
                const userId = $(this).data('id');
                let idCardValue = $(this).val();

                // Replace special characters in the input field in real-time
                idCardValue = replaceChars(idCardValue);
                $(this).val(idCardValue);

                // Clear the previous timer
                clearTimeout(timer);

                // Set a new timer for 2 seconds
                timer = setTimeout(() => {
                    // AJAX request to update id_card in the database
                    $.ajax({
                        url: 'update_id_card.php',
                        method: 'POST',
                        data: {
                            id: userId,
                            id_card: idCardValue
                        },
                        success: function(response) {
                            showAlert("ID Card updated successfully.", "success");
                            table.ajax.reload(); // Reload DataTable
                        },
                        error: function() {
                            showAlert("Error updating ID Card.", "danger");
                        }
                    });
                }, 2000); // 2-second delay
            });

            // Button click handler for transferring data
            $('#transferDataButton').on('click', function() {
                $.ajax({
                    url: 'transfer_data.php', // Your PHP script to handle the data transfer
                    method: 'POST',
                    success: function(response) {
                        showAlert("Data transferred successfully.", "success");
                    },
                    error: function() {
                        showAlert("Error transferring data.", "danger");
                    }
                });
            });
        });
    </script>
</body>

</html>
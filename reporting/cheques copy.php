<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Chèques</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        body {
            padding: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Gestion des Chèques</h1>
        <div class="table-responsive">
            <table id="multi-filter-select" class="display table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Numéro de Chèque</th>
                        <th>Adhérent</th>
                        <th>Date d'Émission</th>
                        <th>Banque</th>
                        <th>Montant Payer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Example data, replace with your PHP code to fetch data -->
                    <tr>
                        <td>001</td>
                        <td>Jean Dupont</td>
                        <td>2024-09-30</td>
                        <td>Banque Populaire</td>
                        <td>1000 MAD</td>
                        <td>Validé</td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td>Marie Curie</td>
                        <td>2024-09-25</td>
                        <td>Attijariwafa Bank</td>
                        <td>1500 MAD</td>
                        <td>En Attente</td>
                    </tr>
                    <!-- Add more rows as needed -->
                </tbody>
                <tfoot>
                    <tr>
                        <th>Numéro de Chèque</th>
                        <th>Adhérent</th>
                        <th>Date d'Émission</th>
                        <th>Banque</th>
                        <th>Montant Payer</th>
                        <th>Status</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#multi-filter-select").DataTable({
                pageLength: 5,
                dom: 'Bfrtip',  // Enable buttons
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Exporter en Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Exporter en PDF',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimer',
                        className: 'btn btn-primary'
                    }
                ],
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        var select = $('<select class="form-select"><option value=""></option></select>')
                            .appendTo($(column.footer()).empty())
                            .on("change", function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? "^" + val + "$" : "", true, false).draw();
                            });

                        column.data().unique().sort().each(function(d, j) {
                            select.append('<option value="' + d + '">' + d + "</option>");
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>

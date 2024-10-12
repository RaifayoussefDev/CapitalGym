</div>

<footer class="footer bg-dark text-white py-3">
    <div class="container-fluid d-flex justify-content-center align-items-center">
        <p class="mb-0 text-center">
            Créé par <strong>Capitalsoft</strong> |
            Site officiel : <a href="https://www.capitalsoft.ma" class="text-white">capitalsoft.ma</a> |
            &copy; <span id="currentYear"></span> Capitalsoft. Tous droits réservés.

        </p>
    </div>
</footer>


</div>


<!-- End Custom template -->
</div>
<script>
    // Script pour mettre à jour l'année actuelle
    document.getElementById("currentYear").textContent = new Date().getFullYear();
</script>
<script>
  $(document).ready(function() {
    // Hide the loader once the content is loaded
    $('#custom-loader').fadeOut('slow');
  });

  // Show the loader on page load
  $(window).on('beforeunload', function() {
    $('#custom-loader').fadeIn('slow');
  });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get the current URL path
        const currentPath = window.location.pathname;

        // Remove the '/Privilage' part if it exists and any trailing slashes
        let updatedUrl = currentPath.replace('/Privilage/', '').replace(/\/$/, '');

        // Log the current path and updated URL for debugg

        // Clear existing active classes
        document.querySelectorAll('.nav-item').forEach(li => li.classList.remove('active'));

        // Get all navigation items
        const navItems = document.querySelectorAll('.nav-item a');

        // Loop through each navigation item
        navItems.forEach(item => {
            // Get the href attribute of the anchor
            const navHref = item.getAttribute('href');

            // Normalize the href for comparison
            let normalizedHref = navHref.replace(/^\.\.\//, '').replace(/\/$/, '').replace(/^\//, ''); // Remove leading '../' and trailing '/'

            // Log the normalized href and the updated URL for comparison

            // Check if the normalized href matches the updated URL
            if (normalizedHref === updatedUrl) {
                // Add active class to the parent li of the matched item
                item.parentElement.classList.add('active');
            }
        });
    });
</script>

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
            dom: 'Bfrtip', // Enable buttons
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>', // Excel Icon
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>', // PDF Icon
                    className: 'btn btn-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>', // Print Icon
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
        $("#multi-filter-select-planning").DataTable({
            pageLength: 12,
            dom: 'Bfrtip', // Enable buttons
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>', // Excel Icon
                    className: 'btn btn-success'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>', // PDF Icon
                    className: 'btn btn-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>', // Print Icon
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



<!-- <script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script> -->

<!--   Core JS Files   -->
<!-- <script src="../assets//js/core/popper.min.js"></script> -->
<script src="../assets//js/core/bootstrap.min.js"></script>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script> -->

<script>
  $(document).ready(function() {
    // Hide the loader once the content is loaded
    $('#custom-loader').fadeOut('slow');
  });

  // Show the loader on page load
  $(window).on('beforeunload', function() {
    $('#custom-loader').fadeIn('slow');
  });
</script>
</body>

</html>
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

<script>
  // Function to check card status and potentially open the modal
  function checkCardStatus() {
    $.ajax({
      url: '../actions/read_card.php', // PHP script to check if the card exists
      method: 'POST',
      success: function(response) {
        let data = JSON.parse(response);

        if (data.success) {
          let user = data.data;

          // Clear the table if necessary
          clearEnvoiAppTable();

          // Display user details using Bootstrap Notify
          $.notify({
            // options
            title: "<h3>Passage du tourniquet</h3>",
            message: `<a href="../Adherents/consult.php?id_user=${user.id}" style="text-decoration: none; color: inherit;">
                <div style="display: flex; align-items: center;">
                    <img src="../assets/img/capitalsoft/profils/${user.photo || 'default.jpg'}" 
                         alt="Photo de ${user.nom}" 
                         style="width: 100px; height: 100px; border-radius: 50%; margin-right: 10px;">
                    <div style="font-size: 16px;"> 
                        <strong>CIN:</strong> ${user.cin}<br>
                        <strong>CIN:</strong> ${user.cin}<br>
                        <strong>Matricule:</strong> ${user.matricule}<br>
                        <strong>Nom et Prénom</strong> ${user.nom} ${user.prenom}<br>
                        <strong>Wallet Privilège</strong> ${user.balance} MAD <br>
                        <strong>Type D'abonnement</strong> ${user.pack_name} MAD <br>
                        <strong>Date Debut D'abonnement:</strong> ${user.date_debut} <br>
                        <strong>Date Fin D'abonnement:</strong> ${user.date_fin}
                    </div>
                </div>
            </a>`
          }, {
            // settings
            type: user.etat === 'actif' ? 'success' : 'danger', // Green for "actif", red otherwise
            placement: {
              from: "top",
              align: "right"
            },
            time: 10000, // Duration to show the notification
            z_index: 1051, // Adjust z-index if needed
            // Add custom styling for larger green/red bar
            template: `<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert" style="min-height: 150px;">
                <button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>
                <span data-notify="icon"></span>
                <span data-notify="title">{1}</span>
                <span data-notify="message">{2}</span>
                <div class="progress" data-notify="progressbar">
                    <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                </div>
                <a href="{3}" target="{4}" data-notify="url"></a>
               </div>`
          });

        }
      },
      error: function(xhr, status, error) {
        console.error('Erreur lors de la requête:', error);
      }
    });
  }


  // Function to clear envoi_app table
  function clearEnvoiAppTable() {
    $.ajax({
      url: '../actions/clear_envoi_app.php', // PHP script to clear envoi_app table
      method: 'POST',
      success: function(response) {
        let data = JSON.parse(response);
        if (data.success) {
          console.log('envoi_app table cleared successfully.');
        } else {
          console.error('Failed to clear envoi_app table:', data.message);
        }
      },
      error: function(error) {
        console.error('Error clearing envoi_app table:', error);
      }
    });
  }

  // Check for card status every 1 second (1000ms)
  setInterval(checkCardStatus, 2000);
</script>


<script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<!--   Core JS Files   -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets//js/core/popper.min.js"></script>
<script src="../assets//js/core/bootstrap.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>



<!-- jQuery Scrollbar -->
<script src="../assets//js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

<!-- Chart JS -->
<script src="../assets//js/plugin/chart.js/chart.min.js"></script>

<!-- jQuery Sparkline -->
<script src="../assets//js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

<!-- Chart Circle -->
<script src="../assets//js/plugin/chart-circle/circles.min.js"></script>

<!-- Datatables -->
<script src="../assets//js/plugin/datatables/datatables.min.js"></script>

<!-- Bootstrap Notify -->
<script src="../assets//js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<!-- jQuery Vector Maps -->
<script src="../assets//js/plugin/jsvectormap/jsvectormap.min.js"></script>
<script src="../assets//js/plugin/jsvectormap/world.js"></script>



<script src="../src/plugins/jquery-steps/jquery.steps.js"></script>

<script src="../vendors/scripts/steps-setting.js"></script>

<!-- Sweet Alert -->
<script src="../assets//js/plugin/sweetalert/sweetalert.min.js"></script>

<!-- Kaiadmin JS -->
<script src="../assets//js/kaiadmin.min.js"></script>

<script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<!-- Datatables -->
<script src="../assets/js/plugin/datatables/datatables.min.js"></script>

<!-- Kaiadmin DEMO methods, don't include it in your project! -->
<script src="../assets//js/setting-demo.js"></script>
<script src="../assets//js/demo.js"></script>
<script>
  $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#177dff",
    fillColor: "rgba(23, 125, 255, 0.14)",
  });

  $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#f3545d",
    fillColor: "rgba(243, 84, 93, .14)",
  });

  $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#ffa534",
    fillColor: "rgba(255, 165, 52, .14)",
  });
</script>
<script>
  $(document).ready(function() {
    $("#basic-datatables").DataTable({});

    $("#multi-filter-select").DataTable({
      pageLength: 5,
      initComplete: function() {
        this.api()
          .columns()
          .every(function() {
            var column = this;
            var select = $(
                '<select class="form-select"><option value=""></option></select>'
              )
              .appendTo($(column.footer()).empty())
              .on("change", function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());

                column
                  .search(val ? "^" + val + "$" : "", true, false)
                  .draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function(d, j) {
                select.append(
                  '<option value="' + d + '">' + d + "</option>"
                );
              });
          });
      },
    });

    // Add Row
    $("#add-row").DataTable({
      pageLength: 5,
    });

    var action =
      '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

    $("#addRowButton").click(function() {
      $("#add-row")
        .dataTable()
        .fnAddData([
          $("#addName").val(),
          $("#addPosition").val(),
          $("#addOffice").val(),
          action,
        ]);
      $("#addRowModal").modal("hide");
    });
  });
</script>
</body>

</html>
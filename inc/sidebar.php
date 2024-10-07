<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="index.html" class="logo">
        <img src="../assets/img/capitalsoft/logo_light.png" alt="navbar brand" class="navbar-brand" height="120" />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="fas fa-bars"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="fas fa-ellipsis-v"></i>
      </button>
    </div>
    <!-- End Logo Header -->
  </div>

  <?php
  // Secure session and DB query
  require "../inc/conn_db.php";
  $session_profil = intval($_SESSION['profil']); // Ensure it's an integer to avoid injection risks

  // Prepare the SQL query
  $stmt = $conn->prepare("SELECT nom_page FROM habilitation WHERE id_role = ? and statut_actif='actif'");
  $stmt->bind_param("i", $session_profil);
  $stmt->execute();
  $result = $stmt->get_result();

  $menu_permissions = [];
  while ($row = $result->fetch_assoc()) {
    $menu_permissions[] = $row['nom_page'];
  }

  // Function to check access
  function hasAccess($menu_item_id, $permissions)
  {
    return in_array($menu_item_id, $permissions);
  }
  ?>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">

        <!-- Dashboard (menu item id: 1) -->
        <?php if (hasAccess('Dashboard', $menu_permissions)) { ?>
          <li class="nav-item">
            <a href="../Dashboards/">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
        <?php } ?>

        <!-- Adhérent (menu item id: 1) -->
        <?php if (hasAccess('Liste des adherents', $menu_permissions)) { ?>
          <li class="nav-item">
            <a href="../adherents/">
              <i class="fas fa-users"></i>
              <p>Liste des adherents</p>
            </a>
          </li>
        <?php } ?>

        <!-- Planning (menu item id: 2) -->
        <?php if (hasAccess('Planning', $menu_permissions)) { ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts1">
              <i class="fas fa-th-list"></i>
              <p>Planning</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts1">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../planning/">
                    <span class="sub-item">Planning par activité</span>
                  </a>
                </li>
                <li>
                  <a href="../reservations/">
                    <span class="sub-item">Réservation</span>
                  </a>
                </li>
                <li>
                  <a href="../Inaccessible.html">
                    <span class="sub-item">Gestion d'Équipe</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>

        <!-- Restauration (menu item id: 3) -->
        <?php if (hasAccess('Restauration', $menu_permissions)) { ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts2" aria-controls="sidebarLayouts2">
              <i class="fas fa-utensils"></i>
              <p>Restauration</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts2">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../Inaccessible.html">
                    <span class="sub-item">Menu du jour</span>
                  </a>
                </li>
                <li>
                  <a href="../Inaccessible.html">
                    <span class="sub-item">Réservation de table</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>

        <!-- Services Spa et Massage (menu item id: 4) -->
        <?php if (hasAccess('Services Spa et Massage', $menu_permissions)) { ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts3">
              <i class="fas fa-spa"></i>
              <p>Services Spa et Massage</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts3">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../Inaccessible.html">
                    <span class="sub-item">Liste des services</span>
                  </a>
                </li>
                <li>
                  <a href="../Inaccessible.html">
                    <span class="sub-item">Réservation de services</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>

        <!-- Wallet (menu item id: 5) -->
        <?php if (hasAccess('Wallet', $menu_permissions)) { ?>
          <li class="nav-item">
            <a href="../wallet/">
              <i class="fas fa-wallet"></i>
              <p>Wallet Privilège</p>
            </a>
          </li>
        <?php } ?>
        <!-- Wallet (menu item id: 5) -->
        <?php if (hasAccess('Cheques', $menu_permissions)) { ?>
          <li class="nav-item">
            <a href="../cheques/">
              <i class="fas fa-money-check-alt"></i> <!-- Changement d'icône -->
              <p>Chèques</p> <!-- Correction orthographique : Chèques -->
            </a>
          </li>

        <?php } ?>

        <!-- Paramétrages (menu item id: 6) -->
        <?php if (hasAccess('Paramétrages', $menu_permissions)) { ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts4" aria-controls="sidebarLayouts4">
              <i class="fas fa-cogs"></i>
              <p>Paramétrages</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts4">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../administration/coaches.php">
                    <span class="sub-item">Coaches</span>
                  </a>
                </li>
                <li>
                  <a href="../administration/local.php">
                    <span class="sub-item">Local</span>
                  </a>
                </li>
                <li>
                  <a href="../administration/materiels.php">
                    <span class="sub-item">Matériels</span>
                  </a>
                </li>
                <li>
                  <a href="../administration/">
                    <span class="sub-item">Différente activité proposé par club</span>
                  </a>
                </li>
                <li>
                  <a href="../habilitations/">
                    <span class="sub-item">Habilitations</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>

        <!-- Gestion des rapports (menu item id: 7) -->
        <?php if (hasAccess('Gestion des rapports', $menu_permissions)) { ?>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts5rapport" aria-controls="sidebarLayouts5rapport">
              <i class="fas fa-file-alt"></i>
              <p>Gestion des rapports</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts5rapport">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../reporting/cheques.php">
                    <span class="sub-item">Rapport des chèques</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/prospects.php">
                    <span class="sub-item">Liste des prospects</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/adherents.php">
                    <span class="sub-item">Liste des adhérents</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/abonnement.php">
                    <span class="sub-item">Abonnement</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/chiffre_affaire.php">
                    <span class="sub-item">Chiffre d'affaire</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/planning_coaches.php">
                    <span class="sub-item">Planning des coaches</span>
                  </a>
                </li>
                <li>
                  <a href="../reporting/suivi_paiement.php">
                    <span class="sub-item">Suivi Paiement Abonnement</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>

      </ul>
    </div>
  </div>
</div>
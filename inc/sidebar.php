<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="index.html" class="logo">
        <img src="../assets/img/capitalsoft/logo_light.png" alt="navbar brand" class="navbar-brand" height="90" />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="fas fa-bars"></i> <!-- Updated icon -->
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="fas fa-bars"></i> <!-- Updated icon -->
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="fas fa-ellipsis-v"></i> <!-- Updated icon -->
      </button>
    </div>
    <!-- End Logo Header -->
  </div>
  <?php
  $session_profil = $_SESSION['profil'];
  if ($session_profil == 3) {; ?>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item active">
            <a href="../Dashboards">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts1">
              <i class="fas fa-th-list"></i>
              <p>Planning</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts1">
              <ul class="nav nav-collapse">

                <li>
                  <a href="../reservations/">
                    <span class="sub-item">Réservation</span>
                  </a>
                </li>

              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts2">
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

        </ul>
      </div>
    </div>
  <?php
  } elseif ($session_profil == 4) {; ?>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item active">
            <a href="../Dashboards">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../adherents/">
              <i class="fas fa-users"></i>
              <p>Liste des adherents</p>
            </a>
          </li>
        </ul>
      </div>
    </div>
  <?php
  } elseif ($session_profil == 5) {; ?>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item active">
            <a href="../Dashboards">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../adherents/">
              <i class="fas fa-users"></i>
              <p>Liste des adherents</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../wallet/">
              <i class="fas fa-users"></i>
              <p>Wallet</p>
            </a>
          </li>

        </ul>
      </div>
    </div>
  <?php
  } else {; ?>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item active">
            <a href="../Dashboards">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../adherents/">
              <i class="fas fa-users"></i>
              <p>Liste des adherents</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../wallet/">
              <i class="fas fa-users"></i>
              <p>Wallet</p>
            </a>
          </li>
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
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts2">
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
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts4">
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
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts5">
              <i class="fas fa-futbol"></i>
              <p>Activités Sportives</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts5">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../administration/">
                    <span class="sub-item">Différente activité proposé par club</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#sidebarLayouts5rapport">
              <!-- Changed icon class here -->
              <i class="fas fa-file-alt"></i>
              <p>Gestion des rapports</p>
              <span class="caret"></span>
            </a>
            <div class="collapse" id="sidebarLayouts5rapport">
              <ul class="nav nav-collapse">
                <li>
                  <a href="../reporting/">
                    <span class="sub-item">Rapport des chèques</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>

        </ul>
      </div>
    </div>
  <?php

  }; ?>

</div>
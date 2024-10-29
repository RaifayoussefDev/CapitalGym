<?php
require "../inc/app.php";
require "../inc/conn_db.php";
$profil = $_SESSION['profil'];
$id_user = $_SESSION['id'];

?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <h3 class="fw-bold mb-3">Adhérents</h3>
    </div>
</div>



<?php
require "../inc/footer.php";
?>
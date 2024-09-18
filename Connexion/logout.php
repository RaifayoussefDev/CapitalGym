<?php
session_start();
session_destroy();
header('Location: ../connexion'); // Adjust the path to your login page as needed
exit();
?>

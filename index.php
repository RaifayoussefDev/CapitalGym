<?php
session_start();
if(isset($_SESSION['email'])){
    header('location:Dashboards');
}else{
    header('location:connexion');
}

<?php
session_start();

if(!isset($_SESSION['email'])){
    header('location:../connexion');
}

;?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Privilége - Application Gestion Club Sportifs</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="../assets//img/capitalsoft/logo_light.png"
      type="image/x-icon"
    />

    <link
			rel="stylesheet"
			type="text/css"
			href="../src/plugins/jquery-steps/jquery.steps.css"
		/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Fonts and icons -->
    <script src="../assets//js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["../assets//css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- Load jQuery Validate and Steps -->

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets//css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets//css/plugins.min.css" />
    <link rel="stylesheet" href="../assets//css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="../assets//css/demo.css" />
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <?php
      require "sidebar.php"
      ;?>
      <!-- End Sidebar -->

      <div class="main-panel">

    <?php
    require "header.php"
    ;?>
        <div class="container">

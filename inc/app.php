<?php
session_start();

if (!isset($_SESSION['email'])) {
  header('location:../connexion');
}; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Privilége - Application Gestion Club Sportifs</title>
  <meta
    content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
    name="viewport" />
  <link
    rel="icon"
    href="../assets//img/capitalsoft/logo_light.png"
    type="image/x-icon" />

  <link
    rel="stylesheet"
    type="text/css"
    href="../src/plugins/jquery-steps/jquery.steps.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Fonts and icons -->
  <script src="../assets//js/plugin/webfont/webfont.min.js"></script>
  <script>
    WebFont.load({
      google: {
        families: ["Public Sans:300,400,500,600,700"]
      },
      custom: {
        families: [
          "Font Awesome 5 Solid",
          "Font Awesome 5 Regular",
          "Font Awesome 5 Brands",
          "simple-line-icons",
        ],
        urls: ["../assets//css/fonts.min.css"],
      },
      active: function() {
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

  <style>
    .loader-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(34, 34, 34, 9);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .loading {
      display: flex;
    }

    .loading .dot {
      position: relative;
      width: 2em;
      height: 2em;
      margin: 0.8em;
      border-radius: 50%;
      background: #ffd700;
      /* Gold color */
      box-shadow: 0 0 0.5em #ffd700, 0 0 1em #ffd700;
      animation: bounce 1.5s infinite;
    }

    .loading .dot:nth-child(1) {
      animation-delay: 0s;
    }

    .loading .dot:nth-child(2) {
      animation-delay: 0.3s;
    }

    .loading .dot:nth-child(3) {
      animation-delay: 0.6s;
    }

    .loading .dot:nth-child(4) {
      animation-delay: 0.9s;
    }

    .loading .dot:nth-child(5) {
      animation-delay: 1.2s;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-1em);
        /* Adjust height for bounce */
      }
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <!-- <div class="loader-wrapper" id="custom-loader">
    <div class="loading">
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
    </div>
  </div> -->



  <div class="wrapper">
    <!-- Sidebar -->
    <?php
    require "sidebar.php"; ?>
    <!-- End Sidebar -->

    <div class="main-panel">

      <?php
      require "header.php"; ?>
      <div class="container">
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php'; // Ensure the path is correct

// Function to encode image to Base64
function base64_encode_image($file)
{
    $imageData = file_get_contents($file);
    $base64 = base64_encode($imageData);
    return 'data:image/png;base64,' . $base64;
}

function sendEmail($to, $subject, $message)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'demonstrationcs@gmail.com';
        $mail->Password   = 'gqdboeuayppnpqgm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('demonstrationcs@gmail.com', 'Privilege Luxury Fitness Club');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Encode logo image in Base64
        $logoPath = 'logo_light.png'; // Ensure this path is correct and accessible
        $logo = base64_encode_image($logoPath);

        // HTML email content
        $mail->Body = '
        <!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Privilège Email</title>
    <style media="all" type="text/css">
    /* GLOBAL RESETS */
    
    body {
      font-family: Helvetica, sans-serif;
      -webkit-font-smoothing: antialiased;
      font-size: 16px;
      line-height: 1.3;
      background-color: #1c1c1c;
      color: #f5f5f5;
      margin: 0;
      padding: 0;
    }
    p {
  font-size: 16px;
  margin: 0;
  margin-bottom: 16px;
  color: #f5f5f5; /* Set this to white or gold for better contrast */
}
    table {
      border-collapse: separate;
      width: 100%;
    }
    
    table td {
      font-size: 16px;
      vertical-align: top;
    }

    .container {
      margin: 0 auto !important;
      max-width: 600px;
      padding-top: 24px;
      width: 600px;
    }
    
    .content {
      display: block;
      margin: 0 auto;
      max-width: 600px;
      padding: 0;
    }
    
    .main {
      background: #000000;
      border: 1px solid #d4af37;
      border-radius: 16px;
      width: 100%;
    }
    
    .wrapper {
      padding: 24px;
      box-sizing: border-box;
    }
    
    .footer {
      clear: both;
      padding-top: 24px;
      text-align: center;
      width: 100%;
      color: #d4af37;
    }

    .footer td,
    .footer p,
    .footer a {
      color: #d4af37;
      font-size: 16px;
      text-align: center;
    }

    p {
      font-size: 16px;
      margin: 0;
      margin-bottom: 16px;
    }
    
    a {
      color: #d4af37;
      text-decoration: underline;
    }

    .btn {
      width: 100%;
    }
    
    .btn table {
      width: auto;
    }
    
    .btn table td {
      background-color: #d4af37;
      border-radius: 4px;
      text-align: center;
    }
    
    .btn a {
      color: #000;
      background-color: #d4af37;
      border: solid 2px #d4af37;
      font-size: 16px;
      font-weight: bold;
      padding: 12px 24px;
      text-decoration: none;
      display: inline-block;
      border-radius: 4px;
    }

    @media only screen and (max-width: 640px) {
      .main p,
      .main td,
      .main span {
        font-size: 16px !important;
      }
      .wrapper {
        padding: 8px !important;
      }
      .container {
        padding: 0 !important;
        padding-top: 8px !important;
        width: 100% !important;
      }
      .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
      .btn a {
        font-size: 16px !important;
        width: 100% !important;
      }
    }
    </style>
  </head>
  <body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">

            <span class="preheader">Privilege Luxury Fitness Club</span>
            ' . $message . '
            <div class="footer">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                    <td>
                        <span style="color: #d4af37;">Privilège,711 Bd Modibo Keita, Casablanca</span>
                    </td>
                    </tr>
                    <tr>
                    <td class="powered-by">
                        Propulsé par <a href="http://privilegeclub.com" style="color: #d4af37;">Privilège</a>
                    </td>
                    </tr>
                    <tr>
                    <td style="padding-top: 10px;">
                        <!-- Icônes des réseaux sociaux -->
                        <a href="https://facebook.com" target="_blank">
                        <img src="https://img.icons8.com/ios-filled/50/d4af37/facebook-new.png" alt="Facebook" width="24" style="margin-right: 10px;">
                        </a>
                        <a href="https://twitter.com" target="_blank">
                        <img src="https://img.icons8.com/ios-filled/50/d4af37/twitter.png" alt="Twitter" width="24" style="margin-right: 10px;">
                        </a>
                        <a href="https://instagram.com" target="_blank">
                        <img src="https://img.icons8.com/ios-filled/50/d4af37/instagram-new.png" alt="Instagram" width="24">
                        </a>
                    </td>
                    </tr>
                </table>
            </div>
          </div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>

</html>
';

        // Send the email
        $mail->send();
        return "Email sent successfully to $to";
    } catch (Exception $e) {
        return "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
    }
};

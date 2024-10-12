<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php'; // Make sure the path is correct

function sendEmail($to, $subject, $message, $logoPath) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                        // Set mailer to use SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = 'demonstrationcs@gmail.com';      // Your Gmail address
        $mail->Password   = 'gqdboeuayppnpqgm';               // Your Gmail password (App Password if 2FA is enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = 587;                              // TCP port to connect to

        // Recipients
        $mail->setFrom('demonstrationcs@gmail.com', 'Your Name'); // Change to your name
        $mail->addAddress($to);                               // Add a recipient

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;

        // HTML email content
        $mail->Body    = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .logo { width: 100px; height: auto; }
                .content { text-align: center; }
            </style>
        </head>
        <body>
            <div class="content">
                <img src="' . htmlspecialchars($logoPath) . '" alt="Logo" class="logo"/>
                <h2>' . htmlspecialchars($subject) . '</h2>
                <p>' . nl2br(htmlspecialchars($message)) . '</p>
            </div>
        </body>
        </html>';

        // Send the email
        $mail->send();
        return "Email sent successfully to $to";
    } catch (Exception $e) {
        return "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Example usage
$to = "recipient@example.com"; // Change to the recipient's email
$subject = "Your Subject Here";
$message = "This is the message body. You can change this text as needed.";
$logoPath = "path/to/your/logo.png"; // Change to the logo path

echo sendEmail($to, $subject, $message, $logoPath);
?>

<?php
include 'database.php'; // Your PDO connection

function sendEmail($to, $subject, $messageBody) {
    global $pdo;

    // Fetch site settings
    $stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
    $site = $stmt->fetch(PDO::FETCH_ASSOC);

    $site_name = $site['site_name'] ?? 'Investment Platform';
    $site_logo = $site['site_logo'] ?? 'default-logo.png';

    // Build the sender email using the current domain
    $host = $_SERVER['HTTP_HOST'] ?? 'example.com';
    $from = "noreply@$host";

    // Email headers
    $headers = "From: $site_name <$from>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Construct email message with logo and branding
    $logo_url = "https://$host/$site_logo";
    $fullMessage = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .email-header {
                    text-align: center;
                    padding: 20px;
                }
                .email-body {
                    padding: 20px;
                    background-color: #f9f9f9;
                }
            </style>
        </head>
        <body>
            <div class='email-header'>
                <img src='$logo_url' alt='$site_name Logo' style='max-height: 80px;'>
                <h2>$site_name</h2>
            </div>
            <div class='email-body'>
                $messageBody
            </div>
        </body>
        </html>
    ";

    // Send the email
    mail($to, $subject, $fullMessage, $headers);
}

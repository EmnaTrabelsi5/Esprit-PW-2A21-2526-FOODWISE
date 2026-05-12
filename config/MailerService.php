<?php
declare(strict_types=1);

class MailerService
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;

    public function __construct(
        string $smtpHost = 'smtp.gmail.com',
        int $smtpPort = 587,
        string $smtpUsername = '',
        string $smtpPassword = '',
        string $fromEmail = '',
        string $fromName = 'FoodWise'
    ) {
        $this->smtpHost = $smtpHost;
        $this->smtpPort = $smtpPort;
        $this->smtpUsername = $smtpUsername ?: getenv('MAIL_USERNAME') ?: '';
        $this->smtpPassword = $smtpPassword ?: getenv('MAIL_PASSWORD') ?: '';
        $this->fromEmail = $fromEmail ?: $this->smtpUsername;
        $this->fromName = $fromName;
    }

    /**
     * Envoyer un email avec code de réinitialisation
     */
    public function sendResetCodeEmail(string $toEmail, string $toName, string $resetCode): bool
    {
        if (empty($this->smtpUsername) || empty($this->smtpPassword)) {
            error_log('SMTP credentials not configured');
            return false;
        }

        $subject = 'Réinitialiser votre mot de passe FoodWise';
        
        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .code-box { background: #f0f0f0; border: 2px solid #667eea; padding: 20px; text-align: center; border-radius: 4px; margin: 20px 0; }
        .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 2px; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Réinitialisation de mot de passe</h2>
        </div>
        <p>Bonjour {$toName},</p>
        <p>Vous avez demandé une réinitialisation de mot de passe pour votre compte FoodWise. Voici votre code de réinitialisation :</p>
        <div class="code-box">
            <div class="code">{$resetCode}</div>
        </div>
        <p>Ce code est valide pendant 30 minutes. Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
        <p>Cordialement,<br>L'équipe FoodWise</p>
        <div class="footer">
            <p>Cet email a été envoyé à {$toEmail}</p>
        </div>
    </div>
</body>
</html>
HTML;

        $textBody = "Bonjour {$toName},\n\n" .
                   "Vous avez demandé une réinitialisation de mot de passe. Voici votre code : {$resetCode}\n\n" .
                   "Ce code est valide pendant 30 minutes.\n\n" .
                   "Cordialement,\nL'équipe FoodWise";

        return $this->send($toEmail, $toName, $subject, $htmlBody, $textBody);
    }

    /**
     * Envoyer un email (méthode privée)
     */
    private function send(string $to, string $toName, string $subject, string $htmlBody, string $textBody): bool
    {
        try {
            error_log("SMTP: Tentative de connexion à {$this->smtpHost}:{$this->smtpPort}");
            
            // Créer une connexion socket
            $fp = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
            if (!$fp) {
                error_log("SMTP connection failed: {$errstr} ({$errno})");
                return false;
            }

            error_log("SMTP: Connecté au serveur");

            // Lire la réponse du serveur
            $response = fgets($fp, 1024);
            error_log("SMTP Response 1: " . trim($response));
            
            if (strpos($response, '220') === false) {
                fclose($fp);
                error_log("SMTP: Réponse initiale invalide");
                return false;
            }

            // EHLO
            $this->sendCommand($fp, "EHLO localhost\r\n");
            $response = $this->readResponse($fp);
            error_log("SMTP EHLO Response: " . substr($response, 0, 100));

            // STARTTLS
            $this->sendCommand($fp, "STARTTLS\r\n");
            $response = $this->readResponse($fp);
            error_log("SMTP STARTTLS Response: " . substr($response, 0, 100));
            
            if (strpos($response, '220') === false) {
                fclose($fp);
                error_log("SMTP: STARTTLS failed");
                return false;
            }

            // Activer le chiffrement TLS
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                error_log("SMTP: Failed to enable TLS encryption");
                return false;
            }

            error_log("SMTP: TLS activé");

            // Réenvoyer EHLO après STARTTLS
            $this->sendCommand($fp, "EHLO localhost\r\n");
            $this->readResponse($fp);

            // Authentification
            $this->sendCommand($fp, "AUTH LOGIN\r\n");
            $this->readResponse($fp);
            
            error_log("SMTP: Envoi du username");
            $this->sendCommand($fp, base64_encode($this->smtpUsername) . "\r\n");
            $this->readResponse($fp);
            
            error_log("SMTP: Envoi du password");
            $this->sendCommand($fp, base64_encode($this->smtpPassword) . "\r\n");
            $response = $this->readResponse($fp);
            
            if (strpos($response, '235') === false) {
                fclose($fp);
                error_log("SMTP authentication failed. Response: " . trim($response));
                return false;
            }

            error_log("SMTP: Authentification réussie");

            // MAIL FROM
            $this->sendCommand($fp, "MAIL FROM:<{$this->fromEmail}>\r\n");
            $this->readResponse($fp);

            // RCPT TO
            $this->sendCommand($fp, "RCPT TO:<{$to}>\r\n");
            $this->readResponse($fp);

            // DATA
            $this->sendCommand($fp, "DATA\r\n");
            $this->readResponse($fp);

            // Construire le message
            $boundary = md5((string)time());
            $message = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $message .= "To: {$toName} <{$to}>\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $textBody . "\r\n\r\n";
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $htmlBody . "\r\n\r\n";
            $message .= "--{$boundary}--\r\n";

            fputs($fp, $message . "\r\n.\r\n");
            $response = $this->readResponse($fp);
            
            error_log("SMTP: Message envoyé. Response: " . trim($response));

            // QUIT
            $this->sendCommand($fp, "QUIT\r\n");
            fclose($fp);

            error_log("SMTP: Email envoyé avec succès à {$to}");
            return strpos($response, '250') !== false;
            
        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendCommand($fp, string $command): void
    {
        fputs($fp, $command);
    }

    private function readResponse($fp): string
    {
        $response = '';
        while ($line = fgets($fp, 1024)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}


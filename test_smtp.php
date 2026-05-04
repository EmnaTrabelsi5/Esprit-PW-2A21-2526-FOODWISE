<?php
// Fichier de test SMTP - À exécuter: http://localhost/mod%202%20web/test_smtp.php

require __DIR__ . '/app/bootstrap.php';

echo "<h2>Test de connexion SMTP Gmail</h2>";
echo "<pre>";

$email = getenv('MAIL_USERNAME');
$password = getenv('MAIL_PASSWORD');

echo "Email: $email\n";
echo "Password: " . (empty($password) ? "VIDE" : str_repeat('*', strlen($password))) . "\n\n";

if (empty($email) || empty($password)) {
    echo "❌ Erreur: Email ou mot de passe vide dans .env\n";
    exit;
}

// Test de connexion simple
echo "Tentative de connexion à smtp.gmail.com:587...\n";
$fp = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);

if (!$fp) {
    echo "❌ Impossible de se connecter: $errstr ($errno)\n";
    exit;
}

echo "✅ Connexion établie\n\n";

// Tester l'envoi
$mailer = new MailerService();
$result = $mailer->sendResetCodeEmail('test@example.com', 'Test User', '123456');

if ($result) {
    echo "✅ Email envoyé avec succès!\n";
} else {
    echo "❌ Erreur lors de l'envoi.\n";
    echo "Vérifiez les logs XAMPP: " . (PHP_OS_FAMILY === 'Windows' ? 'C:\\xampp\\apache\\logs\\error.log' : '/var/log/apache2/error.log') . "\n";
}

echo "\n</pre>";

// Afficher les derniers logs d'erreur
echo "<h3>Logs récents (check console PHP/error.log)</h3>";
echo "<p>Consultez la console du navigateur (F12) ou les logs XAMPP pour plus de détails.</p>";
?>

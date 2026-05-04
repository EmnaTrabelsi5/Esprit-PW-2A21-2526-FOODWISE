# Configuration de la Réinitialisation de Mot de Passe - SMTP Gmail

## Vue d'ensemble
Le système de réinitialisation de mot de passe FoodWise envoie des codes de réinitialisation par email via SMTP Gmail. L'utilisateur reçoit un code à 6 chiffres valide pendant 30 minutes.

## Fonctionnement

### Étape 1 : Demande de réinitialisation
1. L'utilisateur accède à la page "Mot de passe oublié"
2. Il saisit son adresse email
3. Un code aléatoire à 6 chiffres est généré et sauvegardé en base de données
4. Un email est envoyé avec le code

### Étape 2 : Vérification du code
1. L'utilisateur reçoit l'email avec son code
2. Il accède à la page "Vérifier le code"
3. Il saisit son email, le code et son nouveau mot de passe
4. Le système vérifie la validité du code (6 chiffres, expiration < 30 min)
5. Si valide, le mot de passe est changé et l'utilisateur peut se connecter

## Configuration SMTP Gmail

### Étape 1 : Créer une "App password"
Gmail ne permet pas d'envoyer des emails via SMTP avec le mot de passe standard pour des raisons de sécurité.

1. Accédez à votre compte Google : https://myaccount.google.com
2. Allez dans "Sécurité" (Security)
3. Activez la "Vérification en 2 étapes" (2-Step Verification) si ce n'est pas fait
4. Allez dans "Mots de passe d'application" (App passwords)
5. Sélectionnez "Mail" et "Windows" (ou votre système d'exploitation)
6. Générez un mot de passe à 16 caractères
7. Copiez ce mot de passe

### Étape 2 : Configurer le fichier .env
1. Copiez le fichier `.env.example` en `.env`
   ```bash
   cp .env.example .env
   ```

2. Remplissez les variables SMTP :
   ```
   MAIL_USERNAME=votre-email@gmail.com
   MAIL_PASSWORD=xxxx xxxx xxxx xxxx  (le mot de passe généré, sans espaces)
   MAIL_FROM_EMAIL=votre-email@gmail.com
   MAIL_FROM_NAME=FoodWise
   ```

### Configuration avancée
Si vous utilisez un autre fournisseur SMTP (Office 365, SendGrid, etc.), vous pouvez modifier la classe `MailerService.php`:

```php
$mailer = new MailerService(
    'smtp.autre-serveur.com',  // SMTP Host
    587,                         // SMTP Port (habituellement 587 pour TLS)
    'votre-email@exemple.com',   // Nom d'utilisateur SMTP
    'votre-motdepasse',          // Mot de passe SMTP
    'votre-email@exemple.com',   // Email FROM
    'Votre Nom'                  // Nom FROM
);
```

## Base de Données

### Modifications apportées
Trois colonnes ont été ajoutées à la table `utilisateurs`:
- `photo_profil` VARCHAR(500) - Chemin vers la photo de profil
- `reset_code` VARCHAR(10) - Code de réinitialisation à 6 chiffres
- `reset_code_expires_at` DATETIME - Heure d'expiration du code

### Migration
Si vous avez une base de données existante, exécutez le script de migration:
```sql
-- database/migrations/001_add_password_reset_columns.sql
ALTER TABLE utilisateurs ADD COLUMN photo_profil VARCHAR(500) DEFAULT NULL;
ALTER TABLE utilisateurs ADD COLUMN reset_code VARCHAR(10) DEFAULT NULL;
ALTER TABLE utilisateurs ADD COLUMN reset_code_expires_at DATETIME DEFAULT NULL;
```

## Routes

### Routes frontend
- `/index.php?route=module2.front.password_reset` - Page pour demander le code
- `/index.php?route=module2.front.verify_reset_code` - Page pour vérifier le code et changer le mot de passe

### Vues associées
- `app/views/module2/front/mot_de_passe_oublie.php` - Formulaire de demande
- `app/views/module2/front/verify_reset_code.php` - Formulaire de vérification

## Règles de validation

### Code de réinitialisation
- Format : 6 chiffres (ex: 123456)
- Validité : 30 minutes
- Génération : Aléatoire

### Nouveau mot de passe
- Longueur minimale : 8 caractères
- Doit contenir au moins une majuscule
- Doit contenir au moins un chiffre

## Sécurité

- Les codes ne sont visibles que dans les emails chiffrés
- Les codes expirent après 30 minutes
- Les codes ne sont stockés que temporairement en base de données
- Après changement du mot de passe, le code est supprimé
- Les emails d'erreur affichent le même message si l'email existe ou non (prévention d'énumération)

## Test local

Pour tester sans configurer Gmail:

1. Vous pouvez temporairement modifier `MailerService.php` pour enregistrer les codes en fichier au lieu d'envoyer des emails
2. Ou utiliser un service comme Mailtrap pour capturer les emails en développement

## Dépannage

### "SMTP connection failed"
- Vérifiez que vous êtes connecté à Internet
- Vérifiez que les ports 587 ou 465 ne sont pas bloqués par le pare-feu
- Testez avec Telnet: `telnet smtp.gmail.com 587`

### "SMTP authentication failed"
- Vérifiez que l'email et le mot de passe sont corrects dans .env
- Assurez-vous d'utiliser une "App password" et non votre mot de passe standard
- Vérifiez que la vérification en 2 étapes est activée

### "No such file or directory" pour le fichier .env
- Créez le fichier .env à la racine du projet (même niveau que index.php)
- Copiez le contenu de .env.example

## Fichiers impliqués

- `app/MailerService.php` - Classe pour envoyer les emails via SMTP
- `app/Models/UtilisateurModel.php` - Méthodes pour gérer les codes de réinitialisation
- `app/Controllers/FrontController.php` - Actions passwordReset() et verifyResetCode()
- `app/bootstrap.php` - Chargement du fichier .env
- `app/views/module2/front/mot_de_passe_oublie.php` - Vue de demande
- `app/views/module2/front/verify_reset_code.php` - Vue de vérification
- `database/schema.sql` - Schéma mis à jour
- `database/migrations/001_add_password_reset_columns.sql` - Script de migration
- `.env` - Configuration (à remplir)
- `.env.example` - Exemple de configuration

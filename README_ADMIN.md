# 🔐 GUIDE : CRÉATION DE COMPTE ADMIN

## **URL D'ACCÈS AU BACK-OFFICE**

Clique sur ce lien pour accéder à la page de connexion admin :
```
?route=module2.back.login
```

Ou directement :
```
index.php?route=module2.back.login
```

---

## **CRÉER UN COMPTE ADMIN VIA LA BASE DE DONNÉES**

### **Option 1 : Via phpMyAdmin ou MySQL Workbench**

Exécute cette requête SQL dans ton gestionnaire de base de données :

```sql
-- Créer un admin avec email: admin@foodwise.com et mot de passe: Admin123!
INSERT INTO utilisateurs (nom, prenom, email, password_hash, role) 
VALUES (
  'Admin',
  'FoodWise',
  'admin@foodwise.com',
  '$2y$10$E7qPJwMBb1EkT7v1ek2hkeX7FWxd.FfXqLHKMBwzJcfH5F5mxZKWa',
  'admin'
);
```

### **Option 2 : Créer ton propre compte admin**

1. Va sur cette page pour générer un mot de passe hashé : https://www.php.net/manual/en/function.password-hash.php

2. Ou utilise PHP directement. Crée un fichier `generate-password.php` :

```php
<?php
$password = 'TonMotDePasse123!'; // Remplace par ton mot de passe
$hash = password_hash($password, PASSWORD_DEFAULT);
echo 'Hash: ' . $hash;
?>
```

3. Exécute ce script et tu recevras un hash. Utilise-le dans cette requête :

```sql
INSERT INTO utilisateurs (nom, prenom, email, password_hash, role) 
VALUES (
  'Ton Nom',
  'Ton Prénom',
  'votreemail@example.com',
  'PASTE_LE_HASH_ICI',
  'admin'
);
```

---

## **COMPTE ADMIN PAR DÉFAUT (POUR TESTER)**

| Élément | Valeur |
|---------|--------|
| **Email** | admin@foodwise.com |
| **Mot de passe** | Admin123! |
| **URL login** | ?route=module2.back.login |

---

## **ACCORDER/RETIRER LES DROITS ADMIN**

### **Faire passer un utilisateur en admin :**
```sql
UPDATE utilisateurs SET role = 'admin' WHERE email = 'user@example.com';
```

### **Retirer les droits admin :**
```sql
UPDATE utilisateurs SET role = 'user' WHERE email = 'admin@example.com';
```

---

## **CE QUE PEUT FAIRE L'ADMIN**

✅ Voir la liste de tous les utilisateurs et leurs profils  
✅ Chercher des utilisateurs par : nom, email, objectif, allergies, régimes, intolérances  
✅ Créer un nouvel utilisateur  
✅ Modifier le profil d'un utilisateur  
✅ Supprimer un utilisateur  
✅ Voir les statistiques globales (score moyen, profils complets, etc.)  

---

## **PROBLÈMES COURANTS**

**❌ "Identifiants incorrects"**
- Vérife que tu as bien saisi l'email et le mot de passe
- Assure-toi que l'utilisateur a le rôle `admin` dans la base de données

**❌ "Page non trouvée"**
- La route n'a pas été ajoutée à `index.php`
- Vérife que le fichier `bootstrap.php` inclut les contrôleurs

**❌ Mot de passe oublié**
- Tu dois créer manuellement un nouveau hash et mettre à jour la base de données
- Utilise : `password_hash('nouveau_mdp', PASSWORD_DEFAULT)`

---

Besoin d'aide ? Demande-moi ! 😊

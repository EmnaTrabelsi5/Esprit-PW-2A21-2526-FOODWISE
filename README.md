# FoodWise

> **Manger mieux. Gaspiller moins. Vivre local.**

---

##  Description du Projet

**FoodWise** est une plateforme web intelligente qui aide les utilisateurs à faire des choix alimentaires adaptés à leurs besoins, leurs préférences et les ressources disponibles.

###  Objectif

FoodWise répond à deux besoins concrets du quotidien :

- **Faciliter la décision alimentaire** : chaque utilisateur obtient des recettes personnalisées selon son profil nutritionnel (objectifs, goûts, restrictions alimentaires).
- **Lutter contre le gaspillage alimentaire** : les commerçants locaux (restaurants, boulangeries, supermarchés) publient leurs invendus du jour à prix réduit, directement accessibles depuis chaque recette.


##  Table des Matières

- [Description du Projet](#-description-du-projet)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [Structure du Projet](#-structure-du-projet)
- [Technologies Utilisées](#-technologies-utilisées)
- [Équipe](#-équipe)
- [Licence](#-licence)

---

##  Installation

### Prérequis

Avant de commencer, assurez-vous d'avoir installé sur votre machine :

* [PHP](https://www.php.net/) >= 8.0
* [MySQL](https://www.mysql.com/) ou [MariaDB](https://mariadb.org/)
* [Composer](https://getcomposer.org/)
* Un serveur local : [XAMPP](https://www.apachefriends.org/) ou [Laragon](https://laragon.org/)
* [Git](https://git-scm.com/)

### Étapes d'installation

1. **Clonez le repository**

```bash
git clone https://github.com/EmnaTrabelsi5/Esprit-PW-2A21-2526-FOODWISE.git
cd Esprit-PW-2A21-2526-FOODWISE
```

1. **Lancez le serveur local**

* Placez le projet dans le dossier `htdocs` de XAMPP (ou équivalent)
* Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP
* Accédez à l'application via : [http://localhost/Esprit-PW-2A21-2526-FOODWISE](http://localhost/Esprit-PW-2A21-2526-FOODWISE)

---

##  Utilisation

### Technologies principales utilisées

| Technologie | Rôle |
|-------------|------|
| **PHP** | Langage backend principal, gestion de la logique métier |
| **MySQL** | Base de données relationnelle |
| **HTML / CSS** | Structure et mise en forme des pages |
| **JavaScript** | Interactions dynamiques côté client |
| **Bootstrap** | Framework CSS pour le design responsive |


---

##  Structure du Projet

```
Esprit-PW-2A21-2526-FOODWISE/
│
├── config/                  # Fichiers de configuration (BDD, constantes)
├── controllers/             # Contrôleurs PHP (logique métier)
├── models/                  # Modèles PHP (accès aux données)
├── views/                   # Vues HTML/PHP (interface utilisateur)
│   ├── user/                # Pages côté utilisateur
│   ├── merchant/            # Pages côté commerçant
│   └── admin/               # Pages côté administrateur
├── public/                  # Assets publics
│   ├── css/                 # Feuilles de style
│   ├── js/                  # Scripts JavaScript
│   └── images/              # Images et icônes
├── database/                # Script SQL de création/peuplement BDD
├── README.md                # Documentation du projet
└── index.php                # Point d'entrée de l'application
```

---



---

##  Équipe

Les membres de notre equipe AXIUM

| Nom | Module developpé |
|-----|------|
| Trabelsi EMNA | RecipeBook — Gestion des recettes |
| Laghlough Elaa |  LocalMarket — Commerçants & offres |
| Melki Islem | SmartCart — Commandes & paiement |
| Abdallah  Mohammed Samir | NutriProfile — Profil nutritionnel |
| Ben Ayed Yassine  | Community — Avis & favoris |
| Ellouz Amine | MealPlanner — Plans alimentaires |



---

##  Licence

Ce projet est réalisé à des fins **pédagogiques** dans le cadre Projet Technologies Web.  
Tous droits réservés © 2025–2026 — Équipe AXIUM.

---

<p align="center">
  Fait avec <<3>> par l'équipe AXIUM · Esprit 2A21
</p>



# SportCoach
Une plateforme web moderne de mise en relation entre coachs sportifs et sportifs, développée en PHP orienté objet avec PDO.

# Description
SportCoach permet aux coachs de gérer leurs disponibilités et aux sportifs de réserver des séances d'entraînement en toute simplicité.

Fonctionnalités principales
Pour les coachs : Création et gestion de disponibilités (séances d'entraînement)
Pour les sportifs : Consultation des profils de coachs et réservation de séances disponibles
Système de réservation : Une séance réservée passe automatiquement de disponible à réservée
# Technologies
Backend : PHP (Programmation Orientée Objet), PDO
Base de données : MySQL / MariaDB
Frontend : HTML, CSS, JavaScript
# Architecture du projet
COACH-PRO-V2/
│
├── config/
│   └── database.php           # Configuration de la connexion BDD
│
├── classes/
│   ├── Utilisateur.php        # Classe parent
│   ├── Coach.php              # Gestion des coachs
│   ├── Sportif.php            # Gestion des sportifs
│   ├── Seance.php             # Gestion des séances
│   └── Reservation.php        # Gestion des réservations
│
├── pages/
│   ├── login.php              # Connexion
│   ├── register.php           # Inscription
│   ├── logout.php             # Déconnexion
│   ├── reserve_seance.php     # Réservation d'une séance
│   ├── cancel_reservation.php # Annulation de réservation
│   ├── 404.php                # Page d'erreur
│   │
│   └── Dashboards/
│       ├── dashboard_coach.php    # Tableau de bord coach
│       └── dashboard_sportif.php  # Tableau de bord sportif
│
├── styles/
│   └── style.css              # Feuilles de style
│
├── script/
│   └── script.js              # Scripts JavaScript
│
└── README.md
# Base de données
Création de la base
sql
CREATE DATABASE coach_pro;
USE coach_pro;
Table users
sql
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom_user VARCHAR(100),
    prenom_user VARCHAR(100),
    email_user VARCHAR(150) UNIQUE,
    role_user ENUM('coach', 'sportif') NOT NULL,
    phone_user VARCHAR(10),
    password_user VARCHAR(255)
);
Table coachs
sql
CREATE TABLE coachs (
    id_user INT PRIMARY KEY,
    discipline_coach VARCHAR(100),
    experiences_coach INT,
    description_coach TEXT,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);
Table sportifs
sql
CREATE TABLE sportifs (
    id_user INT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);
Table seances
sql
CREATE TABLE seances (
    id_seance INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT,
    date_seance DATE,
    heure_seance TIME,
    duree_senace INT,
    statut_seance ENUM('disponible', 'reservee') DEFAULT 'disponible',
    FOREIGN KEY (coach_id) REFERENCES coachs(id_user)
);
Table reservations
sql
CREATE TABLE reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    seance_id INT NOT NULL UNIQUE,
    sportif_id INT NOT NULL,
    statut_reservation ENUM('active','annulee') DEFAULT 'active',
    FOREIGN KEY (seance_id) REFERENCES seances(id_seance),
    FOREIGN KEY (sportif_id) REFERENCES sportifs(id_user)
);
# Fonctionnement
1- Inscription & Connexion
Inscription

Création d'un compte dans la table users
Si role_user = 'coach' → insertion dans coachs
Si role_user = 'sportif' → insertion dans sportifs
Connexion

Redirection selon le rôle :
Coach → pages/Dashboards/dashboard_coach.php
Sportif → pages/Dashboards/dashboard_sportif.php
2- Gestion des séances (Coach)
Dans dashboard_coach.php, le coach peut :

Ajouter une séance (date, heure, durée)
Supprimer une séance (uniquement si statut_seance = 'disponible')
Consulter ses réservations
3- Réservation (Sportif)
Dans dashboard_sportif.php, le sportif peut :

Consulter la liste des coachs
Voir le profil détaillé d'un coach
Afficher les séances disponibles
Réserver une séance
Logique de réservation :

Vérifier l'existence de la séance
Vérifier que statut_seance = 'disponible'
Insérer dans reservations (seance_id, sportif_id)
Mettre à jour seances.statut_seance = 'reservee'
Garantie d'unicité : Une séance ne peut être réservée qu'une seule fois grâce à la contrainte UNIQUE(seance_id) dans la table reservations.

# Connexion à la base de données
Le fichier config/database.php contient la classe Database qui :

Stocke les informations de connexion
Crée une instance PDO unique
Retourne la connexion via la méthode connect()
Exemple d'utilisation :

php
$db = new Database();
$pdo = $db->connect();
# Sécurité
Bonnes pratiques implémentées
Utilisation systématique de prepare() et execute() pour éviter les injections SQL
Hashage des mots de passe :
password_hash() lors de l'inscription
password_verify() lors de la connexion
# Installation
Prérequis
Serveur Apache
MySQL / MariaDB
PHP 7.4 ou supérieur
Ubuntu / Linux (ou XAMPP / WAMP pour Windows)
Étapes d'installation
1- Installer les dépendances (Ubuntu)
bash
# Mettre à jour les paquets
sudo apt update

# Installer Apache
sudo apt install apache2

# Installer MySQL
sudo apt install mysql-server

# Installer PHP et les extensions nécessaires
sudo apt install php libapache2-mod-php php-mysql php-pdo
2- Cloner ou télécharger le projet
bash
# Se placer dans le répertoire web d'Apache
cd /var/www/html

# Cloner le projet (ou télécharger et extraire)
sudo git clone <url-du-projet> coach-pro
# OU
sudo cp -r /chemin/vers/COACH-PRO-V2 /var/www/html/coach-pro

# Donner les permissions appropriées
sudo chown -R www-data:www-data /var/www/html/coach-pro
sudo chmod -R 755 /var/www/html/coach-pro
3- Configurer MySQL
bash
# Se connecter à MySQL
sudo mysql -u root -p

# Dans le prompt MySQL, exécuter :
sql
CREATE DATABASE coach_pro;
USE coach_pro;

-- Exécuter tous les scripts SQL de création des tables
-- (copier-coller les scripts depuis la section "Base de données")

-- Créer un utilisateur pour l'application (optionnel mais recommandé)
CREATE USER 'coach_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON coach_pro.* TO 'coach_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
4- Configurer la connexion
Modifier config/database.php avec vos identifiants MySQL :

php
private $host = "localhost";
private $db_name = "coach_pro";
private $username = "coach_user";  // ou "root"
private $password = "votre_mot_de_passe";
5- Démarrer les services (Ubuntu)
bash
# Démarrer Apache
sudo systemctl start apache2

# Démarrer MySQL
sudo systemctl start mysql

# Vérifier le statut des services
sudo systemctl status apache2
sudo systemctl status mysql

# Activer le démarrage automatique (optionnel)
sudo systemctl enable apache2
sudo systemctl enable mysql
6- Accéder à l'application
Inscription : http://localhost/coach-pro/pages/register.php
Connexion : http://localhost/coach-pro/pages/login.php
Commandes utiles (Ubuntu)
bash
# Redémarrer Apache après modification
sudo systemctl restart apache2

# Arrêter les services
sudo systemctl stop apache2
sudo systemctl stop mysql

# Voir les logs d'erreur Apache
sudo tail -f /var/log/apache2/error.log

# Voir les logs d'erreur PHP
sudo tail -f /var/log/apache2/error.log
# Rôles utilisateurs
# Coach
Ajouter et supprimer des séances
Consulter les réservations de ses séances
Gérer son profil (discipline, expérience, description)
# Sportif
Consulter la liste des coachs disponibles
Voir les profils et disponibilités des coachs
Réserver des séances
Gérer ses réservations
# 🚫 Page 404
Configuration avec Apache
Créer un fichier .htaccess à la racine :

apache
ErrorDocument 404 /pages/404.php
# Licence
Ce projet est développé à des fins éducatives.

# Auteur
Projet SportCoach - Plateforme de coaching sportif
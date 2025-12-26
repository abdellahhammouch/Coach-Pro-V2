SportCoach
Une plateforme web moderne de mise en relation entre coachs sportifs et sportifs, développée en PHP orienté objet avec PDO.

Table des matières
Description
Fonctionnalités
Technologies
Architecture du projet
Installation
Base de données
Utilisation
Sécurité
Licence
Description
SportCoach permet aux coachs de gérer leurs disponibilités et aux sportifs de réserver des séances d'entraînement en toute simplicité.

Fonctionnalités
Pour les coachs :

Création et gestion de disponibilités (séances d'entraînement)
Consultation des réservations
Gestion du profil (discipline, expérience, description)
Pour les sportifs :

Consultation des profils de coachs
Réservation de séances disponibles
Gestion de leurs réservations
Système de réservation :

Une séance réservée passe automatiquement de disponible à réservée
Garantie d'unicité : une séance ne peut être réservée qu'une seule fois
Technologies
Backend : PHP 7.4+ (Programmation Orientée Objet)
Base de données : MySQL / MariaDB avec PDO
Frontend : HTML5, CSS3, JavaScript
Serveur : Apache
Architecture du projet
COACH-PRO-V2/
│
├── config/
│   └── database.php              # Configuration de la connexion BDD
│
├── classes/
│   ├── Utilisateur.php           # Classe parent
│   ├── Coach.php                 # Gestion des coachs
│   ├── Sportif.php               # Gestion des sportifs
│   ├── Seance.php                # Gestion des séances
│   └── Reservation.php           # Gestion des réservations
│
├── pages/
│   ├── login.php                 # Connexion
│   ├── register.php              # Inscription
│   ├── logout.php                # Déconnexion
│   ├── reserve_seance.php        # Réservation d'une séance
│   ├── cancel_reservation.php    # Annulation de réservation
│   ├── 404.php                   # Page d'erreur
│   │
│   └── Dashboards/
│       ├── dashboard_coach.php    # Tableau de bord coach
│       └── dashboard_sportif.php  # Tableau de bord sportif
│
├── styles/
│   └── style.css                 # Feuilles de style
│
├── script/
│   └── script.js                 # Scripts JavaScript
│
└── README.md
Installation
Prérequis
Apache 2.4+
MySQL 5.7+ / MariaDB 10.3+
PHP 7.4+
Ubuntu / Linux (ou XAMPP / WAMP pour Windows)
Étapes d'installation
1. Installer les dépendances (Ubuntu/Debian)
bash
# Mettre à jour les paquets
sudo apt update

# Installer Apache
sudo apt install apache2

# Installer MySQL
sudo apt install mysql-server

# Installer PHP et les extensions nécessaires
sudo apt install php libapache2-mod-php php-mysql php-pdo
2. Cloner le projet
bash
# Se placer dans le répertoire web d'Apache
cd /var/www/html

# Cloner le projet
sudo git clone <url-du-projet> coach-pro

# Donner les permissions appropriées
sudo chown -R www-data:www-data /var/www/html/coach-pro
sudo chmod -R 755 /var/www/html/coach-pro
3. Configurer la base de données
bash
# Se connecter à MySQL
sudo mysql -u root -p
Exécuter les commandes SQL suivantes :

sql
CREATE DATABASE coach_pro;
USE coach_pro;

-- Créer un utilisateur pour l'application
CREATE USER 'coach_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON coach_pro.* TO 'coach_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
Voir la section Base de données pour les scripts de création des tables.

4. Configurer la connexion
Modifier le fichier config/database.php :

php
private $host = "localhost";
private $db_name = "coach_pro";
private $username = "coach_user";
private $password = "votre_mot_de_passe";
5. Démarrer les services
bash
# Démarrer Apache
sudo systemctl start apache2

# Démarrer MySQL
sudo systemctl start mysql

# Vérifier le statut
sudo systemctl status apache2
sudo systemctl status mysql
6. Accéder à l'application
Ouvrir le navigateur et accéder à :

Inscription : http://localhost/coach-pro/pages/register.php
Connexion : http://localhost/coach-pro/pages/login.php
Commandes utiles
bash
# Redémarrer Apache
sudo systemctl restart apache2

# Arrêter les services
sudo systemctl stop apache2
sudo systemctl stop mysql

# Consulter les logs d'erreur
sudo tail -f /var/log/apache2/error.log
Base de données
Schéma de la base
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
Diagramme relationnel
users (1) ----< coachs
users (1) ----< sportifs
coachs (1) ----< seances
seances (1) ---- (1) reservations
sportifs (1) ----< reservations
Utilisation
Inscription
Accéder à pages/register.php
Remplir le formulaire (nom, prénom, email, téléphone, mot de passe)
Choisir le rôle : Coach ou Sportif
Si Coach : renseigner discipline, années d'expérience, description
Connexion
Accéder à pages/login.php
Saisir email et mot de passe
Redirection automatique selon le rôle :
Coach → dashboard_coach.php
Sportif → dashboard_sportif.php
Espace Coach
Ajouter une séance : Date, heure, durée
Voir mes séances : Liste des disponibilités
Supprimer une séance : Uniquement si non réservée
Consulter les réservations : Voir qui a réservé
Espace Sportif
Explorer les coachs : Liste des coachs disponibles
Voir un profil : Détails du coach (discipline, expérience)
Réserver une séance : Choisir parmi les disponibilités
Mes réservations : Gérer les séances réservées
Logique de réservation
Le sportif sélectionne une séance disponible
Vérification du statut disponible
Création de la réservation dans la table reservations
Mise à jour automatique : statut_seance → reservee
Sécurité
Mesures implémentées
Requêtes préparées : Utilisation de prepare() et execute() avec PDO
Hashage des mots de passe :
password_hash() à l'inscription
password_verify() à la connexion
Validation des données : Filtrage et sanitisation des entrées
Contraintes d'intégrité : Clés étrangères et contraintes UNIQUE
Recommandations
Utiliser HTTPS en production
Configurer des sessions sécurisées
Limiter les tentatives de connexion
Sauvegarder régulièrement la base de données
Configuration Apache
Page 404 personnalisée
Créer un fichier .htaccess à la racine :

apache
ErrorDocument 404 /coach-pro/pages/404.php
Activer mod_rewrite (optionnel)
bash
sudo a2enmod rewrite
sudo systemctl restart apache2
Connexion PDO
Le fichier config/database.php contient la classe Database :

php
class Database {
    private $host = "localhost";
    private $db_name = "coach_pro";
    private $username = "coach_user";
    private $password = "votre_mot_de_passe";
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
        return $this->conn;
    }
}
Utilisation :

php
$db = new Database();
$pdo = $db->connect();
Contribuer
Les contributions sont les bienvenues ! N'hésitez pas à :

Fork le projet
Créer une branche (git checkout -b feature/amelioration)
Commit vos changements (git commit -m 'Ajout d'une fonctionnalité')
Push vers la branche (git push origin feature/amelioration)
Ouvrir une Pull Request
Licence
Ce projet est développé à des fins éducatives.

Auteur
SportCoach - Plateforme de coaching sportif

Note : Ce projet est un exercice pédagogique. Pour une utilisation en production, des mesures de sécurité supplémentaires doivent être mises en place.


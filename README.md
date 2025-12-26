# SportCoach — Plateforme Coach ↔ Sportif (PHP POO + PDO)

## Présentation
**SportCoach** est une plateforme web qui met en relation des **sportifs** et des **coachs**.

- Un **coach** peut **ajouter des disponibilités** (séances).
- Un **sportif** peut **voir les disponibilités** d’un coach et **réserver** une séance si elle est encore **disponible**.
- Une séance réservée passe de `disponible` à `reservee`.

---

## Technologies utilisées
- PHP (Programmation Orientée Objet)
- PDO (connexion base de données)
- MySQL / MariaDB
- HTML / CSS / JS

---

## Structure du projet
> Les dashboards sont dans `pages/Dashboards/`.

COACH-PRO-V2/
├─ config/
│ └─ database.php
├─ classes/
│ ├─ Utilisateur.php
│ ├─ Seance.php
│ ├─ Sportif.php
│ ├─ Coach.php
│ └─ Reservation.php
├─ pages/
│ ├─ 404.php
│ ├─ login.php
│ ├─ register.php
│ ├─ logout.php
│ ├─ cancel_reservation.php
│ ├─ reserve_seance.php
│ └─ Dashboards/
│   ├─ dashboard_sportif.php
│   └─ dashboard_coach.php
├─ styles/
│ └─ style.css
├─ script/
│ └─ script.js
└─ README.md


---

## Base de données

### Création + Tables
```sql
CREATE DATABASE coach_pro;
USE coach_pro;

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom_user VARCHAR(100),
    prenom_user VARCHAR(100),
    email_user VARCHAR(150) UNIQUE,
    role_user ENUM('coach', 'sportif') NOT NULL,
    phone_user VARCHAR(10),
    password_user VARCHAR(255)
);

CREATE TABLE coachs (
    id_user INT PRIMARY KEY,
    discipline_coach VARCHAR(100),
    experiences_coach INT,
    description_coach TEXT,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

CREATE TABLE sportifs (
    id_user INT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

CREATE TABLE seances (
    id_seance INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT,
    date_seance DATE,
    heure_seance TIME,
    duree_senace INT,
    statut_seance ENUM('disponible', 'reservee') DEFAULT 'disponible',
    FOREIGN KEY (coach_id) REFERENCES coachs(id_user)
);

CREATE TABLE reservations (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    seance_id INT NOT NULL UNIQUE,
    sportif_id INT NOT NULL,
    statut_reservation ENUM('active','annulee') DEFAULT 'active',
    FOREIGN KEY (seance_id) REFERENCES seances(id_seance),
    FOREIGN KEY (sportif_id) REFERENCES sportifs(id_user)
);
Fonctionnement du système
1) Inscription / Connexion
À l’inscription, on insère dans users.

Si rôle = coach → insertion aussi dans coachs.

Si rôle = sportif → insertion aussi dans sportifs.

Au login :

Si rôle = coach → redirection vers pages/Dashboards/dashboard_coach.php

Si rôle = sportif → redirection vers pages/Dashboards/dashboard_sportif.php

2) Gestion des séances (Coach)
Dans pages/Dashboards/dashboard_coach.php :

Le coach peut ajouter une séance (date, heure, durée).

Le coach peut supprimer une séance uniquement si statut_seance = disponible.

Table principale :

seances

3) Réservation (Sportif)
Dans pages/Dashboards/dashboard_sportif.php :

Le sportif affiche les coachs

Il ouvre le profil d’un coach

Il voit ses séances disponibles

Il clique sur Réserver pour choisir une séance

Logique de réservation :

Vérifier que la séance existe et que statut_seance = disponible

Insérer dans reservations :

seance_id

sportif_id

Mettre à jour seances.statut_seance → reservee

Une séance ne peut être réservée qu’une seule fois grâce à UNIQUE(seance_id) dans reservations.

PDO : Connexion à la base (config/database.php)
Le fichier config/database.php contient une classe Database qui :

stocke les infos de connexion (host, dbName, username, password)

crée une connexion PDO une seule fois ($conn)

retourne la connexion via connect().

Utilisation :

php
Copy code
$db = new Database();
$pdo = $db->connect();
Sécurité (minimum recommandé)
Utiliser des requêtes préparées (prepare + execute)

Mot de passe sécurisé :

password_hash() à l’inscription

password_verify() au login

Lancer le projet
Importer la base de données coach_pro (phpMyAdmin ou MySQL CLI)

Vérifier les identifiants dans config/database.php

Démarrer Apache + MySQL (XAMPP / WAMP / LAMP)

Ouvrir dans le navigateur :

.../pages/register.php

.../pages/login.php

Rôles
Coach
Ajouter / supprimer des séances

Voir les réservations

Sportif
Voir les coachs

Voir les disponibilités

Réserver une séance

Voir ses réservations
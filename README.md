# SportCoach — Plateforme Coach ↔ Sportif (PHP POO + PDO)

---

## Présentation

**SportCoach** est une plateforme web qui met en relation des **sportifs** et des **coachs**.

- Un **coach** peut ajouter des **disponibilités** (séances).
- Un **sportif** peut voir les disponibilités d’un coach et **réserver** une séance si elle est encore **disponible**.
- Une séance réservée passe de `disponible` à `reservee`.

---

## Technologies utilisées

- **PHP** (Programmation Orientée Objet)
- **PDO** (connexion base de données)
- **MySQL / MariaDB**
- **HTML / CSS / JavaScript**

---

## Structure du projet

> Les dashboards sont dans `pages/Dashboards/`.

```txt
COACH-PRO-V2/
├─ config/
│  └─ database.php
├─ classes/
│  ├─ Utilisateur.php
│  ├─ Seance.php
│  ├─ Sportif.php
│  ├─ Coach.php
│  └─ Reservation.php
├─ pages/
│  ├─ 404.php
│  ├─ login.php
│  ├─ register.php
│  ├─ logout.php
│  ├─ cancel_reservation.php
│  ├─ reserve_seance.php
│  └─ Dashboards/
│     ├─ dashboard_sportif.php
│     └─ dashboard_coach.php
├─ styles/
│  └─ style.css
├─ script/
│  └─ script.js
└─ README.md

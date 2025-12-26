<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "coach") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../classes/Seance.php";

$db = new Database();
$pdo = $db->connect();

$coach_id = (int)$_SESSION["user_id"];

$stmtCoach = $pdo->prepare("SELECT  u.id_user, u.nom_user, u.prenom_user, u.email_user,
                                    c.discipline_coach, c.experiences_coach, c.description_coach
                            FROM users u
                            JOIN coachs c ON c.id_user = u.id_user
                            WHERE u.id_user = ?
                            LIMIT 1");
$stmtCoach->execute([$coach_id]);
$coach = $stmtCoach->fetch();

if (!$coach) {
    die("Profil coach introuvable dans la table coachs.");
}

$coach_nom        = $coach["nom_user"];
$coach_prenom     = $coach["prenom_user"];
$coach_email      = $coach["email_user"];
$coach_discipline = $coach["discipline_coach"];
$coach_experience = $coach["experiences_coach"];
$coach_desc       = $coach["description_coach"];

$seanceModel = new Seance($pdo);
$errors = [];
$success = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_seance"])) {
    $date  = trim($_POST["date"]);
    $heure = trim($_POST["heure"]);
    $duree = $_POST["duree"];

    if ($date === "" || $heure === "" || $duree <= 0) {
        $errors[] = "Tous les champs sont obligatoires (durée > 0).";
    } else {
        $ok = $seanceModel->create($coach_id, $date, $heure, $duree);
        if ($ok) $success = "Séance ajoutée avec succès.";
        else $errors[] = "Erreur : impossible d'ajouter la séance.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_seance"])) {
    $id_seance = $_POST["id_seance"];

    if ($id_seance > 0) {
        $check = $pdo->prepare("SELECT statut_seance FROM seances 
                                WHERE id_seance=? AND coach_id=?");
        $check->execute([$id_seance, $coach_id]);
        $row = $check->fetch();

        if (!$row) {
            $errors[] = "Séance introuvable.";
        } else if (($row['statut_seance'] ?? '') !== 'disponible') {
            $errors[] = "Impossible : cette séance est déjà réservée.";
        } else {
            $ok = $seanceModel->delete($id_seance, $coach_id);
            if ($ok) $success = "Séance supprimée.";
            else $errors[] = "Suppression impossible.";
        }
    }
}

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM seances 
                            WHERE coach_id = ?");
$stmtTotal->execute([$coach_id]);
$total_seances = (int)$stmtTotal->fetchColumn();

$stmtDispo = $pdo->prepare("SELECT COUNT(*) FROM seances 
                            WHERE coach_id = ? AND statut_seance = 'disponible'");
$stmtDispo->execute([$coach_id]);
$dispo_seances = (int)$stmtDispo->fetchColumn();

$stmtRes = $pdo->prepare("SELECT COUNT(*) FROM seances 
                            WHERE coach_id = ? AND statut_seance = 'reservee'");
$stmtRes->execute([$coach_id]);
$reservee_seances = $stmtRes->fetchColumn();

$stmtAth = $pdo->prepare("SELECT COUNT(DISTINCT r.sportif_id)FROM reservations r
                            WHERE r.coach_id = ?");
$stmtAth->execute([$coach_id]);
$athletes_count = (int)$stmtAth->fetchColumn();


$mySeances = $seanceModel->getByCoach($coach_id);


$stmtReservations = $pdo->prepare("SELECT   r.id_reservation, r.created_at,
                                            s.id_seance, s.date_seance, s.heure_seance, s.duree_senace, s.statut_seance,
                                            u.nom_user AS sportif_nom, u.prenom_user AS sportif_prenom
                                    FROM reservations r
                                    JOIN seances s ON s.id_seance = r.seance_id
                                    JOIN users u ON u.id_user = r.sportif_id
                                    WHERE r.coach_id = ?
                                    ORDER BY s.date_seance DESC, s.heure_seance DESC");
$stmtReservations->execute([$coach_id]);
$allReservations = $stmtReservations->fetchAll();

$recentReservations = array_slice($allReservations, 0, 5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Coach - SportCoach</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../issets/style.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="#" class="logo" onclick="showSection('overview'); return false;">
            <i class="fas fa-dumbbell"></i>
            <span>SportCoach</span>
        </a>

        <ul class="nav-menu">
            <li style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-user-tie" style="font-size: 22px; color: var(--primary-dark);"></i>
                <span style="color: var(--primary-dark); font-weight: 600;">
                    <?= $coach_prenom . " " . $coach_nom ?>
                </span>
            </li>
            <li>
                <a href="../logout.php" class="btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link active" onclick="showSection('overview'); return false;">
                    <i class="fas fa-chart-line"></i>
                    <span>Vue d'ensemble</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('reservations'); return false;">
                    <i class="fas fa-calendar-check"></i>
                    <span>Réservations</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('seances'); return false;">
                    <i class="fas fa-clock"></i>
                    <span>Mes Séances</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('profile'); return false;">
                    <i class="fas fa-user-edit"></i>
                    <span>Mon Profil</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- MAIN -->
    <main class="main-content">

        <!-- OVERVIEW -->
        <div id="overviewSection" class="dashboard-section">
            <div class="dashboard-header">
                <h1>Tableau de bord</h1>
                <p style="color: var(--text-gray);">Bienvenue dans votre espace coach</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background:#fee2e2; border:2px solid #dc2626; color:#dc2626; padding:15px; border-radius:8px; margin-bottom:20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <ul style="margin: 10px 0 0 18px;">
                        <?php foreach($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background:#d1fae5; border:2px solid #10b981; color:#065f46; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center; font-weight:600;">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending"><i class="fas fa-list"></i></div>
                    <div class="stat-details">
                        <h3><?= $total_seances ?></h3>
                        <p>Total séances</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon confirmed"><i class="fas fa-check"></i></div>
                    <div class="stat-details">
                        <h3><?= $dispo_seances ?></h3>
                        <p>Séances disponibles</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon today"><i class="fas fa-lock"></i></div>
                    <div class="stat-details">
                        <h3><?= $reservee_seances ?></h3>
                        <p>Séances réservées</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tomorrow"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <h3><?= $athletes_count ?></h3>
                        <p>Sportifs totaux</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Réservations récentes</h2>
                    <button class="btn-secondary" onclick="showSection('reservations')">Voir tout</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Sportif</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Durée</th>
                            <th>Statut séance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentReservations) === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:40px; color: var(--text-gray);">
                                    Aucune réservation pour le moment
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentReservations as $r): ?>
                                <tr>
                                    <td><strong><?= $r['sportif_prenom'] . " " . $r['sportif_nom'] ?></strong></td>
                                    <td><?= $r['date_seance'] ?></td>
                                    <td><?= substr($r['heure_seance'], 0, 5) ?></td>
                                    <td><?= $r['duree_senace'] ?> min</td>
                                    <td>
                                        <?php if (($r['statut_seance']) === 'reservee'): ?>
                                            <span class="status-badge pending">Réservée</span>
                                        <?php else: ?>
                                            <span class="status-badge confirmed">Disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESERVATIONS -->
        <div id="reservationsSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Réservations</h1>
                <p style="color: var(--text-gray);">Liste des séances réservées par les sportifs</p>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Toutes les réservations</h2>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sportif</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Durée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($allReservations) === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:40px; color: var(--text-gray);">
                                    Aucune réservation
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allReservations as $r): ?>
                                <tr>
                                    <td>#<?= $r['id_reservation'] ?></td>
                                    <td><strong><?= $r['sportif_prenom'] . " " . $r['sportif_nom'] ?></strong></td>
                                    <td><?= $r['date_seance'] ?></td>
                                    <td><?= substr($r['heure_seance'], 0, 5) ?></td>
                                    <td><?= $r['duree_senace'] ?> min</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SEANCES (DISPONIBILITES) -->
        <div id="seancesSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Mes Séances</h1>
                <p style="color: var(--text-gray);">Ajoutez et supprimez vos disponibilités</p>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Mes séances</h2>
                    <button class="btn-primary" onclick="openModal('addSeanceModal')">
                        <i class="fas fa-plus"></i> Ajouter une séance
                    </button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mySeances) === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:40px; color: var(--text-gray);">
                                    Aucune séance
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($mySeances as $s): ?>
                                <tr>
                                    <td><strong><?= $s["date_seance"] ?></strong></td>
                                    <td><?= substr($s["heure_seance"], 0, 5) ?></td>
                                    <td><?= $s["duree_senace"] ?> min</td>
                                    <td>
                                        <?php if ($s["statut_seance"] === "disponible"): ?>
                                            <span class="status-badge confirmed">Disponible</span>
                                        <?php else: ?>
                                            <span class="status-badge pending">Réservée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <form method="POST" action="" onsubmit="return confirm('Supprimer cette séance ?');" style="display:inline;">
                                            <input type="hidden" name="id_seance" value="<?= $s["id_seance"] ?>">
                                            <button type="submit" name="delete_seance" class="btn-reject">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROFILE -->
        <div id="profileSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Mon Profil</h1>
                <p style="color: var(--text-gray);">Informations coach</p>
            </div>

            <div class="table-container">
                <form action="../update_coach.php" method="POST" style="max-width:700px; margin:0 auto;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="<?= $coach_prenom ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" name="nom" class="form-control" value="<?= $coach_nom ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $coach_email ?>" required>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label>Discipline</label>
                            <input type="text" name="discipline" class="form-control" value="<?= $coach_discipline ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Expérience (années)</label>
                            <input type="number" name="experience" class="form-control" value="<?= (int)$coach_experience ?>" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"
                                  style="padding:12px; resize:vertical; border-radius:8px; border:2px solid #e5e5e5;"
                                  required><?= $coach_desc ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>

    </main>
</div>

<!-- MODAL ADD SEANCE -->
<div class="modal" id="addSeanceModal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Ajouter une séance</h3>
            <button class="close-modal" onclick="closeModal('addSeanceModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="add_seance" value="1">

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Heure</label>
                <input type="time" name="heure" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Durée (minutes)</label>
                <input type="number" name="duree" class="form-control" min="1" placeholder="Ex: 60" required>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </form>
    </div>
</div>

<footer class="footer">
    <div class="footer-bottom" style="padding:20px;">
        <p>&copy; 2024 SportCoach. Tous droits réservés.</p>
    </div>
</footer>

<script src="../../script/script.js"></script>
</body>
</html>

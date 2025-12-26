<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sportif') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../classes/Seance.php";

$db = new Database();
$connect = $db->connect();
$seanceModel = new Seance($connect);

$sportifId = $_SESSION['user_id'];

/* Sportif infos */
$stmt = $connect->prepare("
    SELECT u.*
    FROM users u
    INNER JOIN sportifs s ON s.id_user = u.id_user
    WHERE u.id_user = ?
    LIMIT 1
");
$stmt->execute([$sportifId]);
$sportif = $stmt->fetch();

if (!$sportif) {
    header("Location: logout.php");
    exit();
}

$sportif_nom    = $sportif['nom_user'];
$sportif_prenom = $sportif['prenom_user'];
$sportif_email  = $sportif['email_user'];
$sportif_phone  = $sportif['phone_user'];

/* Total coaches */
$qTotal = $connect->prepare("SELECT COUNT(*) AS total FROM coachs");
$qTotal->execute();
$coaches_totaux = ($qTotal->fetch()['total'] ?? 0);

/* Total available seances (global) */
$qAvail = $connect->prepare("SELECT COUNT(*) AS total FROM seances WHERE statut_seance = 'disponible'");
$qAvail->execute();
$seances_disponibles = ($qAvail->fetch()['total'] ?? 0);

/* Reservations active for this sportif */
$qActive = $connect->prepare("SELECT COUNT(*) AS total FROM reservations WHERE sportif_id = ? AND statut_reservation = 'active'");
$qActive->execute([$sportifId]);
$seances_reservees = ($qActive->fetch()['total'] ?? 0);

/* Reservations cancelled for this sportif (you used it as “terminees”) */
$qCancel = $connect->prepare("SELECT COUNT(*) AS total FROM reservations WHERE sportif_id = ? AND statut_reservation = 'annulee'");
$qCancel->execute([$sportifId]);
$seances_terminees = ($qCancel->fetch()['total'] ?? 0);

/* Recent reservations for this sportif (last 3) */
$recent = $connect->prepare("SELECT r.id_reservation,r.statut_reservation,s.id_seance,
                                    s.date_seance,s.heure_seance,s.duree_senace,
                                    u.nom_user AS coach_nom,
                                    u.prenom_user AS coach_prenom,
                                    c.discipline_coach
                            FROM reservations r
                            INNER JOIN seances s ON s.id_seance = r.seance_id
                            INNER JOIN coachs c ON c.id_user = s.coach_id
                            INNER JOIN users u ON u.id_user = c.id_user
                            WHERE r.sportif_id = ?
                            ORDER BY s.date_seance DESC, s.heure_seance DESC
                            LIMIT 3");
$recent->execute([$sportifId]);
$recent_list = $recent->fetchAll();

$all = $connect->prepare("SELECT r.id_reservation,r.statut_reservation,
                                s.id_seance,s.date_seance,s.heure_seance,
                                s.duree_senace,u.nom_user AS coach_nom,
                                u.prenom_user AS coach_prenom,c.discipline_coach
                        FROM reservations r
                        INNER JOIN seances s ON s.id_seance = r.seance_id
                        INNER JOIN coachs c ON c.id_user = s.coach_id
                        INNER JOIN users u ON u.id_user = c.id_user
                        WHERE r.sportif_id = ?
                        ORDER BY s.date_seance DESC, s.heure_seance DESC");
$all->execute([$sportifId]);
$all_list = $all->fetchAll();

$coachesStmt = $connect->prepare("SELECT    c.id_user AS id_coach,u.nom_user AS coach_nom,
                                            u.prenom_user AS coach_prenom,c.discipline_coach,
                                            c.experiences_coach,c.description_coach,
                                            (SELECT COUNT(*) FROM seances s
                                            WHERE s.coach_id = c.id_user AND s.statut_seance = 'disponible') 
                                AS seances_dispo FROM coachs c
                                INNER JOIN users u ON u.id_user = c.id_user
                                WHERE u.role_user = 'coach'
                                ORDER BY u.nom_user ASC, u.prenom_user ASC");
$coachesStmt->execute();
$coaches = $coachesStmt->fetchAll();

/* My coaches (coaches I already reserved with) */
$mycoaches = $connect->prepare("SELECT  c.id_user AS id_coach,
                                        u.nom_user AS coach_nom,u.prenom_user AS coach_prenom,
                                        c.discipline_coach,COUNT(r.id_reservation) AS total_seances,
                                        MIN(s.date_seance) AS since_date
                                FROM reservations r
                                INNER JOIN seances s ON s.id_seance = r.seance_id
                                INNER JOIN coachs c ON c.id_user = s.coach_id
                                INNER JOIN users u ON u.id_user = c.id_user
                                WHERE r.sportif_id = ?
                                AND r.statut_reservation = 'active'
                                GROUP BY c.id_user
                                ORDER BY since_date DESC");
$mycoaches->execute([$sportifId]);
$mycoaches_list = $mycoaches->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - SportCoach</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/style.css">
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="#" class="logo" onclick="showSection('overview'); return false;">
            <i class="fas fa-dumbbell"></i>
            <span>SportCoach</span>
        </a>

        <ul class="nav-menu">
            <li>
                <a href="#" class="nav-link" onclick="showSection('findcoach'); return false;">
                    <i class="fas fa-users"></i> Trouver un coach
                </a>
            </li>

            <li style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-user-circle" style="font-size: 26px; color: var(--primary-dark);"></i>
                <span style="color: var(--primary-dark); font-weight:600;">
                    <?= $sportif_nom . ' ' . $sportif_prenom ?>
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
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link active" onclick="showSection('overview'); return false;">
                    <i class="fas fa-chart-line"></i>
                    <span>Vue d'ensemble</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('mybookings'); return false;">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mes Réservations</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('findcoach'); return false;">
                    <i class="fas fa-search"></i>
                    <span>Trouver un Coach</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="showSection('mycoaches'); return false;">
                    <i class="fas fa-user-tie"></i>
                    <span>Mes Coachs</span>
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

    <main class="main-content">

        <!-- Overview -->
        <div id="overviewSection" class="dashboard-section">
            <div class="dashboard-header">
                <h1>Tableau de bord</h1>
                <p style="color: var(--text-gray);">Bienvenue dans votre espace personnel</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                    <div class="stat-details">
                        <h3><?= $seances_reservees ?></h3>
                        <p>Réservations actives</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon confirmed"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-details">
                        <h3><?= $seances_disponibles ?></h3>
                        <p>Séances disponibles</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon today"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-details">
                        <h3><?= $seances_terminees ?></h3>
                        <p>Réservations annulées</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tomorrow"><i class="fas fa-user-friends"></i></div>
                    <div class="stat-details">
                        <h3><?= $coaches_totaux ?></h3>
                        <p>Coachs totaux</p>
                    </div>
                </div>
            </div>

            <!-- Recent reservations -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Réservations récentes</h2>
                    <button class="btn-secondary" onclick="showSection('mybookings')">Voir tout</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Coach</th>
                            <th>Discipline</th>
                            <th>Date & Heure</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($recent_list) > 0): ?>
                        <?php foreach ($recent_list as $r): ?>
                            <?php
                                $status_class = 'pending';
                                $status_label = 'Active';
                                if ($r['statut_reservation'] === 'annulee') {
                                    $status_class = 'cancelled';
                                    $status_label = 'Annulée';
                                }
                            ?>
                            <tr>
                                <td><strong><?= $r['coach_prenom'] . ' ' . $r['coach_nom'] ?></strong></td>
                                <td><?= $r['discipline_coach'] ?></td>
                                <td><?= date('d M Y', strtotime($r['date_seance'])) ?>, <?= substr($r['heure_seance'], 0, 5) ?></td>
                                <td><span class="status-badge <?= $status_class ?>"><?= $status_label ?></span></td>
                                <td class="action-buttons">
                                    <?php if ($r['statut_reservation'] === 'active'): ?>
                                        <form method="POST" action="../cancel_reservation.php" style="display:inline;">
                                            <input type="hidden" name="reservation_id" value="<?= $r['id_reservation'] ?>">
                                            <button type="submit" class="btn-reject" onclick="return confirm('Annuler cette réservation ?')">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--text-gray); font-size: 14px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:25px; color: var(--text-gray);">
                                Aucune réservation récente
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My bookings -->
        <div id="mybookingsSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Mes Réservations</h1>
                <p style="color: var(--text-gray);">Gérez toutes vos séances sportives</p>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Toutes mes réservations</h2>
                    <button class="btn-primary" onclick="showSection('findcoach')">
                        <i class="fas fa-plus"></i> Nouvelle réservation
                    </button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Coach</th>
                            <th>Discipline</th>
                            <th>Date & Heure</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($all_list) > 0): ?>
                        <?php foreach ($all_list as $r): ?>
                            <?php
                                $status_class = 'pending';
                                $status_label = 'Active';
                                if ($r['statut_reservation'] === 'annulee') {
                                    $status_class = 'cancelled';
                                    $status_label = 'Annulée';
                                }
                            ?>
                            <tr>
                                <td>#<?= str_pad($r['id_reservation'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td><strong><?= $r['coach_prenom'] . ' ' . $r['coach_nom'] ?></strong></td>
                                <td><?= $r['discipline_coach'] ?></td>
                                <td><?= date('d M Y', strtotime($r['date_seance'])) ?>, <?= substr($r['heure_seance'], 0, 5) ?></td>
                                <td><?= $r['duree_senace'] ?> min</td>
                                <td><span class="status-badge <?= $status_class ?>"><?= $status_label ?></span></td>
                                <td class="action-buttons">
                                    <?php if ($r['statut_reservation'] === 'active'): ?>
                                        <form method="POST" action="../cancel_reservation.php" style="display:inline;">
                                            <input type="hidden" name="reservation_id" value="<?= $r['id_reservation'] ?>">
                                            <button type="submit" class="btn-reject" onclick="return confirm('Annuler cette réservation ?')">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--text-gray); font-size: 14px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color: var(--text-gray);">
                                <i class="fas fa-inbox" style="font-size:48px; margin-bottom:10px; display:block;"></i>
                                Vous n'avez aucune réservation pour le moment
                                <br><br>
                                <button class="btn-primary" onclick="showSection('findcoach')">
                                    <i class="fas fa-search"></i> Trouver un coach
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Find coach -->
        <div id="findcoachSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Découvrez nos <span style="color: var(--primary-gold);">coachs professionnels</span></h1>
                <p style="color: var(--text-gray);">Trouvez le coach idéal pour atteindre vos objectifs sportifs</p>
            </div>

            <div class="coaches-grid" id="coachesGrid">
                <?php foreach ($coaches as $coach): ?>
                    <?php
                        $totalSportifsStmt = $connect->prepare("SELECT COUNT(DISTINCT r.sportif_id) AS totalSportifs
                                                                FROM reservations r
                                                                INNER JOIN seances s ON s.id_seance = r.seance_id
                                                                WHERE s.coach_id = ?");
                        $totalSportifsStmt->execute([$coach['id_coach']]);
                        $totalSportifs = ($totalSportifsStmt->fetch()['totalSportifs'] ?? 0);

                        $dispos = $seanceModel->getAvailableByCoach($coach['id_coach']);
                    ?>
                    <div class="coach-card" data-sport="<?= strtolower($coach['discipline_coach']) ?>">
                        <div style="height: 160px; display:flex; align-items:center; justify-content:center; background: var(--primary-light); border-radius: 12px 12px 0 0;">
                            <i class="fas fa-user-tie" style="font-size: 50px; color: var(--primary-gold);"></i>
                        </div>

                        <div class="coach-info">
                            <div class="coach-header">
                                <div>
                                    <h3 class="coach-name"><?= $coach['coach_nom'] . ' ' . $coach['coach_prenom'] ?></h3>
                                    <span class="coach-specialty"><?= $coach['discipline_coach'] ?></span>
                                </div>
                            </div>

                            <div class="coach-stats">
                                <div class="stat-item">
                                    <i class="fas fa-medal"></i>
                                    <span><?= $coach['experiences_coach'] ?> ans</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-users"></i>
                                    <span><?= $totalSportifs ?> élèves</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= $coach['seances_dispo'] ?> dispo</span>
                                </div>
                            </div>

                            <div class="coach-actions">
                                <button class="btn-view" onclick="viewCoachProfileModal(<?= $coach['id_coach'] ?>)">
                                    <i class="fas fa-eye"></i> Voir profil
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden modal content -->
                    <div id="coachModalContent<?= $coach['id_coach'] ?>" style="display:none;">
                        <div style="text-align:center; margin-bottom: 20px;">
                            <i class="fas fa-user-tie" style="font-size: 60px; color: var(--primary-gold);"></i>
                            <h2 style="color: var(--primary-dark); margin: 10px 0 5px;">
                                <?= $coach['coach_nom'] . ' ' . $coach['coach_prenom'] ?>
                            </h2>
                            <span style="color: var(--primary-gold); font-weight:600; font-size: 16px;">
                                <?= $coach['discipline_coach'] ?>
                            </span>
                        </div>

                        <div style="background-color: var(--primary-light); padding: 20px; border-radius: 10px; margin-bottom: 15px;">
                            <h3 style="color: var(--primary-dark); margin-bottom: 10px;">
                                <i class="fas fa-user"></i> À propos
                            </h3>
                            <p style="color: var(--text-gray); line-height: 1.8;">
                                <?= $coach['description_coach'] ?>
                            </p>
                        </div>

                        <h3 style="margin: 10px 0;">Disponibilités</h3>

                        <?php if (count($dispos) === 0): ?>
                            <p style="color: var(--text-gray);">Aucune disponibilité pour le moment.</p>
                        <?php else: ?>
                            <div class="table-container" style="margin-top: 10px;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Heure</th>
                                            <th>Durée</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dispos as $s): ?>
                                            <tr>
                                                <td><?= $s['date_seance'] ?></td>
                                                <td><?= substr($s['heure_seance'], 0, 5) ?></td>
                                                <td><?= $s['duree_senace'] ?> min</td>
                                                <td>
                                                    <form method="POST" action="../reserve_seance.php">
                                                        <input type="hidden" name="seance_id" value="<?= $s['id_seance'] ?>">
                                                        <button class="btn-book" type="submit">Réserver</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- My coaches -->
        <div id="mycoachesSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Mes Coachs</h1>
                <p style="color: var(--text-gray);">Les coachs avec qui vous travaillez</p>
            </div>

            <div class="coaches-grid" style="max-width:1200px;">
                <?php if (count($mycoaches_list) > 0): ?>
                    <?php foreach ($mycoaches_list as $coach): ?>
                        <div class="coach-card">
                            <div style="height: 160px; display:flex; align-items:center; justify-content:center; background: var(--primary-light); border-radius: 12px 12px 0 0;">
                                <i class="fas fa-user-tie" style="font-size: 50px; color: var(--primary-gold);"></i>
                            </div>

                            <div class="coach-info">
                                <div class="coach-header">
                                    <div>
                                        <h3 class="coach-name"><?= $coach['coach_nom'] . ' ' . $coach['coach_prenom'] ?></h3>
                                        <span class="coach-specialty"><?= $coach['discipline_coach'] ?></span>
                                    </div>
                                </div>

                                <div class="coach-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= $coach['total_seances'] ?> séances</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Depuis <?= date('d/m/Y', strtotime($coach['since_date'])) ?></span>
                                    </div>
                                </div>

                                <div class="coach-actions">
                                    <button class="btn-view" onclick="viewCoachProfileModal(<?= $coach['id_coach'] ?>)">
                                        <i class="fas fa-eye"></i> Voir profil 
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:60px 20px;">
                        <i class="fas fa-users" style="font-size:64px;"></i>
                        <h3>Vous n'avez pas encore de coach</h3>
                        <p>Trouvez un coach professionnel pour commencer</p>
                        <button class="btn-primary" onclick="showSection('findcoach')">
                            <i class="fas fa-search"></i> Trouver un coach
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile -->
        <div id="profileSection" class="dashboard-section" style="display:none;">
            <div class="dashboard-header">
                <h1>Mon Profil</h1>
                <p style="color: var(--text-gray);">Mes informations personnelles</p>
            </div>

            <div class="table-container" style="max-width:700px; margin:0 auto;">
                <div style="display:flex; gap:20px; align-items:center; margin-bottom:20px;">
                    <i class="fas fa-user-circle" style="font-size:80px; color: var(--primary-gold);"></i>
                    <div>
                        <h2 style="margin:0; color: var(--primary-dark);">
                            <?= $sportif_nom . ' ' . $sportif_prenom ?>
                        </h2>
                        <p style="margin:5px 0 0; color: var(--text-gray);">
                            Compte Sportif
                        </p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div style="background: var(--primary-light); padding:15px; border-radius:10px;">
                        <strong>Nom</strong>
                        <p style="margin:8px 0 0; color: var(--text-gray);"><?= $sportif_nom ?></p>
                    </div>

                    <div style="background: var(--primary-light); padding:15px; border-radius:10px;">
                        <strong>Prénom</strong>
                        <p style="margin:8px 0 0; color: var(--text-gray);"><?= $sportif_prenom ?></p>
                    </div>

                    <div style="background: var(--primary-light); padding:15px; border-radius:10px;">
                        <strong>Email</strong>
                        <p style="margin:8px 0 0; color: var(--text-gray);"><?= $sportif_email ?></p>
                    </div>

                    <div style="background: var(--primary-light); padding:15px; border-radius:10px;">
                        <strong>Téléphone</strong>
                        <p style="margin:8px 0 0; color: var(--text-gray);"><?= $sportif_phone ?></p>
                    </div>
                </div>

                <div style="margin-top:20px; text-align:center;">
                    <button class="btn-primary" onclick="showSection('findcoach')">
                        <i class="fas fa-search"></i> Trouver un coach
                    </button>
                </div>
            </div>
        </div>


    </main>
</div>

<div class="modal" id="coachModal" style="display:none;">
    <div class="modal-content" style="max-width:700px; scrollbar-width:none">
        <div class="modal-header">
            <h3>Profil du Coach</h3>
            <button class="close-modal" onclick="closeModal('coachModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalContent"></div>
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
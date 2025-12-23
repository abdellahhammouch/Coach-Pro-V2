<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require "auth_check.php";
    require "connect.php";

    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'sportif') {
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - SportCoach</title>
    <link rel="stylesheet" href="issets/style.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="dashboard-athlete.php" class="logo">
                <i class="fas fa-dumbbell"></i>
                <span>SportCoach</span>
            </a>
            <ul class="nav-menu">
                <li>
                    <a href="#" class="nav-link" onclick="showSection('findcoach')">
                        <i class="fas fa-users"></i> Trouver un coach
                    </a>
                </li>

                <li style="display: flex; align-items: center; gap: 10px;">
                    <img src="##" alt="########" style="width: 35px; height: 35px; border-radius: 50%;">
                    <span style="color: var(--primary-dark); font-weight: 600;">####</span>
                </li>

                <li>
                    <a href="logout.php" class="btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Layout -->
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link active" onclick="showSection('overview')">
                        <i class="fas fa-chart-line"></i>
                        <span>Vue d'ensemble</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" onclick="showSection('mybookings')">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mes Réservations</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" onclick="showSection('findcoach')">
                        <i class="fas fa-search"></i>
                        <span>Trouver un Coach</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" onclick="showSection('mycoaches')">
                        <i class="fas fa-user-tie"></i>
                        <span>Mes Coachs</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" onclick="showSection('profile')">
                        <i class="fas fa-user-edit"></i>
                        <span>Mon Profil</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <!-- Overview Section -->
            <div id="overviewSection" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Tableau de bord</h1>
                    <p style="color: var(--text-gray);">Bienvenue dans votre espace personnel</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <h3>###</h3>
                            <p>Réservations en attente</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon confirmed">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-details">
                            <h3>###</h3>
                            <p>Séances confirmées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon today">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3>###</h3>
                            <p>Séances complétées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon tomorrow">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-details">
                            <h3>###</h3>
                            <p>Coachs totaux</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
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
                            <tr>
                                <td><strong>#####</strong></td>
                                <td>Non spécifiée</td>
                                <td>01 Jan 2024, 10:00</td>
                                <td><span class="status-badge pending">En attente</span></td>
                                <td class="action-buttons">
                                    <button type="button" class="btn-reject">Annuler</button>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="5" style="text-align: center; padding: 25px; color: var(--text-gray);">
                                    Aucune réservation récente
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- My Bookings Section -->
            <div id="mybookingsSection" class="dashboard-section" style="display: none;">
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
                            <tr>
                                <td>#001</td>
                                <td><strong>Coach Exemple</strong></td>
                                <td>Fitness</td>
                                <td>01 Jan 2024, 10:00</td>
                                <td>1h</td>
                                <td><span class="status-badge confirmed">Confirmée</span></td>
                                <td class="action-buttons">
                                    <span style="color: var(--text-gray); font-size: 14px;">-</span>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-gray);">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                    Vous n'avez aucune réservation pour le moment
                                    <br><br>
                                    <button class="btn-primary" onclick="showSection('findcoach')">
                                        <i class="fas fa-search"></i> Trouver un coach
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FIND COACH SECTION -->
            <div id="findcoachSection" class="dashboard-section" style="display: none;">
                <div class="dashboard-header">
                    <h1>Découvrez nos <span style="color: var(--primary-gold);">coachs professionnels</span></h1>
                    <p style="color: var(--text-gray);">Trouvez le coach idéal pour atteindre vos objectifs sportifs</p>
                </div>

                <!-- Filter Section -->
                <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px var(--shadow); margin-bottom: 40px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div class="input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un coach...">
                        </div>
                        <div class="input-group">
                            <i class="fas fa-filter"></i>
                            <select id="sportFilter" class="form-control">
                                <option value="">Tous les sports</option>
                                <option value="football">Football</option>
                                <option value="tennis">Tennis</option>
                                <option value="natation">Natation</option>
                                <option value="boxe">Boxe</option>
                                <option value="fitness">Préparation physique</option>
                            </select>
                        </div>
                        <button class="btn-primary" onclick="filterCoaches()">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </div>

                <!-- Coaches Grid -->
                <div class="coaches-grid" id="coachesGrid">
                    <div class="coach-card" data-sport="football">
                        <img src="##" alt="Coach Exemple" class="coach-image">
                        <div class="coach-info">
                            <div class="coach-header">
                                <div>
                                    <h3 class="coach-name">Coach Exemple</h3>
                                    <span class="coach-specialty">Football</span>
                                </div>
                            </div>

                            <div class="coach-stats">
                                <div class="stat-item">
                                    <i class="fas fa-medal"></i>
                                    <span>5 ans</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-users"></i>
                                    <span>10 élèves</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-tag"></i>
                                    <span>100 DH/h</span>
                                </div>
                            </div>

                            <div class="coach-actions">
                                <button class="btn-view" onclick="viewCoachProfileModal(1)">
                                    <i class="fas fa-eye"></i> Voir profil
                                </button>
                                <button class="btn-book" onclick="bookSessionModal(1)">
                                    <i class="fas fa-calendar-plus"></i> Réserver
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Modal Content -->
                    <div id="coachModalContent1" style="display: none;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <img src="##" alt="Coach Exemple" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 15px; object-fit: cover;">
                            <h2 style="color: var(--primary-dark); margin-bottom: 5px;">Coach Exemple</h2>
                            <span style="color: var(--primary-gold); font-weight: 600; font-size: 18px;">Football</span>
                        </div>

                        <div style="background-color: var(--primary-light); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                            <h3 style="color: var(--primary-dark); margin-bottom: 15px;">
                                <i class="fas fa-user"></i> À propos
                            </h3>
                            <p style="color: var(--text-gray); line-height: 1.8;">
                                Bio du coach (exemple).
                            </p>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div style="background-color: var(--primary-light); padding: 15px; border-radius: 10px;">
                                <i class="fas fa-medal" style="color: var(--primary-gold); font-size: 20px; margin-bottom: 8px; display: block;"></i>
                                <strong style="color: var(--primary-dark);">Expérience</strong>
                                <p style="color: var(--text-gray); margin-top: 5px;">5 ans</p>
                            </div>
                            <div style="background-color: var(--primary-light); padding: 15px; border-radius: 10px;">
                                <i class="fas fa-users" style="color: var(--primary-gold); font-size: 20px; margin-bottom: 8px; display: block;"></i>
                                <strong style="color: var(--primary-dark);">Élèves</strong>
                                <p style="color: var(--text-gray); margin-top: 5px;">10 sportifs</p>
                            </div>
                        </div>

                        <div style="background-color: var(--primary-light); padding: 20px; border-radius: 10px; margin-bottom: 25px; text-align: center;">
                            <h3 style="color: var(--primary-dark); margin-bottom: 10px;">
                                <i class="fas fa-tag"></i> Tarif
                            </h3>
                            <p style="color: var(--primary-gold); font-size: 24px; font-weight: bold;">100 DH/heure</p>
                        </div>

                        <button onclick="bookSessionModal(1)" class="btn-submit" style="width: 100%;">
                            <i class="fas fa-calendar-plus"></i> Réserver une séance
                        </button>
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResultsMessage" style="display: none; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-search" style="font-size: 64px; color: var(--primary-gold); margin-bottom: 20px; display: block;"></i>
                    <h3 style="color: var(--primary-dark); margin-bottom: 10px;">Aucun coach trouvé</h3>
                    <p style="color: var(--text-gray);">Essayez de modifier vos critères de recherche</p>
                </div>
            </div>

            <!-- My Coaches Section -->
            <div id="mycoachesSection" class="dashboard-section" style="display: none;">
                <div class="dashboard-header">
                    <h1>Mes Coachs</h1>
                    <p style="color: var(--text-gray);">Les coachs avec qui vous travaillez</p>
                </div>

                <div class="coaches-grid" style="max-width: 1200px;">
                    <div class="coach-card">
                        <img src="##" alt="Coach Exemple" class="coach-image">
                        <div class="coach-info">
                            <div class="coach-header">
                                <div>
                                    <h3 class="coach-name">Coach Exemple</h3>
                                    <span class="coach-specialty">Fitness</span>
                                </div>
                            </div>

                            <div class="coach-stats">
                                <div class="stat-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>3 séances</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>Depuis 01/01/2024</span>
                                </div>
                            </div>

                            <div class="coach-actions">
                                <button class="btn-view" onclick="viewCoachProfileModal(1)">
                                    <i class="fas fa-eye"></i> Voir profil
                                </button>
                                <button class="btn-book" onclick="bookSessionModal(1)">
                                    <i class="fas fa-calendar-plus"></i> Réserver
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; padding: 60px 20px;">
                        <i class="fas fa-users" style="font-size: 64px;"></i>
                        <h3>Vous n'avez pas encore de coach</h3>
                        <p>Trouvez un coach professionnel pour commencer</p>
                        <button class="btn-primary" onclick="showSection('findcoach')">
                            <i class="fas fa-search"></i> Trouver un coach
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profileSection" class="dashboard-section" style="display: none;">
                <div class="dashboard-header">
                    <h1>Mon Profil</h1>
                    <p style="color: var(--text-gray);">Modifiez vos informations personnelles</p>
                </div>

                <div class="table-container">
                    <form id="athleteProfileForm" action="update_athlete.php" method="POST" enctype="multipart/form-data" style="max-width: 700px; margin: 0 auto;">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <img id="athletePhotoPreview" src="##" alt="Sportif Nom Prénom" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 15px; object-fit: cover;">

                            <input type="file" name="photo" id="athletePhotoInput" accept="image/*" style="display: none;">

                            <button type="button" class="btn-secondary" onclick="document.getElementById('athletePhotoInput').click();">
                                <i class="fas fa-camera"></i> Changer la photo
                            </button>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="" style="padding-left: 15px;" required>
                            </div>
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="" style="padding-left: 15px;" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="" style="padding-left: 15px;" required>
                        </div>

                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="tel" name="phone" class="form-control" value="" style="padding-left: 15px;" required>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <!-- Coach Profile Modal -->
    <div class="modal" id="coachModal" style="display: none;">
        <div class="modal-content" style="max-width: 700px; scrollbar-width:none">
            <div class="modal-header">
                <h3>Profil du Coach</h3>
                <button class="close-modal" onclick="closeModal('coachModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalContent">
                <!-- Content will be copied here by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-bottom" style="padding: 20px;">
            <p>&copy; 2024 SportCoach. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="issets/main.js"></script>
    <script src="issets/athlete_dashboard.js"></script>
    <script src="issets/coaches.js"></script>
</body>
</html>

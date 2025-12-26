<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Utilisateur.php";

$errors = [];
$old = [
    "nom" => "",
    "prenom" => "",
    "email" => "",
    "role" => ""
];

$success = $_SESSION["success"] ?? null;
unset($_SESSION["success"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom      = trim($_POST["nom"]);
    $prenom   = trim($_POST["prenom"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    $role     = trim($_POST["role"]);
    $disciplines = trim($_POST["disciplines"] ?? "");
    $experience  = $_POST["experience"] ?? null;
    $bio         = trim($_POST["biographie"] ?? "");

    $old["nom"] = $nom;
    $old["prenom"] = $prenom;
    $old["email"] = $email;
    $old["role"] = $role;

    if ($nom === "")      $errors[] = "Nom obligatoire.";
    if ($prenom === "")   $errors[] = "Prénom obligatoire.";
    if ($email === "")    $errors[] = "Email obligatoire.";
    if ($password === "") $errors[] = "Mot de passe obligatoire.";

    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if ($role !== "coach" && $role !== "sportif") {
        $errors[] = "Rôle invalide (coach ou sportif).";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    if ($role !== "coach") {
        $disciplines = null;
        $experience = null;
        $bio = null;
    }

    if (empty($errors)) {
        try {
            $db = new Database();
            $pdo = $db->connect();

            $user = new Utilisateur($pdo);

            if ($user->register($nom, $prenom, $email, $password, $role, $disciplines, $experience, $bio)) {
                $_SESSION["success"] = "Inscription réussie. Connecte-toi maintenant.";
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Inscription impossible. Email déjà utilisé ou données invalides.";
            }

        } catch (PDOException $e) {
            $errors[] = "Erreur base de données : " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SportCoach</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-dumbbell"></i>
                <span>SportCoach</span>
            </a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="coaches.php" class="nav-link"><i class="fas fa-users"></i> Nos Coachs</a></li>
                <li><a href="login.php" class="btn-secondary"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                <li><a href="register.php" class="btn-primary"><i class="fas fa-user-plus"></i> Inscription</a></li>
            </ul>
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="form-container" style="max-width: 600px;">
        <div class="form-header">
            <h2>Créer un compte</h2>
            <p>Rejoignez notre communauté sportive</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="" method="post" >
            <div class="form-group">
                <label for="userType">Je m'inscris en tant que</label>
                <div class="input-group">
                    <i class="fas fa-user-tag"></i>
                    <select name="role" id="userType" class="form-control" required>
                        <option value="">Sélectionnez votre rôle</option>
                        <option value="sportif">Sportif</option>
                        <option value="coach">Coach</option>
                    </select>
                </div>
                <span class="error-message" id="userTypeError">Veuillez sélectionner un type de compte</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="firstName">Prénom</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="prenom" id="firstName" class="form-control" placeholder="Votre prénom" required>
                    </div>
                    <span class="error-message" id="firstNameError">Veuillez entrer un prénom valide(pas de chiffres,min 2 caractères)</span>
                </div>

                <div class="form-group">
                    <label for="lastName">Nom</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nom" id="lastName" class="form-control" placeholder="Votre nom" required>
                    </div>
                    <span class="error-message" id="lastNameError">Veuillez entrer un nom valide(pas de chiffres,min 2 caractères)</span>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" class="form-control" placeholder="votre@email.com" required>
                </div>
                <span class="error-message" id="emailError">Veuillez entrer un email valide</span>
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="+212 6XX-XXXXXX" required>
                </div>
                <span class="error-message" id="phoneError">Veuillez entrer un numero de telephone valide</span>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 caractères" required>
                </div>
                <span class="error-message" id="passwordError">8 caractères min. (majuscule, minuscule, chiffres)</span>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmer le mot de passe</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Confirmez votre mot de passe" required>
                </div>
                <span class="error-message" id="confirmPasswordError">Les mots de passe ne correspondent pas</span>
            </div>

            <!-- Coach specific fields -->
            <div id="coachFields" style="display: none;">
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Vos Spécialités</label>
                    
                    <!-- Selected Disciplines Display (Top) -->
                    <div class="tag-input" id="tags">
                        <!-- Selected tags will appear here -->
                    </div>
                    <input type="hidden" name="disciplines" id="hiddenInput">
                    
                    <p class="discipline-hint">
                        <i class="fas fa-info-circle"></i>
                        Cliquez sur les disciplines pour les sélectionner
                    </p>

                    <span class="error-message" id="disciplineError">Veuillez sélectionner au moins une discipline</span>

                    <div class="choices">
                        <span class="choice" data-value="Football"><i class="fas fa-futbol"></i> Football</span>
                        <span class="choice" data-value="Tennis"><i class="fas fa-table-tennis"></i> Tennis</span>
                        <span class="choice" data-value="Natation"><i class="fas fa-swimmer"></i> Natation</span>
                        <span class="choice" data-value="Boxe"><i class="fas fa-fist-raised"></i> Boxe</span>
                        <span class="choice" data-value="Preparation physique"><i class="fas fa-dumbbell"></i> Préparation physique</span>
                        <span class="choice" data-value="Basketball"><i class="fas fa-basketball-ball"></i> Basketball</span>
                        <span class="choice" data-value="Yoga"><i class="fas fa-spa"></i> Yoga</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="experience"><i class="fas fa-calendar-alt"></i> Années d'expérience</label>
                    <div class="input-group">
                        <i class="fas fa-medal"></i>
                        <input type="number" name="experience" id="experience" class="form-control" placeholder="Ex: 5" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="biographie"><i class="fas fa-pen"></i> Biographie</label>
                    <textarea name="biographie" id="biographie" class="form-control" rows="4" placeholder="Parlez de votre expérience, votre approche et votre expertise..." style="padding: 12px; resize: vertical; border-radius: 8px; border: 2px solid #F5E6D3;"></textarea>
                </div>

                <div class="form-group">
                    <label for="prix"><i class="fas fa-money-bill"></i> Tarif par heure (DH)</label>
                    <div class="input-group">
                        <i class="fas fa-tag"></i>
                        <input type="number" name="prix" id="prix" class="form-control" placeholder="Ex: 100" min="50">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="terms" required style="margin-top: 4px;">
                    <span style="font-size: 14px; color: var(--text-gray);">
                        J'accepte les <a href="#" style="color: var(--primary-gold); font-weight: 600;">conditions d'utilisation</a>
                        et la <a href="#" style="color: var(--primary-gold); font-weight: 600;">politique de confidentialité</a>
                    </span>
                </label>
                <span class="error-message" id="termsError">Vous devez accepter les conditions</span>
            </div>

            <button type="submit" name="signup" class="btn-submit">
                <i class="fas fa-user-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="form-footer">
            <p>Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer" style="margin-top: 50px;">
        <div class="footer-container">
            <div class="footer-section">
                <h3><i class="fas fa-dumbbell"></i> SportCoach</h3>
                <p>Votre plateforme de mise en relation avec les meilleurs coachs sportifs professionnels.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="coaches.php">Nos Coachs</a></li>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul class="footer-links">
                    <li><a href="#">Centre d'aide</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Conditions d'utilisation</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope"></i> contact@sportcoach.com</li>
                    <li><i class="fas fa-phone"></i> +212 5XX-XXXXXX</li>
                    <li><i class="fas fa-map-marker-alt"></i> Casablanca, Maroc</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 SportCoach. Tous droits réservés.</p>
        </div>
    </footer>

<script src="../script/script.js" ></script>
</body>

</html>
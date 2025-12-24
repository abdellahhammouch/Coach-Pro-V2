<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Utilisateur.php";

$errors = [];
$old = [
    "email" => "",
    "role" => ""
];

// Flash messages (from register redirect)
$success = $_SESSION["success"] ?? null;
unset($_SESSION["success"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = trim($_POST["role"] ?? "");

    $old["email"] = $email;
    $old["role"] = $role;

    // Validations simples
    if ($email === "")    $errors[] = "Email obligatoire.";
    if ($password === "") $errors[] = "Mot de passe obligatoire.";
    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }
    if ($role !== "coach" && $role !== "sportif") {
        $errors[] = "Veuillez sélectionner le type de compte.";
    }

    if (empty($errors)) {
        try {
            $db = new Database();
            $pdo = $db->connect();

            $user = new Utilisateur($pdo);

            if ($user->login($email, $password)) {

                // Important: vérifier que l'utilisateur a choisi le bon rôle
                if ($user->getRole() !== $role) {
                    $errors[] = "Type de compte incorrect (coach/sportif).";
                } else {
                    // Stocker infos utiles en session
                    $_SESSION["user_id"] = $user->getId();
                    $_SESSION["nom"] = $user->getNom();
                    $_SESSION["prenom"] = $user->getPrenom();
                    $_SESSION["email"] = $user->getEmail();
                    $_SESSION["role"] = $user->getRole();

                    // Redirection vers le bon dashboard
                    if ($user->getRole() === "coach") {
                        header("Location: dashboard/dashboard_coach.php");
                        exit;
                    } else {
                        header("Location: dashboard/dashboard_sportif.php");
                        exit;
                    }
                }

            } else {
                $errors[] = "Email ou mot de passe incorrect.";
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
    <title>Connexion - SportCoach</title>
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

    <!-- Login Form -->
    <div class="form-container">
        <div class="form-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace personnel</p>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div style="background: #fee2e2; border: 2px solid #dc2626; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; font-weight: 600;">
                <i class="fas fa-exclamation-triangle"></i>
                <ul style="margin: 10px 0 0 18px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Success (flash from register) -->
        <?php if ($success): ?>
            <div style="background: #d1fae5; border: 2px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- IMPORTANT: action="" so it posts to the same file -->
        <form id="loginForm" action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="votre@email.com"
                        required
                        value="<?= htmlspecialchars($old['email']) ?>"
                    >
                </div>
                <span class="error-message" id="emailError">Veuillez entrer un email valide</span>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>
                <span class="error-message" id="passwordError">Le mot de passe est requis</span>
            </div>

            <div class="form-group">
                <label for="userType">Type de compte</label>
                <div class="input-group">
                    <i class="fas fa-user-tag"></i>
                    <select name="userType" id="userType" class="form-control" required>
                        <option value="">Sélectionnez votre rôle</option>
                        <option value="sportif" <?= ($old["userType"] === "sportif") ? "selected" : "" ?>>Sportif</option>
                        <option value="coach" <?= ($old["userType"] === "coach") ? "selected" : "" ?>>Coach</option>
                    </select>
                </div>
                <span class="error-message" id="userTypeError">Veuillez sélectionner un type de compte</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="rememberMe">
                    <span style="font-size: 14px; color: var(--text-gray);">Se souvenir de moi</span>
                </label>
                <a href="#" style="font-size: 14px; color: var(--primary-gold); font-weight: 600;">Mot de passe oublié ?</a>
            </div>

            <button type="submit" name="login" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="form-footer">
            <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
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

</body>
</html>

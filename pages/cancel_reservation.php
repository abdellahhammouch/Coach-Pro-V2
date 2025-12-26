<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sportif') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Dashboards/dashboard_sportif.php");
    exit();
}

require_once __DIR__ . "../../config/database.php";

$db = new Database();
$pdo = $db->connect();

$sportif_id = $_SESSION['user_id'];
$reservation_id = $_POST['reservation_id'] ?? null;

if (!$reservation_id) {
    $_SESSION['error'] = "Réservation invalide.";
    header("Location: Dashboards/dashboard_sportif.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 1) get reservation + seance_id (must belong to this sportif)
    $stmt = $pdo->prepare("SELECT id_reservation, seance_id 
                            FROM reservations 
                            WHERE id_reservation = ? AND sportif_id = ? 
                            AND statut_reservation = 'active'
                            LIMIT 1");
    $stmt->execute([$reservation_id, $sportif_id]);
    $res = $stmt->fetch();

    if (!$res) {
        $pdo->rollBack();
        $_SESSION['error'] = "Impossible d'annuler (réservation introuvable ou déjà annulée).";
        header("Location: Dashboards/dashboard_sportif.php");
        exit();
    }

    $seance_id = $res['seance_id'];

    $upRes = $pdo->prepare("UPDATE reservations 
                            SET statut_reservation = 'annulee' 
                            WHERE id_reservation = ?");
    $upRes->execute([$reservation_id]);

    $upSeance = $pdo->prepare("UPDATE seances 
                                SET statut_seance = 'disponible' 
                                WHERE id_seance = ?");
    $upSeance->execute([$seance_id]);

    $pdo->commit();

    $_SESSION['success'] = "Réservation annulée avec succès.";
    header("Location: Dashboards/dashboard_sportif.php");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header("Location: Dashboards/dashboard_sportif.php");
    exit();
}

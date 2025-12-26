<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sportif') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../classes/Seance.php";
require_once __DIR__ . "/../../classes/Reservation.php";

$seanceId  = (int)($_POST['seance_id'] ?? 0);
$sportifId = (int)$_SESSION['user_id'];

if ($seanceId <= 0) {
    $_SESSION['error'] = "Séance invalide.";
    header("Location: dashboard_sportif.php");
    exit;
}

$db  = new Database();
$pdo = $db->connect();

$seanceModel = new Seance($pdo);
$resModel    = new Reservation($pdo);

try {
    $pdo->beginTransaction();

    $seance = $seanceModel->getById($seanceId);

    if (!$seance) {
        throw new Exception("Séance introuvable.");
    }

    if ($seance['statut_seance'] !== 'disponible') {
        throw new Exception("Cette séance est déjà réservée.");
    }

    $resModel->create($seanceId, $sportifId);

    $seanceModel->markReserved($seanceId);

    $pdo->commit();

    $_SESSION['success'] = "Réservation effectuée avec succès ✅";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: dashboard_sportif.php");
exit;
?>
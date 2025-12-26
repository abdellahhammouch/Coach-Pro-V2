<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/Utilisateur.php";

class Coach extends Utilisateur
{
    protected string $discipline_coach = "";
    protected int $experiences_coach = 0;
    protected string $description_coach = "";

    public function getDiscipline(): string { return $this->discipline_coach; }
    public function getExperience(): int { return $this->experiences_coach; }
    public function getDescription(): string { return $this->description_coach; }

    // Charger le profil coach depuis la table coachs
    public function loadCoachProfile(int $id_user): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM coachs WHERE id_user = ?");
        $stmt->execute([$id_user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $this->discipline_coach = $row["discipline_coach"] ?? "";
        $this->experiences_coach = (int)($row["experiences_coach"] ?? 0);
        $this->description_coach = $row["description_coach"] ?? "";

        return true;
    }

    // Modifier profil coach (utile après connexion)
    public function updateCoachProfile(string $discipline, int $experience, string $description): bool
    {
        if ($this->id_user === null) return false;

        $stmt = $this->pdo->prepare("
            UPDATE coachs
            SET discipline_coach = ?, experiences_coach = ?, description_coach = ?
            WHERE id_user = ?
        ");

        return $stmt->execute([trim($discipline), $experience, trim($description), $this->id_user]);
    }
}
?>
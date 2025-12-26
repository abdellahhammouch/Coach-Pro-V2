<?php

class Seance
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $coachId, string $date, string $heure, int $duree): bool
    {
        $sql = "INSERT INTO seances (coach_id, date_seance, heure_seance, duree_senace, statut_seance)
                VALUES (?, ?, ?, ?, 'disponible')";
        return $this->pdo->prepare($sql)->execute([$coachId, $date, $heure, $duree]);
    }

    public function getById(int $idSeance): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM seances 
                                    WHERE id_seance = ?");
        $stmt->execute([$idSeance]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAvailableByCoach(int $coachId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM seances
                                    WHERE coach_id = ? AND statut_seance = 'disponible'
                                    ORDER BY date_seance ASC, heure_seance ASC");
        $stmt->execute([$coachId]);
        return $stmt->fetchAll();
    }

    public function markReserved(int $idSeance): bool
    {
        $stmt = $this->pdo->prepare("UPDATE seances SET statut_seance = 'reservee' 
                                    WHERE id_seance = ?");
        return $stmt->execute([$idSeance]);
    }

    public function markAvailable(int $idSeance): bool
    {
        $stmt = $this->pdo->prepare("UPDATE seances SET statut_seance = 'disponible' 
                                    WHERE id_seance = ?");
        return $stmt->execute([$idSeance]);
    }

    public function getByCoach(int $coachId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM seances
                                    WHERE coach_id = ?
                                    ORDER BY date_seance DESC, heure_seance DESC");
        $stmt->execute([$coachId]);
        return $stmt->fetchAll();
    }
    public function delete(int $id_seance, int $coach_id): bool
    {
        $sql = "DELETE FROM seances 
                WHERE id_seance = ? AND coach_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_seance, $coach_id]);
    }


}

?>
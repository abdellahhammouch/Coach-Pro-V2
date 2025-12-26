<?php

class Reservation
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $seanceId, int $sportifId): bool
    {
        $sql = "INSERT INTO reservations (seance_id, sportif_id, statut_reservation)
                VALUES (?, ?, 'active')";
        return $this->pdo->prepare($sql)->execute([$seanceId, $sportifId]);
    }

    public function getBySportif(int $sportifId): array
    {
        $sql = "SELECT  r.id_reservation, r.created_at,
                        s.id_seance, s.date_seance, s.heure_seance, s.duree_senace, s.statut_seance,
                        u.nom_user AS coach_nom, u.prenom_user AS coach_prenom
                FROM reservations r
                INNER JOIN seances s ON s.id_seance = r.seance_id
                INNER JOIN users u ON u.id_user = s.coach_id
                WHERE r.sportif_id = ? AND r.statut_reservation = 'active'
                ORDER BY s.date_seance DESC, s.heure_seance DESC
            ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sportifId]);
        return $stmt->fetchAll();
    }

    public function getByCoach(int $coachId): array
    {
        $sql = "SELECT  r.id_reservation, r.created_at,
                        s.id_seance, s.date_seance, s.heure_seance, s.duree_senace,
                        u.nom_user AS sportif_nom, u.prenom_user AS sportif_prenom
                FROM reservations r
                INNER JOIN seances s ON s.id_seance = r.seance_id
                INNER JOIN users u ON u.id_user = r.sportif_id
                WHERE s.coach_id = ? AND r.statut_reservation = 'active'
                ORDER BY s.date_seance DESC, s.heure_seance DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coachId]);
        return $stmt->fetchAll();
    }
}
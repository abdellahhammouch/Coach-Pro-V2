<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Utilisateur
{
    protected ?int $id_user = null;
    protected string $nom_user = "";
    protected string $prenom_user = "";
    protected string $email_user = "";
    protected string $role_user = "";

    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getId(): ?int { return $this->id_user; }
    public function getNom(): string { return $this->nom_user; }
    public function getPrenom(): string { return $this->prenom_user; }
    public function getEmail(): string { return $this->email_user; }
    public function getRole(): string { return $this->role_user; }

    public function register(string $nom,string $prenom,string $email,string $password,string $role,?string $discipline = null,?int $experience = null,?string $biographie = null): bool {
        
        $nom = trim($nom);
        $prenom = trim($prenom);
        $email = trim($email);

        if ($nom === "" || $prenom === "" || $email === "" || $password === "" || $role === "") return false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        if ($role !== "coach" && $role !== "sportif") return false;

        $check = $this->pdo->prepare("SELECT id_user FROM users 
                                        WHERE email_user = ?");
        $check->execute([$email]);
        if ($check->fetch()) return false;

        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO users (nom_user, prenom_user, email_user, role_user, password_user)
                                            VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([$nom, $prenom, $email, $role, $hash]);

            $newId = (int)$this->pdo->lastInsertId();

            if ($role === "coach") {
                $stmtCoach = $this->pdo->prepare("INSERT INTO coachs (id_user, discipline_coach, experiences_coach, description_coach)
                                                    VALUES (?, ?, ?, ?)");
                $stmtCoach->execute([
                    $newId,
                    $discipline,
                    $experience,
                    $biographie
                ]);
            } else {
                $stmtSportif = $this->pdo->prepare("INSERT INTO sportifs (id_user) 
                                                    VALUES (?)");
                $stmtSportif->execute([$newId]);
            }

            $this->pdo->commit();

            $this->id_user = $newId;
            $this->nom_user = $nom;
            $this->prenom_user = $prenom;
            $this->email_user = $email;
            $this->role_user = $role;

            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function login(string $email, string $password): bool
    {
        $email = trim($email);
        if ($email === "" || $password === "") return false;

        $stmt = $this->pdo->prepare("SELECT * FROM users 
                                    WHERE email_user = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;
        if (!password_verify($password, $user["password_user"])) return false;

        $this->id_user = $user["id_user"];
        $this->nom_user = $user["nom_user"] ?? "";
        $this->prenom_user = $user["prenom_user"] ?? "";
        $this->email_user = $user["email_user"] ?? "";
        $this->role_user = $user["role_user"] ?? "";

        return true;
    }
}
?>
<?php
    require_once __DIR__ . "/../config/database.php";
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


    public function register(string $nom, string $prenom, string $email, string $password, string $role): bool
    {
        $nom = trim($nom);
        $prenom = trim($prenom);
        $email = trim($email);
        $role = trim($role);

        if ($nom === "" || $prenom === "" || $email === "" || $password === "" || $role === "") {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($role !== "coach" && $role !== "sportif") {
            return false;
        }

        $check = $this->pdo->prepare("select id_user from users where email_user = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            insert into users (nom_user, prenom_user, email_user, role_user, password_user)
            values (?, ?, ?, ?, ?)
        ");

        $ok = $stmt->execute([$nom, $prenom, $email, $role, $hash]);

        if ($ok) {
            $this->id_user = (int)$this->pdo->lastInsertId();
            $this->nom_user = $nom;
            $this->prenom_user = $prenom;
            $this->email_user = $email;
            $this->role_user = $role;
            return true;
        }

        return false;
    }

    public function login(string $email, string $password): bool
    {
        $email = trim($email);
        if ($email === "" || $password === "") {
            return false;
        }

        $stmt = $this->pdo->prepare("select * from users where email_user = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user["password_user"])) {
            return false;
        }

        $this->id_user = (int)$user["id_user"];
        $this->nom_user = $user["nom_user"] ?? "";
        $this->prenom_user = $user["prenom_user"] ?? "";
        $this->email_user = $user["email_user"] ?? "";
        $this->role_user = $user["role_user"] ?? "";

        return true;
    }
}

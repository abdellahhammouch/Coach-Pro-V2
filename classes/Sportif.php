<?php
require_once "/../config/database.php";
require_once "Utilisateur.php";

$db = new Database();
$pdo = $db->connect();

$user = new Utilisateur($pdo);

?>
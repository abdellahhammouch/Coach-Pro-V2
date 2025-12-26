<?php
session_start();
$_SESSION = [];
session_destroy();

// redirection (même dossier => login.php)
header("Location: login.php");
exit();
?>
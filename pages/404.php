<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 - Page introuvable</title>
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f7f7f7;">
    <div style="background:white;padding:30px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.08);max-width:600px;width:100%;text-align:center;">
    <h1 style="margin:0;font-size:60px;">404</h1>
    <p style="color:#666;margin:10px 0 20px;">Page introuvable</p>
    <a href="/pages/login.php" style="display:inline-block;padding:12px 18px;border-radius:10px;background:#111;color:#fff;text-decoration:none;">
        Retour à la connexion
    </a>
    </div>
</body>
</html>

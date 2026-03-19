<?php
// Rediriger si déjà connecté
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Connexion — Belgica FC 3</title>
<link rel="stylesheet" href="../css/compte.css">
</head>
<body>
<div class="auth-layout">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">
                <svg viewBox="0 0 54 54" fill="none">
                    <path d="M27 2L50 11L50 31C50 43 27 52 27 52C27 52 4 43 4 31L4 11Z" fill="#8B261F" stroke="#6b1a14" stroke-width="2"/>
                    <path d="M27 7L45 15L45 31C45 40 27 50 27 50C27 50 9 40 9 31L9 15Z" fill="#0d0d1a"/>
                    <text x="50%" y="42%" dominant-baseline="middle" text-anchor="middle" font-family="Arial,sans-serif" font-size="8" fill="#e0a800" font-weight="bold" letter-spacing="1">BFC</text>
                    <text x="50%" y="60%" dominant-baseline="middle" text-anchor="middle" font-family="Arial,sans-serif" font-size="7" fill="#fff" letter-spacing="1">3</text>
                </svg>
            </div>
            <div class="brand-name">Belgica FC 3</div>
            <div class="brand-mat">Club de Football</div>
            <div class="brand-sub">Espace membre</div>
            <p class="brand-tagline">Connectez-vous pour accéder à votre espace et suivre toute l'actualité du club.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="auth-form-title">Connexion</div>
        <p class="auth-form-sub">Accédez à votre espace membre</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg">❌ Nom d'utilisateur ou mot de passe incorrect.</div>
        <?php endif; ?>

        <form action="../php/login.php" method="POST" class="auth-form">
            <div class="auth-form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" placeholder="votre_pseudo" required autocomplete="username">
            </div>
            <div class="auth-form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-auth">🔐 Se connecter</button>
        </form>

        <div class="auth-divider"><span>ou</span></div>
        <p class="auth-link">Pas encore de compte ? <a href="register.html">Créer un compte →</a></p>

        <div style="margin-top:28px;text-align:center;">
            <a href="../../../index.php"
               style="font-size:.8rem;color:rgba(255,255,255,.25);text-decoration:none;transition:color .2s;"
               onmouseover="this.style.color='rgba(255,255,255,.6)'"
               onmouseout="this.style.color='rgba(255,255,255,.25)'">
                ← Retour au site
            </a>
        </div>
    </div>
</div>
</body>
</html>

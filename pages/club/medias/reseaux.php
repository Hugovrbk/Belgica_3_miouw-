<?php
ini_set("display_errors", 0);
session_start();
require_once '../../../db.php';
require_once '../../../includes/cms_render.php';
// CMS content loader
$page_slug = basename($_SERVER['PHP_SELF'], '.php');
$cms_r = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM page_content WHERE slug=? LIMIT 1");
    $stmt->execute([$page_slug]);
    $cms_r = $stmt->fetch();
} catch(PDOException $e) {}

$root          = '../../../';
$active_folder = 'medias';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réseaux sociaux — RFC Liège</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Barlow+Condensed:wght@400;600;700;900&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Barlow+Condensed:wght@400;600;700;900&display=swap"></noscript>
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/page.css">
    <link rel="stylesheet" href="../../../css/animations.css">
</head>
<body class="inner-page">

<!-- HEADER -->
<header class="header">
    <a href="../../../index.php" class="logo-wrap">
        <div class="logo-img">
            <img src="../../../multimedia/img/logo/rfcl-logo.png" alt="RFC Liège" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div style="display:none;width:48px;height:48px;background:var(--rouge);border-radius:50%;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:#fff;font-weight:900;">RFCL</div>
        </div>
        <div class="logo-text">
            <span class="club-name">RFC Liège</span>
            <span class="club-sub">Matricule 4 · 1892</span>
        </div>
    </a>
    <?php include $root . 'includes/nav.php'; ?>
    <div style="display:flex;align-items:center;gap:10px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="font-size:0.85rem;color:rgba(255,255,255,0.6);">👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if (isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1): ?>
                <a href="../../../pages/admin/html/admin.php" class="btn-compte" style="background:var(--or);color:var(--navy);">Admin</a>
            <?php endif; ?>
            <a href="../../../pages/compte/php/logout.php" class="btn-compte" style="background:transparent;border:1px solid rgba(255,255,255,0.3);">Déconnexion</a>
        <?php else: ?>
            <a href="../../../pages/compte/html/login.php" class="btn-compte">Connexion</a>
        <?php endif; ?>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</header>

<nav class="nav-mobile" id="navMobile"></nav>

<!-- BANNER -->
<section class="page-banner">
    <img src="../../../multimedia/img/hero/herobg.jpg" alt="" class="page-banner-img" aria-hidden="true">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <div class="page-breadcrumb">
            <a href="../../../index.php">Accueil</a>
            <span class="breadcrumb-sep">›</span>
            <a href="./accreditation.php">MEDIAS</a>
            <span class="breadcrumb-sep">›</span>
            <span>Réseaux sociaux</span>
        </div>
        <h1 class="page-h1">Réseaux sociaux</h1>
        <p class="page-desc">Suivez le RFC Liège sur tous ses réseaux sociaux officiels.</p>
    </div>
</section>

<!-- CONTENT -->
<?php if($cms_r&&$cms_r["contenu"]): ?><main class="page-main"><div class="page-main-inner cms-content"><?= cms_render($cms_r["contenu"]) ?></div></main><?php else: ?><main class="page-main">
    <div class="page-main-inner">

        <section class="content-section">
            <h2 class="content-h2">Nos réseaux officiels</h2>
            <div class="content-text"><p>Suivez le RFC Liège en temps réel sur tous nos réseaux sociaux officiels. Résultats, behind-the-scenes, lives de matchs, interviews — toute l'actualité sang et marine directement dans votre fil d'actu.</p></div>
        </section>
        <section class="content-section">
            <h2 class="content-h2">Plateformes</h2>
            <div class="content-grid-2">
                <a href="https://www.instagram.com" target="_blank" class="content-card" style="display:flex;align-items:center;gap:16px;padding:22px;text-decoration:none;">
                    <div style="width:56px;height:56px;background:linear-gradient(135deg,#E1306C,#C13584,#833AB4);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;flex-shrink:0;">📷</div>
                    <div><div style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;color:var(--navy);">Instagram</div><div style="font-size:0.82rem;color:#888;margin-top:2px;">@rfcliege_officiel · Photos & Stories</div></div>
                </a>
                <a href="https://www.facebook.com" target="_blank" class="content-card" style="display:flex;align-items:center;gap:16px;padding:22px;text-decoration:none;">
                    <div style="width:56px;height:56px;background:#1877F2;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;flex-shrink:0;">f</div>
                    <div><div style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;color:var(--navy);">Facebook</div><div style="font-size:0.82rem;color:#888;margin-top:2px;">RFC Liège Officiel · Actualités & Events</div></div>
                </a>
                <a href="https://www.tiktok.com" target="_blank" class="content-card" style="display:flex;align-items:center;gap:16px;padding:22px;text-decoration:none;">
                    <div style="width:56px;height:56px;background:#000;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;flex-shrink:0;">🎵</div>
                    <div><div style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;color:var(--navy);">TikTok</div><div style="font-size:0.82rem;color:#888;margin-top:2px;">@rfcliege · Vidéos & Coulisses</div></div>
                </a>
                <a href="https://x.com" target="_blank" class="content-card" style="display:flex;align-items:center;gap:16px;padding:22px;text-decoration:none;">
                    <div style="width:56px;height:56px;background:#000;border-radius:12px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:#fff;flex-shrink:0;">𝕏</div>
                    <div><div style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;font-weight:700;color:var(--navy);">X (Twitter)</div><div style="font-size:0.82rem;color:#888;margin-top:2px;">@RFCLiege · Live matchs & News</div></div>
                </a>
            </div>
        </section>

    </div>
</main>
<?php endif; ?>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="club-name">RFC Liège</div>
            <p>Fondé en 1892, le Royal Football Club de Liège (Matricule 4) est le doyen du football belge. Sang et Marine depuis toujours.</p>
            <div class="footer-social">
                <a href="https://www.instagram.com" class="social-btn" aria-label="Instagram">IG</a>
                <a href="https://www.facebook.com" class="social-btn" aria-label="Facebook">f</a>
                <a href="https://www.tiktok.com" class="social-btn" aria-label="TikTok">🎵</a>
                <a href="https://x.com" class="social-btn" aria-label="Twitter">𝕏</a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Club</h4>
            <ul>
                <li><a href="../club/histoire.php">Histoire</a></li>
                <li><a href="../club/organigramme.php">Organigramme</a></li>
                <li><a href="../club/stade.php">Stade de Rocourt</a></li>
                <li><a href="../business/partenaires.php">Partenaires</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Équipe</h4>
            <ul>
                <li><a href="../d1b/joueurs.php">Joueurs</a></li>
                <li><a href="../d1b/classement.php">Classement D1B</a></li>
                <li><a href="../dames/equipe-d1.php">Section Dames</a></li>
                <li><a href="../edj/equipe-u21.php">Académie</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Supporters</h4>
            <ul>
                <li><a href="../fans/fanshop.php">Fanshop</a></li>
                <li><a href="../fans/billetterie.php">Billetterie</a></li>
                <li><a href="../medias/reseaux.php">Réseaux sociaux</a></li>
                <li><a href="../../../pages/compte/html/login.php">Espace membre</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 RFC Liège — Tous droits réservés</p>
        <div style="display:flex;gap:20px;">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="../../../pages/admin/html/admin.php">Admin</a>
        </div>
    </div>
</footer>

<script>
document.getElementById('hamburger').addEventListener('click', function() {
    this.classList.toggle('open');
    document.getElementById('navMobile').classList.toggle('open');
});
</script>
<script src="../../../js/animations.js"></script>
<?php if(isset($_SESSION['user_admin'])&&$_SESSION['user_admin']==1): include $root.'includes/inline_edit.php'; endif; ?>
</body>
</html>



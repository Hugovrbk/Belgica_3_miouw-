<?php
/**
 * Header partagé — Belgica FC 3
 * Variables requises (à définir AVANT l'include) :
 *   $root        : chemin relatif vers la racine  (ex: '' depuis index.php, '../../../' depuis pages/x/html/)
 *   $active_page : identifiant de la page active  (ex: 'home', 'joueurs', 'calendrier', 'historique')
 */
$root        = $root        ?? '';
$active_page = $active_page ?? '';

$nav_items = [
    'home'       => ['label' => 'Accueil',    'url' => $root . 'index.php'],
    'joueurs'    => ['label' => 'Joueurs',    'url' => $root . 'pages/joueurs/html/joueurs.php'],
    'calendrier' => ['label' => 'Calendrier', 'url' => $root . 'pages/calendrier/html/calendrier.php'],
    'actualites' => ['label' => 'News',       'url' => $root . 'index.php#section-actualites'],
    'historique' => ['label' => 'Histoire',   'url' => $root . 'pages/historique/html/historique.php'],
];
?>
<!-- Loader -->
<div id="page-loader">
    <div class="loader-ball"></div>
    <div class="loader-ball"></div>
    <div class="loader-ball"></div>
</div>

<!-- ══ HEADER ══════════════════════════════════════════════ -->
<div class="header" id="site-header">
    <!-- Logo -->
    <a href="<?= $root ?>index.php" class="logo-link" style="text-decoration:none;display:flex;align-items:center;gap:0.5rem;">
        <div class="logo"></div>
    </a>

    <!-- Navigation principale -->
    <nav class="navigation" id="main-nav">
        <?php foreach ($nav_items as $key => $item): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>"
               class="nav-btn<?= $active_page === $key ? ' nav-btn--active' : '' ?>">
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Zone compte -->
    <div class="compte" style="display:flex;align-items:center;gap:0.6rem;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="font-size:0.82rem;color:#aaa;">👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if (isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1): ?>
                <a href="<?= $root ?>pages/admin/html/admin.php" class="nav-btn btn-admin"
                   style="background:linear-gradient(135deg,#8B261F,#c0392b);color:#fff;padding:0.35rem 0.9rem;border-radius:20px;font-size:0.82rem;">
                    ⚙️ Admin
                </a>
            <?php endif; ?>
            <a href="<?= $root ?>pages/compte/php/logout.php" class="nav-btn btn-compte">Déconnexion</a>
        <?php else: ?>
            <a href="<?= $root ?>pages/compte/html/login.php" class="nav-btn btn-compte">Connexion</a>
        <?php endif; ?>

        <!-- Burger mobile -->
        <button class="burger-btn" id="burger-btn" aria-label="Menu" style="display:none">
            <span></span><span></span><span></span>
        </button>
    </div>
</div>

<!-- Overlay mobile -->
<div id="nav-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;" onclick="closeNav()"></div>

<style>
/* ── Nav active ──────────────────────────────────────────── */
.nav-btn--active {
    color: #e0a800 !important;
    font-weight: 700;
}
.nav-btn--active::after {
    width: 100% !important;
    left: 0 !important;
}

/* ── Burger responsive ────────────────────────────────────── */
.burger-btn {
    background: none; border: 1px solid rgba(255,255,255,0.2);
    border-radius: 6px; padding: 0.4rem 0.5rem; cursor: pointer;
    display: flex; flex-direction: column; gap: 4px;
}
.burger-btn span {
    display: block; width: 20px; height: 2px;
    background: #fff; border-radius: 2px;
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.burger-btn.open span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
.burger-btn.open span:nth-child(2) { opacity: 0; }
.burger-btn.open span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

/* ── Mobile nav drawer ────────────────────────────────────── */
@media (max-width: 700px) {
    .burger-btn { display: flex !important; }
    #main-nav {
        position: fixed; top: 0; right: -260px; height: 100vh;
        width: 240px; background: #13131f;
        border-left: 1px solid rgba(255,255,255,0.07);
        flex-direction: column; justify-content: flex-start;
        padding: 4rem 0 2rem; gap: 0; z-index: 100;
        transition: right 0.3s ease;
    }
    #main-nav.open { right: 0; }
    #main-nav .nav-btn {
        display: block; padding: 0.9rem 1.5rem;
        font-size: 1rem; border-left: 3px solid transparent;
        width: 100%; box-sizing: border-box;
    }
    #main-nav .nav-btn--active, #main-nav .nav-btn:hover {
        border-left-color: #e0a800; background: rgba(224,168,0,0.08);
    }
}
</style>

<script>
function openNav() {
    document.getElementById('main-nav').classList.add('open');
    document.getElementById('nav-overlay').style.display = 'block';
    document.getElementById('burger-btn').classList.add('open');
}
function closeNav() {
    document.getElementById('main-nav').classList.remove('open');
    document.getElementById('nav-overlay').style.display = 'none';
    document.getElementById('burger-btn').classList.remove('open');
}
document.getElementById('burger-btn').addEventListener('click', function() {
    this.classList.contains('open') ? closeNav() : openNav();
});
// Fermer sur clic d'un lien mobile
document.querySelectorAll('#main-nav .nav-btn').forEach(l => l.addEventListener('click', closeNav));
</script>

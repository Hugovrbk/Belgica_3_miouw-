<?php
/**
 * includes/nav.php — Navigation déroulante partagée RFC Liège
 *
 * Variables requises avant l'include :
 *   $pdo          — connexion PDO
 *   $root         — chemin vers la racine (ex: '../../../' ou '' depuis index.php)
 *   $active_folder — dossier actif pour surligner le bon onglet (ex: 'actu', 'club')
 *                    Laisser vide depuis index.php
 */

// Ordre des catégories
$nav_order = ['ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'];

// Mapping catégorie → folder
$cat_folder = [
    'ACTU'     => 'actu',
    'CLUB'     => 'club',
    'D1B'      => 'd1b',
    'DAMES'    => 'dames',
    'EDJ'      => 'edj',
    'FANS'     => 'fans',
    'MÉDIAS'   => 'medias',
    'BUSINESS' => 'business',
    'TICKETS'  => 'tickets',
];

// Chargement des items depuis la DB
$nav_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM nav_pages WHERE active=1 ORDER BY FIELD(categorie,'ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'), position ASC");
    foreach ($stmt->fetchAll() as $row) {
        $nav_items[$row['categorie']][] = $row;
    }
} catch (PDOException $e) {
    // Fallback silencieux si la table n'existe pas encore
    $nav_items = [];
}

$active_folder = $active_folder ?? '';
?>
<!-- Appliquer le thème immédiatement (avant le rendu) pour éviter le flash -->
<script>
(function(){
    var t = localStorage.getItem('rfcl-theme') ||
            (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    if (t === 'light') document.documentElement.setAttribute('data-theme', 'light');
    else document.documentElement.removeAttribute('data-theme');
})();
</script>
<!-- NAV CSS (injecté une seule fois) -->
<?php if (!defined('RFCL_NAV_CSS')): define('RFCL_NAV_CSS', true); ?>
<style>
/* ── Dropdown Navigation ─────────────────────────────────────── */
.nav-desktop { display: flex; align-items: center; gap: 2px; }

.nav-item { position: relative; }

.nav-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 14px;
    font-family: 'Barlow Condensed', sans-serif;
    font-weight: 700;
    font-size: 0.82rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    border-radius: 6px;
    transition: color 0.18s, background 0.18s;
    white-space: nowrap;
    cursor: pointer;
    background: none;
    border: none;
}
.nav-btn:hover,
.nav-btn.active { color: #fff; background: rgba(200,16,46,0.18); }
.nav-btn.active { color: var(--rouge, #C8102E); }

/* Flèche */
.nav-arrow {
    font-size: 0.55rem;
    opacity: 0.6;
    transition: transform 0.2s;
    display: inline-block;
}
.nav-item:hover .nav-arrow,
.nav-item.open   .nav-arrow { transform: rotate(180deg); opacity: 1; }

/* Menu déroulant */
.nav-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%);
    min-width: 200px;
    background: #1A1F3A;
    border: 1px solid rgba(200,16,46,0.3);
    border-top: 3px solid var(--rouge, #C8102E);
    border-radius: 0 0 10px 10px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.5);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateX(-50%) translateY(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
    z-index: 999;
}
.nav-item:hover .nav-dropdown,
.nav-item.open   .nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.nav-dropdown a {
    display: block;
    padding: 9px 20px;
    font-family: 'Barlow', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    transition: color 0.15s, background 0.15s, padding-left 0.15s;
    border-left: 3px solid transparent;
}
.nav-dropdown a:hover {
    color: #fff;
    background: rgba(200,16,46,0.12);
    padding-left: 26px;
    border-left-color: var(--rouge, #C8102E);
}

/* Mobile dropdown */
.nav-mobile .nav-item { width: 100%; }
.nav-mobile .nav-btn  { width: 100%; justify-content: space-between; }
.nav-mobile .nav-dropdown {
    position: static;
    transform: none;
    opacity: 1;
    visibility: hidden;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, visibility 0.3s;
    border: none;
    border-top: none;
    border-left: 3px solid var(--rouge, #C8102E);
    border-radius: 0;
    box-shadow: none;
    background: rgba(0,0,0,0.2);
    margin-left: 12px;
    padding: 0;
}
.nav-mobile .nav-item.open .nav-dropdown {
    visibility: visible;
    max-height: 600px;
}
.nav-mobile .nav-dropdown a { padding: 8px 16px; font-size: 0.84rem; }
</style>
<?php endif; ?>

<!-- DESKTOP NAV -->
<nav class="nav-desktop">
<?php foreach ($nav_order as $cat):
    $folder = $cat_folder[$cat] ?? strtolower($cat);
    $pages  = $nav_items[$cat] ?? [];
    $is_active = ($active_folder === $folder);
    $first_slug = !empty($pages) ? $pages[0]['slug'] : 'index';
?>
    <div class="nav-item">
        <a href="<?= $root ?>pages/club/<?= $folder ?>/<?= $first_slug ?>.php"
           class="nav-btn<?= $is_active ? ' active' : '' ?>">
            <?= htmlspecialchars($cat) ?>
            <?php if (!empty($pages)): ?><span class="nav-arrow">▾</span><?php endif; ?>
        </a>
        <?php if (!empty($pages)): ?>
        <div class="nav-dropdown">
            <?php foreach ($pages as $p): ?>
            <a href="<?= $root ?>pages/club/<?= htmlspecialchars($p['folder']) ?>/<?= htmlspecialchars($p['slug']) ?>.php">
                <?= htmlspecialchars($p['label']) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</nav>

<!-- Bouton Thème Clair/Sombre -->
<button class="theme-toggle" id="theme-toggle-btn" aria-label="Basculer le thème" title="Mode clair/sombre">🌙</button>

<!-- MOBILE NAV (injecté dans nav#navMobile via JS) -->
<script>
(function(){
    var mobileNav = document.getElementById('navMobile');
    if (!mobileNav) return;
    // Vider et reconstruire
    mobileNav.innerHTML = '<a href="<?= $root ?>index.php" class="nav-btn">🏠 Accueil</a>';

    var items = <?= json_encode(array_map(function($cat) use ($cat_folder, $nav_items, $root) {
        $folder = $cat_folder[$cat] ?? strtolower($cat);
        $pages  = $nav_items[$cat] ?? [];
        return [
            'cat'    => $cat,
            'folder' => $folder,
            'pages'  => array_map(fn($p) => ['label'=>$p['label'],'folder'=>$p['folder'],'slug'=>$p['slug']], $pages),
        ];
    }, array_filter($nav_order, fn($c) => !empty($nav_items[$c])))) ?>;

    items.forEach(function(item){
        var wrap = document.createElement('div');
        wrap.className = 'nav-item';

        var btn = document.createElement('button');
        btn.className = 'nav-btn';
        btn.innerHTML = item.cat + (item.pages.length ? ' <span class="nav-arrow">▾</span>' : '');
        btn.addEventListener('click', function(){
            wrap.classList.toggle('open');
        });
        wrap.appendChild(btn);

        if (item.pages.length) {
            var dd = document.createElement('div');
            dd.className = 'nav-dropdown';
            item.pages.forEach(function(p){
                var a = document.createElement('a');
                a.href = '<?= $root ?>pages/club/' + p.folder + '/' + p.slug + '.php';
                a.textContent = p.label;
                dd.appendChild(a);
            });
            wrap.appendChild(dd);
        }
        mobileNav.appendChild(wrap);
    });

    // Lien connexion
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php if (isset($_SESSION['user_admin']) && $_SESSION['user_admin'] == 1): ?>
    var adminLink = document.createElement('a');
    adminLink.href = '<?= $root ?>pages/admin/html/admin.php';
    adminLink.className = 'btn-compte';
    adminLink.style = 'background:rgba(240,192,64,0.15);border:1px solid rgba(240,192,64,0.4);color:#F0C040;';
    adminLink.textContent = '⚙️ Admin';
    mobileNav.appendChild(adminLink);
    <?php endif; ?>
    var logoutLink = document.createElement('a');
    logoutLink.href = '<?= $root ?>pages/compte/php/logout.php';
    logoutLink.className = 'btn-compte';
    logoutLink.style = 'background:transparent;border:1px solid rgba(255,255,255,0.3);';
    logoutLink.textContent = 'Déconnexion';
    mobileNav.appendChild(logoutLink);
    <?php else: ?>
    var loginLink = document.createElement('a');
    loginLink.href = '<?= $root ?>pages/compte/html/login.php';
    loginLink.className = 'btn-compte';
    loginLink.textContent = 'Connexion';
    mobileNav.appendChild(loginLink);
    <?php endif; ?>

})();
</script>

<!-- Thème Clair / Sombre -->
<script>
(function() {
    var themeBtn = document.getElementById('theme-toggle-btn');
    if (!themeBtn) return;

    function applyTheme(theme) {
        if (theme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            themeBtn.textContent = '☀️';
            themeBtn.title = 'Passer en mode sombre';
        } else {
            document.documentElement.removeAttribute('data-theme');
            themeBtn.textContent = '🌙';
            themeBtn.title = 'Passer en mode clair';
        }
    }

    var currentTheme = localStorage.getItem('rfcl-theme') ||
        (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    applyTheme(currentTheme);

    themeBtn.addEventListener('click', function() {
        var next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        localStorage.setItem('rfcl-theme', next);
        applyTheme(next);
    });
})();
</script>


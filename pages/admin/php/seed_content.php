<?php
/**
 * seed_content.php — Migration unique
 * 1. Corrige le schéma de page_content (colonnes manquantes)
 * 2. Importe le contenu statique existant dans la DB pour chaque page
 *
 * Accès : /pages/admin/php/seed_content.php (session admin requise)
 */
session_start();
require_once '../../../db.php';
require_once '../../admin/html/check_admin.php';

$results = [];

// ── 1. Corriger le schéma ───────────────────────────────────────
try {
    // Créer la table si elle n'existe pas encore
    $pdo->exec("CREATE TABLE IF NOT EXISTS `page_content` (
        `id`         INT NOT NULL AUTO_INCREMENT,
        `slug`       VARCHAR(120) NOT NULL,
        `titre`      VARCHAR(200) DEFAULT NULL,
        `contenu`    MEDIUMTEXT DEFAULT NULL,
        `meta_desc`  VARCHAR(300) DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Ajouter les colonnes manquantes
    $cols = array_column($pdo->query("SHOW COLUMNS FROM page_content")->fetchAll(), 'Field');

    if (!in_array('titre', $cols)) {
        $pdo->exec("ALTER TABLE page_content ADD COLUMN titre VARCHAR(200) DEFAULT NULL AFTER slug");
        $results[] = ['✅', 'Colonne <code>titre</code> ajoutée'];
    } else {
        $results[] = ['ℹ️', 'Colonne <code>titre</code> déjà présente'];
    }
    if (!in_array('meta_desc', $cols)) {
        $pdo->exec("ALTER TABLE page_content ADD COLUMN meta_desc VARCHAR(300) DEFAULT NULL AFTER contenu");
        $results[] = ['✅', 'Colonne <code>meta_desc</code> ajoutée'];
    } else {
        $results[] = ['ℹ️', 'Colonne <code>meta_desc</code> déjà présente'];
    }
} catch (PDOException $e) {
    $results[] = ['❌', 'Erreur schéma : ' . $e->getMessage()];
}

// ── 2. Importer le contenu statique ────────────────────────────
$nav_pages = [];
try {
    $nav_pages = $pdo->query("SELECT * FROM nav_pages WHERE active=1 ORDER BY categorie, position")->fetchAll();
} catch (PDOException $e) {
    $results[] = ['❌', 'Impossible de lire nav_pages : ' . $e->getMessage()];
}

$seeded  = 0;
$skipped = 0;
$errors  = 0;

foreach ($nav_pages as $page) {
    $slug   = $page['slug'];
    $folder = $page['folder'];
    $label  = $page['label'];

    // Déjà dans la DB ? On skip
    $chk = $pdo->prepare("SELECT id FROM page_content WHERE slug=? LIMIT 1");
    $chk->execute([$slug]);
    if ($chk->fetchColumn()) {
        $skipped++;
        continue;
    }

    // Chemin du fichier PHP
    $file = realpath(__DIR__ . '/../../../pages/club/' . $folder . '/' . $slug . '.php');
    if (!$file || !file_exists($file)) {
        $results[] = ['⚠️', "Fichier introuvable : <code>pages/club/$folder/$slug.php</code>"];
        $errors++;
        continue;
    }

    $raw = file_get_contents($file);

    // Extraire le contenu statique (bloc du else jusqu'au endif)
    // Format généré par le script : <?php if($cms_r&&...): ?>...<?php else: ?><main>CONTENU</main><?php endif; ?>
    $static_html = '';

    // Essai 1 : extraire entre "else: ?><main" et "</main>" puis "endif"
    if (preg_match('/\?>\s*<main[^>]*>\s*(.*?)\s*<\/main>\s*<\?php\s*endif/s', $raw, $m)) {
        $static_html = $m[1];
    }
    // Essai 2 : prendre tout le <main>
    elseif (preg_match('/<main[^>]*>(.*?)<\/main>/s', $raw, $m)) {
        $static_html = $m[1];
    }

    // Nettoyer : enlever les tags PHP résiduels et les div inutiles
    $static_html = preg_replace('/<\?php.*?\?>/s', '', $static_html);
    $static_html = preg_replace('/^\s*<div class="page-main-inner">\s*/s', '', $static_html);
    $static_html = preg_replace('/\s*<\/div>\s*$/s', '', $static_html);
    $static_html = trim($static_html);

    if (empty($static_html)) {
        $skipped++;
        continue;
    }

    // Découper en sections (chaque <section class="content-section"> = un bloc)
    $sections = [];
    if (preg_match_all('/<section[^>]*class="content-section"[^>]*>(.*?)<\/section>/s', $static_html, $ms)) {
        foreach ($ms[1] as $sec_html) {
            // Extraire le titre (h2 content-h2)
            $titre_s = '';
            if (preg_match('/<h2[^>]*>(.*?)<\/h2>/s', $sec_html, $th)) {
                $titre_s = strip_tags($th[1]);
                $sec_html = preg_replace('/<h2[^>]*>.*?<\/h2>/s', '', $sec_html, 1);
            }
            // Extraire le contenu (div content-text ou tout le reste)
            $body = '';
            if (preg_match('/<div[^>]*class="content-text"[^>]*>(.*?)<\/div>/s', $sec_html, $tb)) {
                $body = trim($tb[1]);
            } else {
                $body = trim(preg_replace('/<h3[^>]*>.*?<\/h3>/s', '', $sec_html));
            }
            // Détecter type résultats (présence de content-grid)
            $type = (strpos($sec_html, 'content-grid') !== false || strpos($sec_html, 'content-card') !== false)
                    ? 'html_cards' : 'text';

            $sections[] = ['titre' => trim($titre_s), 'contenu' => trim($body), 'type' => 'text'];
        }
    }

    // Fallback : tout en un bloc
    if (empty($sections)) {
        $sections = [['titre' => $label, 'contenu' => $static_html, 'type' => 'text']];
    }

    $json = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    try {
        $pdo->prepare("INSERT INTO page_content (slug, titre, contenu, meta_desc) VALUES(?,?,?,?)")
            ->execute([$slug, $label, $json, '']);
        $seeded++;
    } catch (PDOException $e) {
        $results[] = ['❌', "Erreur INSERT <code>$slug</code> : " . $e->getMessage()];
        $errors++;
    }
}

$results[] = ['📊', "$seeded pages importées · $skipped déjà en DB · $errors erreurs"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Migration contenu — RFC Liège</title>
<style>
body { font-family:system-ui; background:#0f0f1a; color:#e8e8f0; padding:2rem; max-width:760px; margin:0 auto; }
h1   { color:#e0a800; margin-bottom:1.5rem; font-size:1.6rem; }
.row { display:flex; gap:12px; align-items:flex-start; background:#1a1a2e; border:1px solid rgba(255,255,255,.07); border-radius:8px; padding:.9rem 1.2rem; margin-bottom:.6rem; }
.row code { background:rgba(224,168,0,.1); padding:1px 5px; border-radius:4px; color:#e0a800; font-size:.85rem; }
.btn { display:inline-block; background:linear-gradient(135deg,#C8102E,#8B0000); color:#fff; padding:.7rem 1.8rem; border-radius:8px; text-decoration:none; font-weight:700; margin-top:1.2rem; font-size:.9rem; }
</style>
</head>
<body>
<h1>⚙️ Migration du contenu</h1>
<?php foreach ($results as [$icon, $msg]): ?>
<div class="row"><span style="font-size:1.2rem"><?= $icon ?></span><span><?= $msg ?></span></div>
<?php endforeach; ?>
<a href="../html/admin.php?tab=pages" class="btn">→ Aller à l'éditeur de pages</a>
</body>
</html>

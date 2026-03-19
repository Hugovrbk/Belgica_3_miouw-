<?php
session_start();
require_once '../../../db.php';
require_once '../../admin/html/check_admin.php';

// ── Correction schéma auto ──────────────────────────────────────
try {
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
    $cols = array_column($pdo->query("SHOW COLUMNS FROM page_content")->fetchAll(), 'Field');
    if (!in_array('titre', $cols)) $pdo->exec("ALTER TABLE page_content ADD COLUMN titre VARCHAR(200) DEFAULT NULL AFTER slug");
    if (!in_array('meta_desc', $cols)) $pdo->exec("ALTER TABLE page_content ADD COLUMN meta_desc VARCHAR(300) DEFAULT NULL AFTER contenu");
} catch (PDOException $e) {
    $_SESSION['nav_msg'] = "❌ Erreur schéma : " . $e->getMessage();
    header("Location: ../html/admin.php?tab=pages"); exit;
}

$slug  = trim($_POST['slug']     ?? '');
$titre = trim($_POST['titre']    ?? '');
$meta  = trim($_POST['meta_desc'] ?? '');

if (!$slug) {
    $_SESSION['nav_msg'] = "❌ Slug manquant.";
    header("Location: ../html/admin.php?tab=pages"); exit;
}

// Données des sections
$section_titres    = $_POST['section_titre']      ?? [];
$section_types     = $_POST['section_type']       ?? [];
$section_body      = $_POST['section_contenu']    ?? [];
$section_items_raw = $_POST['section_items_json'] ?? [];

// Types qui utilisent des items (pas de contenu texte)
$grid_types = ['player_grid','staff_grid','image_gallery','stats','schedule_block'];

// Tags HTML autorisés pour le texte libre
$allowed = '<p><br><b><strong><i><em><u><s><h2><h3><h4><ul><ol><li>'
         . '<a><span><div><table><thead><tbody><tr><th><td>'
         . '<blockquote><hr><small><sup><sub><code><pre><mark>';

$sections = [];
foreach ($section_titres as $i => $t) {
    $titre_s = trim($t);
    $type_s  = $section_types[$i] ?? 'text';
    $body_s  = trim($section_body[$i] ?? '');
    $items_s = $section_items_raw[$i] ?? '[]';

    $is_grid = in_array($type_s, $grid_types);
    $items_arr = [];
    if ($is_grid) {
        $decoded = json_decode($items_s, true);
        $items_arr = is_array($decoded) ? $decoded : [];
        // Saniter les valeurs des items
        foreach ($items_arr as &$item) {
            foreach ($item as $k => &$v) {
                $v = htmlspecialchars_decode(strip_tags((string)$v));
            }
        }
        unset($item, $v);
    }

    // Ignorer les blocs totalement vides
    if (!$titre_s && !$body_s && empty($items_arr)) continue;

    if ($is_grid) {
        $sections[] = ['titre' => $titre_s, 'type' => $type_s, 'contenu' => '', 'items' => $items_arr];
    } elseif ($type_s === 'resultats') {
        $sections[] = ['titre' => $titre_s, 'type' => $type_s, 'contenu' => htmlspecialchars_decode(strip_tags($body_s)), 'items' => []];
    } else {
        $sections[] = ['titre' => $titre_s, 'type' => $type_s, 'contenu' => strip_tags($body_s, $allowed), 'items' => []];
    }
}

$contenu_json = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

try {
    $row = $pdo->prepare("SELECT id FROM page_content WHERE slug = ? LIMIT 1");
    $row->execute([$slug]);
    $existing_id = $row->fetchColumn();

    if ($existing_id) {
        $pdo->prepare("UPDATE page_content SET titre=?, contenu=?, meta_desc=?, updated_at=NOW() WHERE id=?")
            ->execute([$titre, $contenu_json, $meta, $existing_id]);
    } else {
        $pdo->prepare("INSERT INTO page_content (slug, titre, contenu, meta_desc) VALUES(?,?,?,?)")
            ->execute([$slug, $titre, $contenu_json, $meta]);
    }

    $_SESSION['nav_msg'] = "✅ Page « " . htmlspecialchars($slug) . " » sauvegardée !";
} catch (PDOException $e) {
    $_SESSION['nav_msg'] = "❌ Erreur : " . $e->getMessage();
}

header("Location: ../html/admin.php?tab=pages#edit-" . urlencode($slug));
exit;

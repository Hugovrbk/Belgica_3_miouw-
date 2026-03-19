<?php
session_start();
require_once '../../../db.php';
require_once '../../admin/html/check_admin.php';

$flash = $_SESSION['nav_msg'] ?? $_SESSION['message'] ?? null;
unset($_SESSION['nav_msg'], $_SESSION['message'], $_SESSION['message_type']);

$tab = $_GET['tab'] ?? 'dashboard';

$matches   = $pdo->query("SELECT * FROM matches  ORDER BY date_match  ASC")->fetchAll();
$resultats = $pdo->query("SELECT * FROM resultats ORDER BY date_match DESC")->fetchAll();
$equipes   = $pdo->query("SELECT * FROM equipes  ORDER BY nom  ASC")->fetchAll();
$users     = $pdo->query("SELECT id,username,email,isadmin FROM user ORDER BY username ASC")->fetchAll();

try { $nb_users  = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(); } catch(PDOException $e){ $nb_users=0; }
try { $nb_admins = $pdo->query("SELECT COUNT(*) FROM user WHERE isadmin=1")->fetchColumn(); } catch(PDOException $e){ $nb_admins=0; }

// Classement calculé
$cl = [];
foreach ($equipes as $eq) $cl[$eq['nom']] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
foreach ($resultats as $r) {
    $d=$r['equipe_domicile']; $e=$r['equipe_exterieur'];
    $bd=$r['buts_domicile'];  $be=$r['buts_exterieur'];
    foreach([$d,$e] as $eq) if(!isset($cl[$eq])) $cl[$eq]=['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
    $cl[$d]['j']++; $cl[$e]['j']++;
    $cl[$d]['bp']+=$bd; $cl[$d]['bc']+=$be;
    $cl[$e]['bp']+=$be; $cl[$e]['bc']+=$bd;
    if($bd>$be){ $cl[$d]['v']++;$cl[$d]['pts']+=3;$cl[$e]['d']++; }
    elseif($bd==$be){ $cl[$d]['n']++;$cl[$d]['pts']++;$cl[$e]['n']++;$cl[$e]['pts']++; }
    else{ $cl[$e]['v']++;$cl[$e]['pts']+=3;$cl[$d]['d']++; }
}
foreach($cl as &$s) $s['diff']=$s['bp']-$s['bc']; unset($s);
uasort($cl,fn($a,$b)=>$b['pts']!=$a['pts']?$b['pts']-$a['pts']:($b['diff']!=$a['diff']?$b['diff']-$a['diff']:$b['bp']-$a['bp']));

// Navigation pages
$nav_order  = ['ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'];
$cat_folder = ['ACTU'=>'actu','CLUB'=>'club','D1B'=>'d1b','DAMES'=>'dames','EDJ'=>'edj','FANS'=>'fans','MÉDIAS'=>'medias','BUSINESS'=>'business','TICKETS'=>'tickets'];
$nav_all    = [];
try {
    $rows = $pdo->query("SELECT * FROM nav_pages ORDER BY FIELD(categorie,'ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'), position ASC")->fetchAll();
    foreach ($rows as $row) $nav_all[$row['categorie']][] = $row;
} catch(PDOException $e) { $nav_all = []; }

// CMS — contenu des pages
$page_contents = [];
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

    // Migration : ajouter colonnes manquantes si table existait déjà sans elles
    $cols = array_column($pdo->query("SHOW COLUMNS FROM page_content")->fetchAll(), 'Field');
    if (!in_array('titre', $cols)) {
        $pdo->exec("ALTER TABLE page_content ADD COLUMN titre VARCHAR(200) DEFAULT NULL AFTER slug");
    }
    if (!in_array('meta_desc', $cols)) {
        $pdo->exec("ALTER TABLE page_content ADD COLUMN meta_desc VARCHAR(300) DEFAULT NULL AFTER contenu");
    }

    $rows = $pdo->query("SELECT * FROM page_content")->fetchAll();
    foreach ($rows as $r) $page_contents[$r['slug']] = $r;
} catch(PDOException $e) { $page_contents = []; }

// ── Helpers CMS (définis UNE SEULE FOIS ici, avant tout HTML) ──
function cmsAddLabel($type) {
    $map = ['player_grid'=>'un joueur','staff_grid'=>'un membre',
            'image_gallery'=>'une photo','stats'=>'un chiffre','schedule_block'=>'un match'];
    return $map[$type] ?? 'un élément';
}

function cmsItemRowHtml($type, $num, $data = []) {
    $img      = htmlspecialchars($data['image']       ?? '');
    $nom      = htmlspecialchars($data['nom']         ?? '');
    $poste    = htmlspecialchars($data['poste']       ?? '');
    $role     = htmlspecialchars($data['role']        ?? '');
    $legende  = htmlspecialchars($data['legende']     ?? '');
    $numero   = htmlspecialchars($data['numero']      ?? '');
    $desc     = htmlspecialchars($data['desc']        ?? '');
    $valeur   = htmlspecialchars($data['valeur']      ?? '');
    $label    = htmlspecialchars($data['label']       ?? '');
    $icon     = htmlspecialchars($data['icon']        ?? '');
    $date     = htmlspecialchars($data['date']        ?? '');
    $heure    = htmlspecialchars($data['heure']       ?? '');
    $adv      = htmlspecialchars($data['adversaire']  ?? '');
    $lieu     = htmlspecialchars($data['lieu']        ?? '');
    $comp     = htmlspecialchars($data['competition'] ?? '');
    $show_img = in_array($type, ['player_grid','staff_grid','image_gallery']);

    $h  = '<div class="cms-item-row" data-idx="' . $num . '">';
    $h .= '<span class="cms-item-num">' . $num . '</span>';

    if ($show_img) {
        $h .= '<div class="cms-item-img-wrap" style="position:relative;flex-shrink:0;">'
            . '<img class="cms-item-img-preview" src="' . $img . '" alt="" onerror="this.src=\'\'" '
            . 'style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:2px solid #EEE;background:#F0F2F8;display:block;">'
            . '<label style="position:absolute;bottom:-4px;right:-4px;background:#C8102E;color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.6rem;" title="Upload">'
            . '&#128193;<input type="file" accept="image/*" style="display:none;" onchange="cmsUploadImg(this)"></label>'
            . '</div>'
            . '<input type="text" class="cms-item-field cms-item-image" placeholder="URL image" value="' . $img . '" '
            . 'oninput="this.closest(\'.cms-item-row\').querySelector(\'.cms-item-img-preview\').src=this.value">';
    }

    switch ($type) {
        case 'player_grid':
            $h .= '<input type="text" class="cms-item-field cms-item-nom" placeholder="Nom du joueur" value="' . $nom . '">'
                . '<input type="text" class="cms-item-field md cms-item-poste" placeholder="Poste" value="' . $poste . '">'
                . '<input type="text" class="cms-item-field sm cms-item-numero" placeholder="N°" value="' . $numero . '">';
            break;
        case 'staff_grid':
            $h .= '<input type="text" class="cms-item-field cms-item-nom" placeholder="Nom" value="' . $nom . '">'
                . '<input type="text" class="cms-item-field md cms-item-role" placeholder="Rôle" value="' . $role . '">'
                . '<input type="text" class="cms-item-field cms-item-desc" placeholder="Description" value="' . $desc . '">';
            break;
        case 'image_gallery':
            $h .= '<input type="text" class="cms-item-field cms-item-legende" placeholder="Légende (optionnelle)" value="' . $legende . '">';
            break;
        case 'stats':
            $h .= '<input type="text" class="cms-item-field cms-item-valeur" placeholder="Valeur (ex: 134)" value="' . $valeur . '">'
                . '<input type="text" class="cms-item-field cms-item-label" placeholder="Label" value="' . $label . '">'
                . '<input type="text" class="cms-item-field sm cms-item-icon" placeholder="Icône ⚽" value="' . $icon . '">';
            break;
        case 'schedule_block':
            $h .= '<input type="text" class="cms-item-field md cms-item-date" placeholder="Date ex: 21/03" value="' . $date . '">'
                . '<input type="text" class="cms-item-field sm cms-item-heure" placeholder="Heure" value="' . $heure . '">'
                . '<input type="text" class="cms-item-field cms-item-adversaire" placeholder="Adversaire" value="' . $adv . '">'
                . '<input type="text" class="cms-item-field md cms-item-lieu" placeholder="Lieu" value="' . $lieu . '">'
                . '<input type="text" class="cms-item-field md cms-item-competition" placeholder="Compétition" value="' . $comp . '">';
            break;
    }

    $h .= '<button type="button" class="cms-item-del" onclick="cmsRemoveItem(this)" title="Supprimer">&#10005;</button>';
    $h .= '</div>';
    return $h;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Administration — RFC Liège</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@400;700;900&display=swap">
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/admin_styles.css">
    <style>
    /* ═══════════════════════════════════════
       LAYOUT ADMIN
    ═══════════════════════════════════════ */
    body { background:#F4F4F6; color:#1A1F3A; font-family:'Barlow',sans-serif; }
    .adm-wrap { max-width:1200px; margin:0 auto; padding:24px 20px; }

    /* Header barre */
    .adm-header {
        background:#1A1F3A; color:#fff; padding:18px 28px;
        border-radius:12px; margin-bottom:22px;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 4px 16px rgba(0,0,0,0.15);
    }
    .adm-header h1 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:.06em; }

    /* Onglets */
    .adm-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:22px; }
    .adm-tab {
        padding:9px 18px; border-radius:8px;
        font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.86rem;
        letter-spacing:.1em; text-transform:uppercase; text-decoration:none;
        color:#1A1F3A; background:#fff; border:1px solid #DDD; transition:all .18s;
    }
    .adm-tab:hover { background:#C8102E; color:#fff; border-color:#C8102E; }
    .adm-tab.active { background:#C8102E; color:#fff; border-color:#C8102E; }

    /* Panneaux */
    .adm-panel { display:none; }
    .adm-panel.active { display:block; animation:fadeInUp .3s ease both; }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }

    /* Cartes */
    .adm-card {
        background:#fff; border-radius:12px; padding:24px;
        margin-bottom:18px; box-shadow:0 2px 10px rgba(0,0,0,.06);
        border:1px solid #EBEBEB;
    }
    .adm-card h2 {
        font-family:'Bebas Neue',sans-serif; font-size:1.35rem;
        color:#1A1F3A; margin-bottom:16px; letter-spacing:.06em;
        border-bottom:2px solid #C8102E; padding-bottom:8px;
    }

    /* Formulaires — FIX BLANC SUR BLANC */
    .adm-form { display:grid; gap:14px; }
    .adm-form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .adm-form label {
        font-size:.72rem; font-weight:900; letter-spacing:.14em;
        text-transform:uppercase; color:#666; display:block; margin-bottom:5px;
    }
    .adm-form input,
    .adm-form select,
    .adm-form textarea {
        width:100%; padding:10px 14px;
        border:1px solid #DDD; border-radius:8px;
        font-size:.94rem; font-family:'Barlow',sans-serif;
        transition:border .18s; box-sizing:border-box;
        background:#fff !important;
        color:#1A1F3A !important;
    }
    .adm-form input:focus,
    .adm-form select:focus,
    .adm-form textarea:focus {
        outline:none; border-color:#C8102E;
        box-shadow:0 0 0 3px rgba(200,16,46,.1);
    }
    .adm-form select option { background:#fff; color:#1A1F3A; }
    .adm-form input::placeholder,
    .adm-form textarea::placeholder { color:#aaa; }

    /* Boutons */
    .btn-adm {
        display:inline-flex; align-items:center; gap:6px;
        padding:9px 20px; border-radius:8px;
        font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.88rem;
        letter-spacing:.1em; text-transform:uppercase;
        border:none; cursor:pointer; transition:all .18s; text-decoration:none;
    }
    .btn-adm-primary { background:#C8102E; color:#fff; }
    .btn-adm-primary:hover { background:#e0002e; transform:translateY(-1px); }
    .btn-adm-danger  { background:#ff4444; color:#fff; font-size:.78rem; padding:6px 12px; }
    .btn-adm-danger:hover  { background:#cc0000; }
    .btn-adm-toggle  { background:#f0c040; color:#1A1F3A; font-size:.78rem; padding:6px 12px; }
    .btn-adm-edit    { background:#6B8EFF; color:#fff; font-size:.78rem; padding:6px 12px; }
    .btn-adm-view    { background:#1aaf5d; color:#fff; font-size:.78rem; padding:6px 12px; }

    /* Tables */
    .adm-table { width:100%; border-collapse:collapse; }
    .adm-table th {
        background:#1A1F3A; color:#fff; padding:10px 14px;
        font-family:'Barlow Condensed',sans-serif; font-size:.78rem;
        letter-spacing:.14em; text-transform:uppercase; text-align:left;
    }
    .adm-table td { padding:10px 14px; border-bottom:1px solid #EBEBEB; font-size:.9rem; vertical-align:middle; color:#1A1F3A; }
    .adm-table tr:hover td { background:#FFF5F5; }
    .badge-active   { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; background:#d4edda; color:#155724; }
    .badge-inactive { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; background:#f8d7da; color:#721c24; }

    /* Stats */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; }
    .stat-card {
        text-align:center; padding:22px 16px; background:#fff;
        border-radius:10px; border-top:4px solid #C8102E;
        box-shadow:0 2px 8px rgba(0,0,0,.05);
    }
    .stat-num { font-family:'Bebas Neue',sans-serif; font-size:2.4rem; color:#C8102E; line-height:1; }
    .stat-label { font-size:.78rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; color:#888; margin-top:6px; }

    /* Flash */
    .flash { padding:12px 18px; border-radius:8px; margin-bottom:18px; font-weight:600; background:#d4edda; color:#155724; border:1px solid #c3e6cb; }

    /* Nav cat */
    .drag-handle { cursor:grab; color:#CCC; padding:0 6px; font-size:1.1rem; }

    /* ═══════ CMS STYLES ═══════ */
    .cms-pages-list { display:flex; flex-direction:column; gap:0; }
    .cms-page-item {
        display:flex; align-items:center; gap:12px;
        padding:12px 16px; border-bottom:1px solid #EEE;
        cursor:pointer; list-style:none; user-select:none;
        transition:background .15s;
    }
    .cms-page-item:hover { background:#FFF5F5; }
    .cms-page-item .cms-label { font-weight:700; font-size:.9rem; color:#1A1F3A; flex:1; }
    .cms-page-item .cms-slug { font-family:monospace; font-size:.78rem; color:#C8102E; }
    .cms-page-item .cms-status { font-size:.75rem; padding:2px 8px; border-radius:12px; white-space:nowrap; }
    .cms-status-saved { background:#d4edda; color:#155724; }
    .cms-status-empty { background:#f8d7da; color:#721c24; }
    .cms-page-item .cms-arrow { color:#CCC; font-size:.8rem; transition:transform .2s; margin-left:auto; }
    details.cms-detail[open] > summary .cms-arrow { transform:rotate(180deg); }
    details.cms-detail > summary { list-style:none; }
    details.cms-detail > summary::-webkit-details-marker { display:none; }

    .cms-editor-body {
        padding:20px 16px 16px; border-top:2px solid #C8102E;
        background:#FAFAFA; border-radius:0 0 10px 10px;
        animation:fadeInUp .25s ease;
    }

    /* Toolbar WYSIWYG */
    .cms-toolbar {
        display:flex; gap:4px; flex-wrap:wrap;
        margin-bottom:8px; padding:8px;
        background:#F0F0F0; border-radius:8px 8px 0 0;
        border:1px solid #DDD; border-bottom:none;
    }
    .cms-toolbar button {
        padding:5px 10px; border:1px solid #CCC; border-radius:4px;
        background:#fff; color:#1A1F3A; font-size:.8rem; font-weight:700;
        cursor:pointer; transition:all .15s; font-family:'Barlow',sans-serif;
    }
    .cms-toolbar button:hover { background:#C8102E; color:#fff; border-color:#C8102E; }
    .cms-toolbar .sep { width:1px; background:#CCC; margin:4px 2px; }

    .cms-textarea {
        width:100%; min-height:220px; padding:12px 14px;
        border:1px solid #DDD; border-radius:0 0 8px 8px;
        font-family:'Barlow',sans-serif; font-size:.9rem; line-height:1.6;
        color:#1A1F3A !important; background:#fff !important;
        resize:vertical; box-sizing:border-box;
    }
    .cms-textarea:focus { outline:none; border-color:#C8102E; }

    .cms-preview {
        margin-top:12px; padding:16px; background:#fff;
        border:1px solid #EEE; border-radius:8px;
        font-size:.9rem; line-height:1.7; color:#333; display:none;
    }

    .cms-preview h2 { font-size:1.3rem; color:#1A1F3A; margin:12px 0 6px; }
    .cms-preview h3 { font-size:1.1rem; color:#1A1F3A; margin:10px 0 4px; }
    .cms-preview p  { margin-bottom:8px; }
    .cms-preview ul { padding-left:20px; margin-bottom:8px; }
    .cms-preview a  { color:#C8102E; }

    .cms-save-row { display:flex; gap:10px; align-items:center; margin-top:14px; flex-wrap:wrap; }

    /* ── Blocs de sections ─────────────────────────────────── */
    .cms-section-block {
        background:#fff; border:1.5px solid #E0E2EA; border-radius:12px;
        padding:16px; margin-bottom:12px;
        transition:border-color .2s, box-shadow .2s;
        position:relative;
    }
    .cms-section-block:hover { border-color:#C8102E; box-shadow:0 3px 16px rgba(200,16,46,.1); }
    .cms-section-hd {
        display:flex; align-items:center; gap:8px; margin-bottom:10px;
    }
    .cms-section-num {
        font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.8rem;
        letter-spacing:.08em; color:#888; text-transform:uppercase; flex-shrink:0;
    }
    .cms-type-select {
        border:1px solid #DDD; border-radius:6px; padding:5px 10px;
        font-size:.84rem; background:#F8F8F8 !important; color:#1A1F3A !important;
        font-family:'Barlow',sans-serif; cursor:pointer; flex:1;
    }
    .cms-type-select:focus { outline:none; border-color:#C8102E; }
    .cms-del-section {
        margin-left:auto; background:none; border:1px solid #EEE;
        color:#CCC; border-radius:6px; padding:4px 9px; cursor:pointer;
        font-size:.82rem; transition:all .15s; flex-shrink:0;
    }
    .cms-del-section:hover { background:#ff4444; color:#fff; border-color:#ff4444; }
    .cms-section-title-input {
        width:100%; padding:10px 14px; margin-bottom:10px;
        border:1px solid #DDD; border-radius:8px;
        font-family:'Barlow Condensed',sans-serif; font-size:1.05rem; font-weight:700;
        color:#1A1F3A !important; background:#fff !important;
        letter-spacing:.04em; box-sizing:border-box;
    }
    .cms-section-title-input:focus { outline:none; border-color:#C8102E; box-shadow:0 0 0 3px rgba(200,16,46,.08); }
    .cms-section-title-input::placeholder { color:#BBB; font-weight:400; }
    .cms-sections-wrap { display:flex; flex-direction:column; gap:0; }

    /* ── Éditeur d'items (joueurs, galerie, stats, etc.) ─── */
    .cms-items-header {
        display:flex; align-items:center; justify-content:space-between;
        background:#F0F2F8; border-radius:8px 8px 0 0;
        padding:8px 14px; font-size:.78rem; font-weight:700;
        letter-spacing:.1em; text-transform:uppercase; color:#555;
        border:1px solid #DDD; border-bottom:none;
    }
    .cms-items-list {
        border:1px solid #DDD; border-radius:0 0 8px 8px;
        overflow:hidden; margin-bottom:8px;
    }
    .cms-item-row {
        display:flex; align-items:center; gap:8px;
        padding:10px 12px; border-bottom:1px solid #F0F0F0;
        background:#fff; transition:background .15s;
    }
    .cms-item-row:last-child { border-bottom:none; }
    .cms-item-row:hover { background:#FFF5F5; }
    .cms-item-num {
        width:22px; height:22px; border-radius:50%;
        background:#1A1F3A; color:#fff;
        font-size:.7rem; font-weight:900;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; font-family:'Bebas Neue',sans-serif; font-size:.85rem;
    }
    .cms-item-img-wrap {
        position:relative; flex-shrink:0;
    }
    .cms-item-img-preview {
        width:44px; height:44px; border-radius:8px;
        object-fit:cover; border:2px solid #EEE;
        background:#F0F2F8; display:block;
        font-size:.6rem; overflow:hidden;
    }
    .cms-item-img-preview[src=""] { background:#F0F2F8; }
    .cms-item-upload-btn {
        position:absolute; bottom:-4px; right:-4px;
        background:#C8102E; color:#fff; border:none; border-radius:50%;
        width:18px; height:18px; font-size:.6rem; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        line-height:1; padding:0;
    }
    .cms-item-field {
        flex:1; padding:8px 10px; border:1px solid #E0E2EA; border-radius:6px;
        font-size:.86rem; font-family:'Barlow',sans-serif;
        color:#1A1F3A !important; background:#fff !important;
        min-width:60px;
    }
    .cms-item-field:focus { outline:none; border-color:#C8102E; }
    .cms-item-field.sm { flex:0 0 70px; }
    .cms-item-field.md { flex:0 0 140px; }
    .cms-item-del {
        background:none; border:1px solid #EEE; color:#CCC;
        border-radius:6px; padding:5px 8px; cursor:pointer; font-size:.8rem;
        transition:all .15s; flex-shrink:0;
    }
    .cms-item-del:hover { background:#ff4444; color:#fff; border-color:#ff4444; }
    .cms-add-item-btn {
        display:flex; align-items:center; justify-content:center; gap:6px;
        width:100%; padding:9px; background:#F0F2F8;
        border:1.5px dashed #CCC; border-radius:8px;
        font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.82rem;
        letter-spacing:.1em; text-transform:uppercase; color:#888;
        cursor:pointer; transition:all .15s;
    }
    .cms-add-item-btn:hover { background:#FFF0F0; border-color:#C8102E; color:#C8102E; }

    /* Tag type badge */
    .cms-type-badge {
        font-size:.68rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase;
        padding:2px 8px; border-radius:12px; flex-shrink:0;
    }
    .type-text        { background:#E8F4FD; color:#1a6b9e; }
    .type-resultats   { background:#FFF3E0; color:#e07000; }
    .type-player_grid { background:#E8F5E9; color:#2e7d32; }
    .type-staff_grid  { background:#F3E5F5; color:#6a1b9a; }
    .type-image_gallery { background:#FCE4EC; color:#ad1457; }
    .type-stats       { background:#E8EAF6; color:#283593; }
    .type-schedule_block { background:#E0F7FA; color:#00696f; }

    /* Info box */
    .info-box { background:#f0f7ff; border:1px solid #b8d4f0; border-radius:10px; padding:18px 22px; margin-top:12px; }
    .info-box h3 { color:#1a3a6b; margin-bottom:10px; font-family:'Bebas Neue',sans-serif; font-size:1.1rem; letter-spacing:.06em; }
    .info-box ol, .info-box ul { font-size:.88rem; line-height:1.8; color:#333; padding-left:20px; }
    .info-box code { background:#e8f0fe; padding:1px 5px; border-radius:4px; color:#1a3a6b; font-size:.82rem; }
    </style>
</head>
<body>
<div class="adm-wrap">

    <!-- Header -->
    <div class="adm-header">
        <h1>⚙️ Administration RFC Liège</h1>
        <div style="display:flex;gap:12px;align-items:center;">
            <span style="opacity:.6;font-size:.9rem;">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="../../../index.php" class="btn-adm" style="background:rgba(255,255,255,.15);color:#fff;">🏠 Site</a>
            <a href="../../compte/php/logout.php" class="btn-adm" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.3);">Déconnexion</a>
        </div>
    </div>

    <!-- Flash -->
    <?php if ($flash): ?>
    <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <!-- Onglets -->
    <div class="adm-tabs">
        <a href="?tab=dashboard"  class="adm-tab <?= $tab==='dashboard' ?'active':'' ?>">📊 Dashboard</a>
        <a href="?tab=pages"      class="adm-tab <?= $tab==='pages'     ?'active':'' ?>">📝 Pages</a>
        <a href="?tab=matches"    class="adm-tab <?= $tab==='matches'   ?'active':'' ?>">📅 Matchs</a>
        <a href="?tab=resultats"  class="adm-tab <?= $tab==='resultats' ?'active':'' ?>">⚽ Résultats</a>
        <a href="?tab=classement" class="adm-tab <?= $tab==='classement'?'active':'' ?>">🏆 Classement</a>
        <a href="?tab=equipes"    class="adm-tab <?= $tab==='equipes'   ?'active':'' ?>">👥 Équipes</a>
        <a href="?tab=navigation" class="adm-tab <?= $tab==='navigation'?'active':'' ?>">🗂 Navigation</a>
        <a href="?tab=users"      class="adm-tab <?= $tab==='users'     ?'active':'' ?>">👤 Utilisateurs</a>
    </div>

    <!-- ══════════════════════ DASHBOARD ═══════════════════════ -->
    <div class="adm-panel <?= $tab==='dashboard'?'active':'' ?>">
        <div class="adm-card">
            <h2>Vue d'ensemble</h2>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-num"><?= count($matches) ?></div><div class="stat-label">Matchs</div></div>
                <div class="stat-card"><div class="stat-num"><?= count($resultats) ?></div><div class="stat-label">Résultats</div></div>
                <div class="stat-card"><div class="stat-num"><?= count($equipes) ?></div><div class="stat-label">Équipes</div></div>
                <div class="stat-card"><div class="stat-num"><?= array_sum(array_map('count',$nav_all)) ?></div><div class="stat-label">Pages nav</div></div>
                <div class="stat-card"><div class="stat-num"><?= count($page_contents) ?></div><div class="stat-label">Pages éditées</div></div>
                <div class="stat-card"><div class="stat-num"><?= htmlspecialchars($nb_users) ?></div><div class="stat-label">Utilisateurs</div></div>
            </div>
        </div>
        <div class="adm-card">
            <h2>Prochain match</h2>
            <?php $next = $pdo->query("SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC LIMIT 1")->fetch(); ?>
            <?php if ($next): ?>
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <div style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:#1A1F3A;">RFC Liège <span style="color:#C8102E;">vs</span> <?= htmlspecialchars($next['equipe_adversaire']) ?></div>
                <div style="color:#666;">📅 <?= (new DateTime($next['date_match']))->format('d/m/Y H:i') ?> · <?= htmlspecialchars($next['stade']) ?></div>
            </div>
            <?php else: ?><p style="color:#888;font-style:italic;">Aucun match programmé.</p><?php endif; ?>
        </div>
        <div class="adm-card" style="background:linear-gradient(135deg,#fff5f5,#fff);">
            <h2>Accès rapide</h2>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="?tab=pages" class="btn-adm btn-adm-primary">📝 Éditer les pages</a>
                <a href="?tab=matches" class="btn-adm btn-adm-edit">📅 Ajouter un match</a>
                <a href="?tab=resultats" class="btn-adm btn-adm-toggle">⚽ Saisir un résultat</a>
                <a href="../../../index.php" target="_blank" class="btn-adm" style="background:#EEE;color:#1A1F3A;">🌐 Voir le site</a>
            </div>
        </div>
    </div>

    <!-- ══════════════════════ PAGES / CMS ═══════════════════ -->
    <div class="adm-panel <?= $tab==='pages'?'active':'' ?>">

        <div class="adm-card" style="background:linear-gradient(135deg,#FFF5F5,#fff);border-left:4px solid #C8102E;">
            <h2 style="border:none;margin-bottom:8px;">📝 Éditeur de pages — style WordPress</h2>
            <p style="font-size:.9rem;color:#555;line-height:1.6;">Clique sur <strong>✏️ Éditer</strong> à côté d'une page pour modifier son contenu. Le contenu sera affiché directement sur le site dès la sauvegarde.</p>
        </div>

        <?php foreach ($nav_order as $cat):
            $pages = $nav_all[$cat] ?? [];
            if (empty($pages)) continue;
            $folder = $cat_folder[$cat] ?? strtolower($cat);
        ?>
        <div class="adm-card">
            <h2><?= htmlspecialchars($cat) ?> <span style="font-size:.75rem;opacity:.4;">(<?= count($pages) ?> pages)</span></h2>
            <div class="cms-pages-list">
            <?php foreach ($pages as $p):
                $slug     = $p['slug'];
                $existing = $page_contents[$slug] ?? null;
                $has_content = !empty($existing['contenu']);
            ?>
            <details class="cms-detail" id="edit-<?= htmlspecialchars($slug) ?>">
                <summary class="cms-page-item">
                    <span class="cms-label"><?= htmlspecialchars($p['label']) ?></span>
                    <span class="cms-slug"><?= htmlspecialchars($slug) ?>.php</span>
                    <span class="cms-status <?= $has_content ? 'cms-status-saved' : 'cms-status-empty' ?>">
                        <?= $has_content ? '✅ Contenu sauvegardé' : '📝 Contenu statique' ?>
                    </span>
                    <span class="cms-arrow">▼</span>
                </summary>

                <div class="cms-editor-body">
                    <form action="../php/save_content.php" method="POST" class="adm-form">
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">

                        <div class="adm-form-row">
                            <div>
                                <label>Titre de la page</label>
                                <input type="text" name="titre"
                                    value="<?= htmlspecialchars($existing['titre'] ?? $p['label']) ?>"
                                    placeholder="Titre affiché sur la page">
                            </div>
                            <div>
                                <label>Description SEO (méta)</label>
                                <input type="text" name="meta_desc"
                                    value="<?= htmlspecialchars($existing['meta_desc'] ?? '') ?>"
                                    placeholder="Courte description pour Google">
                            </div>
                        </div>

                        <div>
                            <label>Blocs de contenu <small style="font-weight:400;text-transform:none;letter-spacing:0;color:#aaa;">— chaque bloc = une section sur la page</small></label>

                            <?php
                            // Charger les sections existantes depuis le JSON
                            $saved_sections = [];
                            if (!empty($existing['contenu'])) {
                                $dec = json_decode($existing['contenu'], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                                    $saved_sections = $dec;
                                } else {
                                    // Ancien format HTML → 1 section
                                    $saved_sections = [['titre'=>$existing['titre']??'','contenu'=>$existing['contenu'],'type'=>'text']];
                                }
                            }
                            if (empty($saved_sections)) {
                                $saved_sections = [['titre'=>'','contenu'=>'','type'=>'text'],['titre'=>'','contenu'=>'','type'=>'text']];
                            }
                            ?>

                            <div class="cms-sections-wrap" id="sections-<?= htmlspecialchars($slug) ?>">
                            <?php
                            $grid_types_admin = ['player_grid','staff_grid','image_gallery','stats','schedule_block'];
                            foreach ($saved_sections as $si => $sec):
                                $sec_type  = $sec['type'] ?? 'text';
                                $sec_items = $sec['items'] ?? [];
                                $is_grid   = in_array($sec_type, $grid_types_admin);
                            ?>
                            <div class="cms-section-block">
                                <div class="cms-section-hd">
                                    <span class="cms-section-num">📦 <?= $si+1 ?></span>
                                    <select name="section_type[]" class="cms-type-select" onchange="cmsTypeSwitch(this)">
                                        <option value="text"           <?= $sec_type==='text'           ?'selected':'' ?>>📝 Texte libre</option>
                                        <option value="resultats"      <?= $sec_type==='resultats'      ?'selected':'' ?>>⚽ Résultats matchs</option>
                                        <option value="player_grid"    <?= $sec_type==='player_grid'    ?'selected':'' ?>>👤 Grille joueurs</option>
                                        <option value="staff_grid"     <?= $sec_type==='staff_grid'     ?'selected':'' ?>>🧑‍💼 Grille staff</option>
                                        <option value="image_gallery"  <?= $sec_type==='image_gallery'  ?'selected':'' ?>>🖼️ Galerie photos</option>
                                        <option value="stats"          <?= $sec_type==='stats'          ?'selected':'' ?>>📊 Chiffres clés</option>
                                        <option value="schedule_block" <?= $sec_type==='schedule_block' ?'selected':'' ?>>📅 Calendrier</option>
                                    </select>
                                    <button type="button" class="cms-del-section" onclick="delSection(this)" title="Supprimer">✕</button>
                                </div>

                                <input type="text" name="section_titre[]"
                                       value="<?= htmlspecialchars($sec['titre']??'') ?>"
                                       placeholder="Titre de la section"
                                       class="cms-section-title-input">

                                <!-- Champs TEXTE / RÉSULTATS -->
                                <div class="cms-block-text-fields" <?= $is_grid ? 'style="display:none"' : '' ?>>
                                    <div class="cms-toolbar">
                                        <button type="button" onclick="cmsFmtS(this,'b')"><b>B</b></button>
                                        <button type="button" onclick="cmsFmtS(this,'i')"><i>I</i></button>
                                        <button type="button" onclick="cmsFmtS(this,'u')"><u>U</u></button>
                                        <div class="sep"></div>
                                        <button type="button" onclick="cmsFmtS(this,'h3')">H3</button>
                                        <button type="button" onclick="cmsFmtS(this,'p')">§</button>
                                        <button type="button" onclick="cmsFmtS(this,'ul')">☰</button>
                                        <button type="button" onclick="cmsFmtS(this,'li')">•</button>
                                        <div class="sep"></div>
                                        <button type="button" onclick="cmsLinkS(this)">🔗</button>
                                    </div>
                                    <textarea name="section_contenu[]" class="cms-textarea cms-section-ta" rows="5"
                                              placeholder="<?= $sec_type==='resultats' ? 'U15 | RFC Liège 4 – 0 Seraing | Victoire · 8 mars 2026' : 'Contenu HTML : <p>, <b>, <ul>, <li>...' ?>"><?= htmlspecialchars($sec['contenu']??'') ?></textarea>
                                </div>

                                <!-- Champs GRID (joueurs, staff, galerie, stats, calendrier) -->
                                <div class="cms-block-grid-fields" <?= !$is_grid ? 'style="display:none"' : '' ?>>
                                    <input type="hidden" name="section_items_json[]" class="section-items-json"
                                           value="<?= htmlspecialchars(json_encode($sec_items, JSON_UNESCAPED_UNICODE)) ?>">
                                    <!-- Colonnes selon le type -->
                                    <div class="cms-items-header">
                                        <?php
                                        $headers = [
                                            'player_grid'    => '🖼️ Photo &nbsp;·&nbsp; 👤 Nom &nbsp;·&nbsp; 🎽 Poste &nbsp;·&nbsp; # Numéro',
                                            'staff_grid'     => '🖼️ Photo &nbsp;·&nbsp; 👤 Nom &nbsp;·&nbsp; 🏷️ Rôle &nbsp;·&nbsp; 📝 Description',
                                            'image_gallery'  => '🖼️ Image &nbsp;·&nbsp; 📝 Légende',
                                            'stats'          => '🔢 Valeur &nbsp;·&nbsp; 🏷️ Label &nbsp;·&nbsp; 😀 Icône',
                                            'schedule_block' => '📅 Date &nbsp;·&nbsp; ⏰ Heure &nbsp;·&nbsp; 🆚 Adversaire &nbsp;·&nbsp; 📍 Lieu &nbsp;·&nbsp; 🏆 Compétition',
                                        ];
                                        echo $headers[$sec_type] ?? 'Éléments';
                                        ?>
                                        <span style="font-size:.7rem;color:#C8102E;"><?= count($sec_items) ?> élément(s)</span>
                                    </div>
                                    <div class="cms-items-list" data-block-type="<?= htmlspecialchars($sec_type) ?>">
                                    <?php foreach ($sec_items as $ii => $item): ?>
                                        <?= cmsItemRowHtml($sec_type, $ii+1, $item) ?>
                                    <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="cms-add-item-btn" onclick="cmsAddItem(this)">
                                        ➕ Ajouter <?= cmsAddLabel($sec_type) ?>
                                    </button>
                                </div>

                            </div>
                            <?php endforeach; ?>
                            </div>


                            <button type="button" class="btn-adm" style="background:#EEE;color:#333;margin-top:8px;width:100%;"
                                    onclick="addSection('sections-<?= htmlspecialchars($slug) ?>')">
                                ➕ Ajouter un bloc de contenu
                            </button>
                        </div>

                        <div class="cms-save-row">
                            <button type="submit" class="btn-adm btn-adm-primary">💾 Sauvegarder</button>
                            <a href="../../../pages/club/<?= htmlspecialchars($p['folder']) ?>/<?= htmlspecialchars($p['slug']) ?>.php"
                               target="_blank" class="btn-adm btn-adm-view">👁 Voir la page</a>
                            <?php if ($has_content): ?>
                            <span style="font-size:.8rem;color:#888;">Dernière modif : <?= date('d/m/Y H:i', strtotime($existing['updated_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </details>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="info-box">
            <h3>ℹ️ Comment utiliser l'éditeur de pages ?</h3>
            <ol>
                <li>Clique sur une page pour l'ouvrir</li>
                <li>Écris le contenu dans la zone de texte — <strong>HTML simple accepté</strong></li>
                <li>Utilise la barre d'outils pour insérer des balises facilement</li>
                <li>Clique <strong>💾 Sauvegarder</strong> → le contenu apparaît sur le site ✅</li>
                <li>Clique <strong>👁 Voir la page</strong> pour vérifier le résultat</li>
            </ol>
            <p style="margin-top:10px;font-size:.85rem;color:#555;">
                Balises HTML disponibles : <code>&lt;p&gt;</code> <code>&lt;h2&gt;</code> <code>&lt;h3&gt;</code>
                <code>&lt;b&gt;</code> <code>&lt;i&gt;</code> <code>&lt;ul&gt;</code> <code>&lt;li&gt;</code>
                <code>&lt;a href="..."&gt;</code>
            </p>
        </div>
    </div>

    <!-- ══════════════════════ MATCHS ═════════════════════════ -->
    <div class="adm-panel <?= $tab==='matches'?'active':'' ?>">
        <div class="adm-card">
            <h2>Ajouter un match</h2>
            <form action="../php/save_match.php" method="POST" class="adm-form">
                <div class="adm-form-row">
                    <div><label>Équipe adverse</label><input type="text" name="equipe_adversaire" placeholder="ex: Seraing" required></div>
                    <div><label>Stade</label><input type="text" name="stade" placeholder="ex: Stade de Rocourt" required></div>
                </div>
                <div class="adm-form-row">
                    <div><label>Date & Heure</label><input type="datetime-local" name="date_match" required></div>
                    <div><label>Compétition</label><input type="text" name="competition" placeholder="ex: Challenger Pro League"></div>
                </div>
                <div><button type="submit" class="btn-adm btn-adm-primary">➕ Ajouter le match</button></div>
            </form>
        </div>
        <div class="adm-card">
            <h2>Matchs programmés</h2>
            <?php if (empty($matches)): ?>
            <p style="color:#888;font-style:italic;">Aucun match programmé.</p>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Adversaire</th><th>Stade</th><th>Date</th><th>Compétition</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($matches as $m): ?>
                <tr>
                    <td><strong>RFC Liège vs <?= htmlspecialchars($m['equipe_adversaire']) ?></strong></td>
                    <td><?= htmlspecialchars($m['stade']) ?></td>
                    <td><?= (new DateTime($m['date_match']))->format('d/m/Y H:i') ?></td>
                    <td><?= htmlspecialchars($m['competition'] ?? '—') ?></td>
                    <td>
                        <form action="../php/delete_match.php" method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-danger">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════ RÉSULTATS ══════════════════════ -->
    <div class="adm-panel <?= $tab==='resultats'?'active':'' ?>">
        <div class="adm-card">
            <h2>Ajouter un résultat</h2>
            <form action="../php/save_result.php" method="POST" class="adm-form">
                <div class="adm-form-row">
                    <div><label>Équipe domicile</label>
                        <select name="equipe_domicile" required>
                            <?php foreach ($equipes as $eq): ?><option value="<?= htmlspecialchars($eq['nom']) ?>"><?= htmlspecialchars($eq['nom']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Équipe extérieur</label>
                        <select name="equipe_exterieur" required>
                            <?php foreach ($equipes as $eq): ?><option value="<?= htmlspecialchars($eq['nom']) ?>"><?= htmlspecialchars($eq['nom']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="adm-form-row">
                    <div><label>Buts domicile</label><input type="number" name="buts_domicile" min="0" value="0" required></div>
                    <div><label>Buts extérieur</label><input type="number" name="buts_exterieur" min="0" value="0" required></div>
                </div>
                <div class="adm-form-row">
                    <div><label>Date</label><input type="date" name="date_match" required></div>
                    <div><label>Journée</label><input type="text" name="journee" placeholder="ex: 18"></div>
                </div>
                <div><button type="submit" class="btn-adm btn-adm-primary">➕ Enregistrer</button></div>
            </form>
        </div>
        <div class="adm-card">
            <h2>Résultats enregistrés</h2>
            <?php if (empty($resultats)): ?>
            <p style="color:#888;font-style:italic;">Aucun résultat.</p>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>J.</th><th>Match</th><th>Score</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($resultats as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['journee'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($r['equipe_domicile']) ?> vs <?= htmlspecialchars($r['equipe_exterieur']) ?></td>
                    <td><strong><?= $r['buts_domicile'] ?> – <?= $r['buts_exterieur'] ?></strong></td>
                    <td><?= (new DateTime($r['date_match']))->format('d/m/Y') ?></td>
                    <td>
                        <form action="../php/delete_result.php" method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-danger">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════ CLASSEMENT ════════════════════ -->
    <div class="adm-panel <?= $tab==='classement'?'active':'' ?>">
        <div class="adm-card">
            <h2>Classement (calculé automatiquement)</h2>
            <?php if (empty($cl)): ?>
            <p style="color:#888;font-style:italic;">Aucune donnée.</p>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>#</th><th>Équipe</th><th>J</th><th>V</th><th>N</th><th>D</th><th>BP</th><th>BC</th><th>+/-</th><th>PTS</th></tr></thead>
                <tbody>
                <?php $pos=1; foreach ($cl as $nom=>$s): $is_b=stripos($nom,'RFC Liège')!==false; ?>
                <tr <?= $is_b?'style="background:#fff5f5;font-weight:700;"':'' ?>>
                    <td><?= $pos ?></td>
                    <td><?= $is_b?'⭐ ':'' ?><?= htmlspecialchars($nom) ?></td>
                    <td><?= $s['j'] ?></td><td><?= $s['v'] ?></td><td><?= $s['n'] ?></td><td><?= $s['d'] ?></td>
                    <td><?= $s['bp'] ?></td><td><?= $s['bc'] ?></td>
                    <td style="color:<?= $s['diff']>0?'#1aaf5d':($s['diff']<0?'#ff4444':'#888') ?>;"><?= $s['diff']>0?'+'.$s['diff']:$s['diff'] ?></td>
                    <td><strong style="color:#C8102E;"><?= $s['pts'] ?></strong></td>
                </tr>
                <?php $pos++; endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════ ÉQUIPES ═══════════════════════ -->
    <div class="adm-panel <?= $tab==='equipes'?'active':'' ?>">
        <div class="adm-card">
            <h2>Ajouter une équipe</h2>
            <form action="../php/manage_teams.php" method="POST" class="adm-form" style="grid-template-columns:1fr auto;">
                <div><label>Nom de l'équipe</label><input type="text" name="nom" placeholder="ex: RFC Liège" required></div>
                <div style="align-self:end;"><button type="submit" name="action" value="add" class="btn-adm btn-adm-primary">➕ Ajouter</button></div>
            </form>
        </div>
        <div class="adm-card">
            <h2>Équipes enregistrées</h2>
            <table class="adm-table">
                <thead><tr><th>Nom</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($equipes as $eq): ?>
                <tr>
                    <td><?= htmlspecialchars($eq['nom']) ?></td>
                    <td>
                        <form action="../php/manage_teams.php" method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $eq['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-danger">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══════════════════════ UTILISATEURS ═══════════════════ -->
    <div class="adm-panel <?= $tab==='users'?'active':'' ?>">
        <div class="adm-card">
            <h2>Comptes utilisateurs</h2>
            <?php if (empty($users)): ?>
            <p style="color:#888;font-style:italic;">Aucun utilisateur.</p>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Nom</th><th>Email</th><th>Admin</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['isadmin'] ? '<span class="badge-active">Admin</span>' : '' ?></td>
                    <td>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $u['id']): ?>
                        <form action="../php/toggle_admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="make" value="<?= $u['isadmin'] ? '0' : '1' ?>">
                            <button type="submit" class="btn-adm btn-adm-toggle"><?= $u['isadmin'] ? 'Retirer admin' : 'Promouvoir admin' ?></button>
                        </form>
                        <?php else: ?><em style="color:#888;">vous-même</em><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════ NAVIGATION ════════════════════ -->
    <div class="adm-panel <?= $tab==='navigation'?'active':'' ?>">
        <div class="adm-card">
            <h2>➕ Ajouter une page au menu</h2>
            <form action="../php/nav_actions.php" method="POST" class="adm-form">
                <input type="hidden" name="action" value="nav_add">
                <div class="adm-form-row">
                    <div><label>Catégorie</label>
                        <select name="categorie" required>
                            <?php foreach ($nav_order as $cat): ?><option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label>Label affiché</label><input type="text" name="label" placeholder="ex: Résultats féminines" required></div>
                </div>
                <div class="adm-form-row">
                    <div><label>Dossier (folder)</label><input type="text" name="folder" placeholder="ex: actu" required></div>
                    <div><label>Slug (sans .php)</label><input type="text" name="slug" placeholder="ex: resultats-dames" required></div>
                </div>
                <div class="adm-form-row">
                    <div><label>Position</label><input type="number" name="position" value="99" min="0"></div>
                    <div style="align-self:end;"><button type="submit" class="btn-adm btn-adm-primary">➕ Ajouter</button></div>
                </div>
            </form>
        </div>

        <?php foreach ($nav_order as $cat):
            $pages  = $nav_all[$cat] ?? [];
            $folder = $cat_folder[$cat] ?? strtolower($cat);
        ?>
        <div class="adm-card">
            <h2><?= htmlspecialchars($cat) ?> <span style="font-size:.75rem;opacity:.4;">(<?= count($pages) ?> pages)</span></h2>
            <?php if (empty($pages)): ?>
            <p style="color:#888;font-style:italic;font-size:.9rem;">Aucune page.</p>
            <?php else: ?>
            <table class="adm-table" id="nav-table-<?= strtolower(preg_replace('/[^a-z]/','',strtolower($cat))) ?>">
                <thead><tr><th style="width:30px;">⣿</th><th>#</th><th>Label</th><th>Fichier</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($pages as $p): ?>
                <tr data-id="<?= $p['id'] ?>" style="<?= !$p['active']?'opacity:.5;':'' ?>">
                    <td><span class="drag-handle" title="Glisser">⣿</span></td>
                    <td style="color:#888;font-size:.8rem;"><?= $p['position'] ?></td>
                    <td>
                        <form action="../php/nav_actions.php" method="POST" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="action" value="nav_edit">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="categorie" value="<?= htmlspecialchars($p['categorie']) ?>">
                            <input type="hidden" name="folder" value="<?= htmlspecialchars($p['folder']) ?>">
                            <input type="hidden" name="slug" value="<?= htmlspecialchars($p['slug']) ?>">
                            <input type="hidden" name="position" value="<?= $p['position'] ?>">
                            <input type="text" name="label" value="<?= htmlspecialchars($p['label']) ?>"
                                   style="border:1px solid #DDD;border-radius:6px;padding:5px 10px;font-size:.88rem;width:180px;color:#1A1F3A;background:#fff;">
                            <button type="submit" class="btn-adm btn-adm-edit" style="padding:5px 10px;">💾</button>
                        </form>
                    </td>
                    <td style="font-family:monospace;font-size:.82rem;color:#C8102E;">pages/club/<?= htmlspecialchars($p['folder']) ?>/<strong><?= htmlspecialchars($p['slug']) ?>.php</strong></td>
                    <td><span class="badge-<?= $p['active']?'active':'inactive' ?>"><?= $p['active']?'Visible':'Masqué' ?></span></td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <form action="../php/nav_actions.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="nav_toggle">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-toggle"><?= $p['active']?'🙈 Masquer':'👁 Afficher' ?></button>
                        </form>
                        <form action="../php/nav_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?');">
                            <input type="hidden" name="action" value="nav_delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-danger">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

</div><!-- /adm-wrap -->

<script>
// ═══════════════════════════════════════════════════
// CMS — Fonctions de l'éditeur de blocs
// ═══════════════════════════════════════════════════

/* ── Types grilles (pas de textarea) ── */
const CMS_GRID_TYPES = ['player_grid','staff_grid','image_gallery','stats','schedule_block'];

/* ── Labels bouton "Ajouter..." ── */
const CMS_ADD_LABELS = {
    player_grid:'un joueur', staff_grid:'un membre',
    image_gallery:'une photo', stats:'un chiffre', schedule_block:'un match'
};

/* ── Champs par type ── */
const CMS_FIELDS = {
    player_grid:    ['image','nom','poste','numero'],
    staff_grid:     ['image','nom','role','desc'],
    image_gallery:  ['image','legende'],
    stats:          ['valeur','label','icon'],
    schedule_block: ['date','heure','adversaire','lieu','competition']
};

/* ── Placeholders par champ ── */
const CMS_PH = {
    image:'URL image', nom:'Nom', poste:'Poste', numero:'N°',
    role:'Rôle', desc:'Description', legende:'Légende',
    valeur:'Valeur', label:'Label', icon:'Icône ⚽',
    date:'Date', heure:'Heure', adversaire:'Adversaire',
    lieu:'Lieu', competition:'Compétition'
};

/* ── Sérialiser les items grid avant soumission ─────────────── */
document.addEventListener('submit', function(e) {
    if (!e.target.closest('.cms-editor-body')) return;
    const form = e.target;
    form.querySelectorAll('.cms-section-block').forEach(block => {
        const jsonField = block.querySelector('.section-items-json');
        if (!jsonField) return;
        const list = block.querySelector('.cms-items-list');
        if (!list) { jsonField.value = '[]'; return; }
        const type = list.dataset.blockType || 'player_grid';
        const fields = CMS_FIELDS[type] || [];
        const items = [];
        list.querySelectorAll('.cms-item-row').forEach(row => {
            const obj = {};
            fields.forEach(f => {
                const el = row.querySelector('.cms-item-' + f);
                if (el) obj[f] = el.value.trim();
            });
            // Ne pas inclure les items totalement vides
            if (Object.values(obj).some(v => v !== '')) items.push(obj);
        });
        jsonField.value = JSON.stringify(items);
    });
});

/* ── Changer le type d'un bloc ─────────────────────────────── */
function cmsTypeSwitch(sel) {
    const block     = sel.closest('.cms-section-block');
    const newType   = sel.value;
    const isGrid    = CMS_GRID_TYPES.includes(newType);
    const textDiv   = block.querySelector('.cms-block-text-fields');
    const gridDiv   = block.querySelector('.cms-block-grid-fields');

    textDiv.style.display = isGrid ? 'none' : '';
    gridDiv.style.display = isGrid ? '' : 'none';

    if (isGrid) {
        // Mettre à jour le type de la liste
        const list = gridDiv.querySelector('.cms-items-list');
        if (list) list.dataset.blockType = newType;
        // Mettre à jour le label du bouton + les en-têtes
        const addBtn = gridDiv.querySelector('.cms-add-item-btn');
        if (addBtn) addBtn.innerHTML = '➕ Ajouter ' + (CMS_ADD_LABELS[newType] || 'un élément');
        // Mettre à jour le header des colonnes
        const hdrs = {
            player_grid:    '🖼️ Photo · 👤 Nom · 🎽 Poste · # Numéro',
            staff_grid:     '🖼️ Photo · 👤 Nom · 🏷️ Rôle · 📝 Description',
            image_gallery:  '🖼️ Image · 📝 Légende',
            stats:          '🔢 Valeur · 🏷️ Label · 😀 Icône',
            schedule_block: '📅 Date · ⏰ Heure · 🆚 Adversaire · 📍 Lieu · 🏆 Compétition'
        };
        const hdrEl = gridDiv.querySelector('.cms-items-header');
        if (hdrEl) {
            hdrEl.innerHTML = (hdrs[newType] || 'Éléments') + '<span style="font-size:.7rem;color:#C8102E;">0 élément(s)</span>';
        }
        // Vider la liste si on change de type
        if (list) list.innerHTML = '';
    }
}

/* ── Construire le HTML d'un item ────────────────────────────── */
function cmsBuildItemRow(type, num, data) {
    data = data || {};
    const showImg = ['player_grid','staff_grid','image_gallery'].includes(type);
    const imgUrl  = data.image || '';
    let html = `<div class="cms-item-row" data-idx="${num}">
        <span class="cms-item-num">${num}</span>`;

    if (showImg) {
        html += `<div class="cms-item-img-wrap">
            <img class="cms-item-img-preview" src="${escHtml(imgUrl)}"
                 style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:2px solid #EEE;background:#F0F2F8;" alt="">
            <label style="position:absolute;bottom:-4px;right:-4px;background:#C8102E;color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.6rem;" title="Upload">
                📁<input type="file" accept="image/*" style="display:none;" onchange="cmsUploadImg(this)">
            </label>
        </div>
        <input type="text" class="cms-item-field cms-item-image" placeholder="URL image" value="${escHtml(imgUrl)}"
               oninput="this.closest('.cms-item-row').querySelector('.cms-item-img-preview').src=this.value">`;
    }

    if (type === 'player_grid') {
        html += `<input type="text" class="cms-item-field cms-item-nom" placeholder="Nom du joueur" value="${escHtml(data.nom||'')}">
                 <input type="text" class="cms-item-field md cms-item-poste" placeholder="Poste" value="${escHtml(data.poste||'')}">
                 <input type="text" class="cms-item-field sm cms-item-numero" placeholder="N°" value="${escHtml(data.numero||'')}">`;
    } else if (type === 'staff_grid') {
        html += `<input type="text" class="cms-item-field cms-item-nom" placeholder="Nom" value="${escHtml(data.nom||'')}">
                 <input type="text" class="cms-item-field md cms-item-role" placeholder="Rôle" value="${escHtml(data.role||'')}">
                 <input type="text" class="cms-item-field cms-item-desc" placeholder="Description courte" value="${escHtml(data.desc||'')}">`;
    } else if (type === 'image_gallery') {
        html += `<input type="text" class="cms-item-field cms-item-legende" placeholder="Légende (optionnelle)" value="${escHtml(data.legende||'')}">`;
    } else if (type === 'stats') {
        html += `<input type="text" class="cms-item-field cms-item-valeur" placeholder="Valeur (ex: 134)" value="${escHtml(data.valeur||'')}">
                 <input type="text" class="cms-item-field cms-item-label" placeholder="Label" value="${escHtml(data.label||'')}">
                 <input type="text" class="cms-item-field sm cms-item-icon" placeholder="Icône ⚽" value="${escHtml(data.icon||'')}">`;
    } else if (type === 'schedule_block') {
        html += `<input type="text" class="cms-item-field md cms-item-date" placeholder="Date" value="${escHtml(data.date||'')}">
                 <input type="text" class="cms-item-field sm cms-item-heure" placeholder="Heure" value="${escHtml(data.heure||'')}">
                 <input type="text" class="cms-item-field cms-item-adversaire" placeholder="Adversaire" value="${escHtml(data.adversaire||'')}">
                 <input type="text" class="cms-item-field md cms-item-lieu" placeholder="Lieu" value="${escHtml(data.lieu||'')}">
                 <input type="text" class="cms-item-field md cms-item-competition" placeholder="Compétition" value="${escHtml(data.competition||'')}">`;
    }

    html += `<button type="button" class="cms-item-del" onclick="cmsRemoveItem(this)" title="Supprimer">✕</button></div>`;
    return html;
}

/* ── Ajouter un item ────────────────────────────────────────── */
function cmsAddItem(addBtn) {
    const gridDiv = addBtn.closest('.cms-block-grid-fields');
    const list    = gridDiv.querySelector('.cms-items-list');
    const type    = list.dataset.blockType || 'player_grid';
    const num     = list.querySelectorAll('.cms-item-row').length + 1;
    list.insertAdjacentHTML('beforeend', cmsBuildItemRow(type, num, {}));
    // Focus sur le premier champ du nouvel item
    const lastRow = list.lastElementChild;
    const firstIn = lastRow.querySelector('input[type="text"]');
    if (firstIn) { firstIn.focus(); firstIn.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    // Mise à jour compteur
    updateItemCount(gridDiv, num);
}

/* ── Supprimer un item ──────────────────────────────────────── */
function cmsRemoveItem(btn) {
    const row     = btn.closest('.cms-item-row');
    const list    = row.closest('.cms-items-list');
    const gridDiv = list.closest('.cms-block-grid-fields');
    row.remove();
    // Renumeroter
    list.querySelectorAll('.cms-item-row').forEach((r, i) => {
        const numEl = r.querySelector('.cms-item-num');
        if (numEl) numEl.textContent = i + 1;
        r.dataset.idx = i + 1;
    });
    updateItemCount(gridDiv, list.querySelectorAll('.cms-item-row').length);
}

function updateItemCount(gridDiv, n) {
    const hdr = gridDiv.querySelector('.cms-items-header span');
    if (hdr) hdr.textContent = n + ' élément(s)';
}

/* ── Upload image ────────────────────────────────────────────── */
function cmsUploadImg(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    const row      = fileInput.closest('.cms-item-row');
    const urlField = row.querySelector('.cms-item-image');
    const preview  = row.querySelector('.cms-item-img-preview');

    // Prévisualisation locale immédiate
    const reader = new FileReader();
    reader.onload = e => {
        if (preview) preview.src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload vers le serveur
    const fd = new FormData();
    fd.append('image', file);
    fetch('../php/upload_image.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.url) {
                if (urlField) urlField.value = data.url;
                if (preview)  preview.src   = data.url;
            } else if (data.error) {
                alert('Erreur upload : ' + data.error);
            }
        })
        .catch(() => {/* Upload échoué, la prévisualisation reste DataURL */});
}

/* ── Helpers texte ────────────────────────────────────────────── */
function getSectionTA(btn) {
    return btn.closest('.cms-section-block').querySelector('.cms-section-ta');
}
function cmsFmtS(btn, tag) {
    const ta = getSectionTA(btn);
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.substring(s, e) || 'Texte';
    const ins = '<' + tag + '>' + sel + '</' + tag + '>';
    ta.value = ta.value.substring(0, s) + ins + ta.value.substring(e);
    ta.focus();
    ta.setSelectionRange(s + tag.length + 2, s + tag.length + 2 + sel.length);
}
function cmsLinkS(btn) {
    const ta  = getSectionTA(btn);
    const url = prompt('URL du lien :', 'https://');
    if (!url) return;
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.substring(s, e) || 'Texte';
    ta.value = ta.value.substring(0, s) + '<a href="' + url + '">' + sel + '</a>' + ta.value.substring(e);
    ta.focus();
}

/* ── Supprimer un bloc de section ───────────────────────────── */
function delSection(btn) {
    const block = btn.closest('.cms-section-block');
    const wrap  = block.parentNode;
    if (wrap.querySelectorAll('.cms-section-block').length <= 1) {
        alert('Il faut au moins un bloc.'); return;
    }
    if (confirm('Supprimer ce bloc ?')) block.remove();
    renumberSections(wrap);
}

/* ── Ajouter un bloc de section ─────────────────────────────── */
function addSection(wrapId) {
    const wrap = document.getElementById(wrapId);
    const n    = wrap.querySelectorAll('.cms-section-block').length + 1;
    const div  = document.createElement('div');
    div.className = 'cms-section-block';
    div.innerHTML = `
        <div class="cms-section-hd">
            <span class="cms-section-num">📦 ${n}</span>
            <select name="section_type[]" class="cms-type-select" onchange="cmsTypeSwitch(this)">
                <option value="text">📝 Texte libre</option>
                <option value="resultats">⚽ Résultats matchs</option>
                <option value="player_grid">👤 Grille joueurs</option>
                <option value="staff_grid">🧑‍💼 Grille staff</option>
                <option value="image_gallery">🖼️ Galerie photos</option>
                <option value="stats">📊 Chiffres clés</option>
                <option value="schedule_block">📅 Calendrier</option>
            </select>
            <button type="button" class="cms-del-section" onclick="delSection(this)">✕</button>
        </div>
        <input type="text" name="section_titre[]" placeholder="Titre de la section" class="cms-section-title-input">
        <div class="cms-block-text-fields">
            <div class="cms-toolbar">
                <button type="button" onclick="cmsFmtS(this,'b')"><b>B</b></button>
                <button type="button" onclick="cmsFmtS(this,'i')"><i>I</i></button>
                <button type="button" onclick="cmsFmtS(this,'u')"><u>U</u></button>
                <div class="sep"></div>
                <button type="button" onclick="cmsFmtS(this,'h3')">H3</button>
                <button type="button" onclick="cmsFmtS(this,'p')">§</button>
                <button type="button" onclick="cmsFmtS(this,'ul')">☰</button>
                <button type="button" onclick="cmsFmtS(this,'li')">•</button>
                <div class="sep"></div>
                <button type="button" onclick="cmsLinkS(this)">🔗</button>
            </div>
            <textarea name="section_contenu[]" class="cms-textarea cms-section-ta" rows="5"
                      placeholder="Contenu HTML libre..."></textarea>
        </div>
        <div class="cms-block-grid-fields" style="display:none">
            <input type="hidden" name="section_items_json[]" class="section-items-json" value="[]">
            <div class="cms-items-header">Éléments <span style="font-size:.7rem;color:#C8102E;">0 élément(s)</span></div>
            <div class="cms-items-list" data-block-type="player_grid"></div>
            <button type="button" class="cms-add-item-btn" onclick="cmsAddItem(this)">➕ Ajouter un joueur</button>
        </div>`;
    wrap.appendChild(div);
    div.querySelector('.cms-section-title-input').focus();
    div.scrollIntoView({ behavior:'smooth', block:'nearest' });
}

/* ── Renuméroter les blocs ───────────────────────────────────── */
function renumberSections(wrap) {
    wrap.querySelectorAll('.cms-section-num').forEach((el, i) => {
        el.textContent = '📦 ' + (i + 1);
    });
}

/* ── Utilitaire escHtml ─────────────────────────────────────── */
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Drag & drop navigation ───────────────────────────────────
document.querySelectorAll('[id^="nav-table-"] tbody').forEach(function(tbody) {
    var dragging = null;
    tbody.querySelectorAll('tr').forEach(function(row) {
        row.setAttribute('draggable', true);
        row.addEventListener('dragstart', function() { dragging = this; setTimeout(() => row.style.opacity = '0.4', 0); });
        row.addEventListener('dragend', function() {
            this.style.opacity = '';
            dragging = null;
            var ids = Array.from(tbody.querySelectorAll('tr')).map(r => r.dataset.id);
            fetch('../php/nav_actions.php', {
                method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'action=nav_reorder&ids=' + encodeURIComponent(JSON.stringify(ids))
            });
        });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            var bounding = this.getBoundingClientRect();
            if (e.clientY - bounding.y - bounding.height / 2 > 0) {
                this.parentNode.insertBefore(dragging, this.nextSibling);
            } else {
                this.parentNode.insertBefore(dragging, this);
            }
        });
    });
});

// ── Open correct details from URL hash ──────────────────────
if (location.hash) {
    var target = document.querySelector(location.hash);
    if (target && target.tagName === 'DETAILS') target.open = true;
}
</script>
</body>
</html>

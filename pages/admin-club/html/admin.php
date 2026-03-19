<?php
session_start();
require_once '../../../db.php';
// the check_admin script lives in the regular admin section, not in
// admin-club, so adjust the relative path accordingly
require_once '../../admin/html/check_admin.php';

// ── Lecture messages flash ────────────────────────────────────────
$flash = $_SESSION['nav_msg'] ?? null;
unset($_SESSION['nav_msg']);

// ── Onglet actif ─────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'dashboard';

// ── Données ──────────────────────────────────────────────────────
$matches  = $pdo->query("SELECT * FROM matches  ORDER BY date_match  ASC")->fetchAll();
$resultats= $pdo->query("SELECT * FROM resultats ORDER BY date_match DESC")->fetchAll();
$equipes  = $pdo->query("SELECT * FROM equipes  ORDER BY nom  ASC")->fetchAll();

// Classement
$cl = [];
foreach ($equipes as $eq) {
    $cl[$eq['nom']] = ['pts'=>0,'j'=>0,'v'=>0,'n'=>0,'d'=>0,'bp'=>0,'bc'=>0,'diff'=>0];
}
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
$nav_order   = ['ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'];
$cat_folder  = ['ACTU'=>'actu','CLUB'=>'club','D1B'=>'d1b','DAMES'=>'dames','EDJ'=>'edj','FANS'=>'fans','MÉDIAS'=>'medias','BUSINESS'=>'business','TICKETS'=>'tickets'];
$nav_all     = [];
try {
    $rows = $pdo->query("SELECT * FROM nav_pages ORDER BY FIELD(categorie,'ACTU','CLUB','D1B','DAMES','EDJ','FANS','MÉDIAS','BUSINESS','TICKETS'), position ASC")->fetchAll();
    foreach ($rows as $row) $nav_all[$row['categorie']][] = $row;
} catch(PDOException $e) { $nav_all = []; }
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
    /* ── Admin layout ─────────────────────────────── */
    body { background:#F4F4F6; }
    .adm-wrap { max-width:1200px; margin:0 auto; padding:24px 20px; }
    .adm-header { background:var(--navy,#1A1F3A); color:#fff; padding:20px 28px; border-radius:12px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; }
    .adm-header h1 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:0.06em; }
    .adm-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:24px; }
    .adm-tab { padding:9px 20px; border-radius:8px; font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:0.88rem; letter-spacing:0.1em; text-transform:uppercase; text-decoration:none; color:var(--navy,#1A1F3A); background:#fff; border:1px solid #DDD; transition:all .18s; }
    .adm-tab:hover { background:var(--rouge,#C8102E); color:#fff; border-color:var(--rouge,#C8102E); }
    .adm-tab.active { background:var(--rouge,#C8102E); color:#fff; border-color:var(--rouge,#C8102E); }
    .adm-panel { display:none; }
    .adm-panel.active { display:block; }
    .adm-card { background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
    .adm-card h2 { font-family:'Bebas Neue',sans-serif; font-size:1.4rem; color:var(--navy,#1A1F3A); margin-bottom:16px; letter-spacing:0.06em; border-bottom:2px solid var(--rouge,#C8102E); padding-bottom:8px; }
    /* Forms */
    .adm-form { display:grid; gap:12px; }
    .adm-form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .adm-form label { font-size:0.72rem; font-weight:900; letter-spacing:0.14em; text-transform:uppercase; color:#888; display:block; margin-bottom:5px; }
    .adm-form input, .adm-form select, .adm-form textarea { width:100%; padding:10px 14px; border:1px solid #DDD; border-radius:8px; font-size:0.94rem; font-family:'Barlow',sans-serif; transition:border .18s; box-sizing:border-box; }
    .adm-form input:focus, .adm-form select:focus { outline:none; border-color:var(--rouge,#C8102E); }
    .btn-adm { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; border-radius:8px; font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:0.88rem; letter-spacing:0.1em; text-transform:uppercase; border:none; cursor:pointer; transition:all .18s; }
    .btn-adm-primary { background:var(--rouge,#C8102E); color:#fff; }
    .btn-adm-primary:hover { background:#e0002e; }
    .btn-adm-danger  { background:#ff4444; color:#fff; font-size:0.78rem; padding:6px 12px; }
    .btn-adm-danger:hover { background:#cc0000; }
    .btn-adm-toggle  { background:#f0c040; color:var(--navy); font-size:0.78rem; padding:6px 12px; }
    .btn-adm-edit    { background:#6B8EFF; color:#fff; font-size:0.78rem; padding:6px 12px; }
    /* Tables */
    .adm-table { width:100%; border-collapse:collapse; }
    .adm-table th { background:var(--navy,#1A1F3A); color:#fff; padding:10px 14px; font-family:'Barlow Condensed',sans-serif; font-size:0.78rem; letter-spacing:0.14em; text-transform:uppercase; text-align:left; }
    .adm-table td { padding:10px 14px; border-bottom:1px solid #EBEBEB; font-size:0.9rem; vertical-align:middle; }
    .adm-table tr:hover td { background:#fff5f5; }
    .badge-active   { display:inline-block; padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:900; letter-spacing:0.1em; text-transform:uppercase; background:#d4edda; color:#155724; }
    .badge-inactive { display:inline-block; padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:900; letter-spacing:0.1em; text-transform:uppercase; background:#f8d7da; color:#721c24; }
    /* Flash */
    .flash { padding:12px 18px; border-radius:8px; margin-bottom:18px; font-weight:600; background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    /* Nav categories */
    .nav-cat-block { margin-bottom:20px; }
    .nav-cat-title { font-family:'Bebas Neue',sans-serif; font-size:1.1rem; color:var(--rouge,#C8102E); letter-spacing:0.1em; padding:8px 14px; background:#fff5f5; border-left:4px solid var(--rouge,#C8102E); margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
    /* Drag handle */
    .drag-handle { cursor:grab; color:#CCC; padding:0 6px; font-size:1.1rem; }
    .drag-handle:active { cursor:grabbing; }
    /* Dashboard stats */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; }
    .stat-card { text-align:center; padding:22px 16px; background:#fff; border-radius:10px; border-top:4px solid var(--rouge,#C8102E); box-shadow:0 2px 8px rgba(0,0,0,0.05); }
    .stat-num { font-family:'Bebas Neue',sans-serif; font-size:2.4rem; color:var(--rouge,#C8102E); line-height:1; }
    .stat-label { font-size:0.78rem; font-weight:900; letter-spacing:0.12em; text-transform:uppercase; color:#888; margin-top:6px; }
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

    <!-- Flash message -->
    <?php if ($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

    <!-- Tabs -->
    <div class="adm-tabs">
        <a href="?tab=dashboard"   class="adm-tab <?= $tab==='dashboard'  ?'active':'' ?>">📊 Dashboard</a>
        <a href="?tab=matches"     class="adm-tab <?= $tab==='matches'    ?'active':'' ?>">📅 Matchs</a>
        <a href="?tab=resultats"   class="adm-tab <?= $tab==='resultats'  ?'active':'' ?>">⚽ Résultats</a>
        <a href="?tab=classement"  class="adm-tab <?= $tab==='classement' ?'active':'' ?>">🏆 Classement</a>
        <a href="?tab=equipes"     class="adm-tab <?= $tab==='equipes'    ?'active':'' ?>">👥 Équipes</a>
        <a href="?tab=navigation"  class="adm-tab <?= $tab==='navigation' ?'active':'' ?>">🗂 Navigation</a>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         DASHBOARD
    ══════════════════════════════════════════════════════════ -->
    <div class="adm-panel <?= $tab==='dashboard'?'active':'' ?>">
        <div class="adm-card">
            <h2>Vue d'ensemble</h2>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-num"><?= count($matches) ?></div><div class="stat-label">Matchs programmés</div></div>
                <div class="stat-card"><div class="stat-num"><?= count($resultats) ?></div><div class="stat-label">Résultats</div></div>
                <div class="stat-card"><div class="stat-num"><?= count($equipes) ?></div><div class="stat-label">Équipes</div></div>
                <div class="stat-card"><div class="stat-num"><?= array_sum(array_map('count',$nav_all)) ?></div><div class="stat-label">Pages nav</div></div>
            </div>
        </div>
        <div class="adm-card">
            <h2>Prochain match</h2>
            <?php
            $next = $pdo->query("SELECT * FROM matches WHERE date_match >= NOW() ORDER BY date_match ASC LIMIT 1")->fetch();
            if ($next):
            ?>
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <div style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--navy);">RFC Liège <span style="color:var(--rouge);">vs</span> <?= htmlspecialchars($next['equipe_adversaire']) ?></div>
                <div style="color:#666;">📅 <?= (new DateTime($next['date_match']))->format('d/m/Y H:i') ?> · <?= htmlspecialchars($next['stade']) ?></div>
            </div>
            <?php else: ?><p style="color:#888;font-style:italic;">Aucun match programmé.</p><?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         MATCHS
    ══════════════════════════════════════════════════════════ -->
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
                        <form action="../php/delete_match.php" method="POST" onsubmit="return confirm('Supprimer ce match ?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-danger">🗑 Suppr.</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         RÉSULTATS
    ══════════════════════════════════════════════════════════ -->
    <div class="adm-panel <?= $tab==='resultats'?'active':'' ?>">
        <div class="adm-card">
            <h2>Ajouter un résultat</h2>
            <form action="../php/save_result.php" method="POST" class="adm-form">
                <div class="adm-form-row">
                    <div>
                        <label>Équipe domicile</label>
                        <select name="equipe_domicile" required>
                            <?php foreach ($equipes as $eq): ?>
                            <option value="<?= htmlspecialchars($eq['nom']) ?>"><?= htmlspecialchars($eq['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Équipe extérieur</label>
                        <select name="equipe_exterieur" required>
                            <?php foreach ($equipes as $eq): ?>
                            <option value="<?= htmlspecialchars($eq['nom']) ?>"><?= htmlspecialchars($eq['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="adm-form-row">
                    <div><label>Buts domicile</label><input type="number" name="buts_domicile" min="0" value="0" required></div>
                    <div><label>Buts extérieur</label><input type="number" name="buts_exterieur" min="0" value="0" required></div>
                </div>
                <div class="adm-form-row">
                    <div><label>Date du match</label><input type="date" name="date_match" required></div>
                    <div><label>Journée</label><input type="text" name="journee" placeholder="ex: J18"></div>
                </div>
                <div><button type="submit" class="btn-adm btn-adm-primary">➕ Enregistrer le résultat</button></div>
            </form>
        </div>
        <div class="adm-card">
            <h2>Résultats enregistrés</h2>
            <?php if (empty($resultats)): ?>
            <p style="color:#888;font-style:italic;">Aucun résultat.</p>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Journée</th><th>Match</th><th>Score</th><th>Date</th><th></th></tr></thead>
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

    <!-- ══════════════════════════════════════════════════════════
         CLASSEMENT
    ══════════════════════════════════════════════════════════ -->
    <div class="adm-panel <?= $tab==='classement'?'active':'' ?>">
        <div class="adm-card">
            <h2>Classement actuel (calculé automatiquement)</h2>
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
                    <td><strong style="color:var(--rouge);"><?= $s['pts'] ?></strong></td>
                </tr>
                <?php $pos++; endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         ÉQUIPES
    ══════════════════════════════════════════════════════════ -->
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

    <!-- ══════════════════════════════════════════════════════════
         NAVIGATION ← LE NOUVEAU PANNEAU
    ══════════════════════════════════════════════════════════ -->
    <div class="adm-panel <?= $tab==='navigation'?'active':'' ?>">

        <!-- Ajouter une page -->
        <div class="adm-card">
            <h2>➕ Ajouter une page au menu</h2>
            <form action="../php/nav_actions.php" method="POST" class="adm-form" id="formNavAdd">
                <input type="hidden" name="action" value="nav_add">
                <div class="adm-form-row">
                    <div>
                        <label>Catégorie (menu principal)</label>
                        <select name="categorie" required>
                            <?php foreach ($nav_order as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Label affiché dans le dropdown</label>
                        <input type="text" name="label" placeholder="ex: Résultats féminines" required>
                    </div>
                </div>
                <div class="adm-form-row">
                    <div>
                        <label>Dossier (folder)</label>
                        <input type="text" name="folder" placeholder="ex: actu" required>
                        <small style="color:#888;font-size:0.76rem;">Dossier dans pages/club/</small>
                    </div>
                    <div>
                        <label>Slug (nom du fichier sans .php)</label>
                        <input type="text" name="slug" placeholder="ex: resultats-dames" required>
                    </div>
                </div>
                <div class="adm-form-row">
                    <div>
                        <label>Position (ordre dans le dropdown)</label>
                        <input type="number" name="position" value="99" min="0">
                    </div>
                    <div style="align-self:end;">
                        <button type="submit" class="btn-adm btn-adm-primary">➕ Ajouter au menu</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Liste par catégorie -->
        <?php foreach ($nav_order as $cat):
            $pages = $nav_all[$cat] ?? [];
            $folder = $cat_folder[$cat] ?? strtolower($cat);
        ?>
        <div class="adm-card nav-cat-block">
            <h2><?= htmlspecialchars($cat) ?> <span style="font-size:0.75rem;opacity:.5;">(<?= count($pages) ?> pages)</span></h2>

            <?php if (empty($pages)): ?>
            <p style="color:#888;font-style:italic;font-size:0.9rem;">Aucune page dans cette catégorie.</p>
            <?php else: ?>
            <table class="adm-table" id="nav-table-<?= strtolower(str_replace(['É','Â','Ê'],['e','a','e'],$cat)) ?>">
                <thead><tr><th style="width:30px;">⣿</th><th>#</th><th>Label affiché</th><th>Fichier PHP</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($pages as $p): ?>
                <tr data-id="<?= $p['id'] ?>" style="<?= !$p['active']?'opacity:.5;':'' ?>">
                    <td><span class="drag-handle" title="Glisser pour réordonner">⣿</span></td>
                    <td style="color:#888;font-size:0.8rem;"><?= $p['position'] ?></td>
                    <td>
                        <!-- Inline edit label -->
                        <form action="../php/nav_actions.php" method="POST" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="action"    value="nav_edit">
                            <input type="hidden" name="id"        value="<?= $p['id'] ?>">
                            <input type="hidden" name="categorie" value="<?= htmlspecialchars($p['categorie']) ?>">
                            <input type="hidden" name="folder"    value="<?= htmlspecialchars($p['folder']) ?>">
                            <input type="hidden" name="slug"      value="<?= htmlspecialchars($p['slug']) ?>">
                            <input type="hidden" name="position"  value="<?= $p['position'] ?>">
                            <input type="text" name="label" value="<?= htmlspecialchars($p['label']) ?>"
                                   style="border:1px solid #DDD;border-radius:6px;padding:5px 10px;font-size:0.88rem;width:180px;">
                            <button type="submit" class="btn-adm btn-adm-edit" style="padding:5px 10px;">💾</button>
                        </form>
                    </td>
                    <td style="font-family:monospace;font-size:0.82rem;color:var(--rouge);">
                        pages/club/<?= htmlspecialchars($p['folder']) ?>/<strong><?= htmlspecialchars($p['slug']) ?>.php</strong>
                    </td>
                    <td>
                        <span class="badge-<?= $p['active']?'active':'inactive' ?>">
                            <?= $p['active']?'Visible':'Masqué' ?>
                        </span>
                    </td>
                    <td style="display:flex;gap:6px;flex-wrap:wrap;">
                        <!-- Toggle visible/masqué -->
                        <form action="../php/nav_actions.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="nav_toggle">
                            <input type="hidden" name="id"     value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-adm btn-adm-toggle">
                                <?= $p['active']?'🙈 Masquer':'👁 Afficher' ?>
                            </button>
                        </form>
                        <!-- Supprimer -->
                        <form action="../php/nav_actions.php" method="POST" style="display:inline;"
                              onsubmit="return confirm('Supprimer « <?= htmlspecialchars($p['label']) ?> » du menu ?');">
                            <input type="hidden" name="action" value="nav_delete">
                            <input type="hidden" name="id"     value="<?= $p['id'] ?>">
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

        <!-- Info box -->
        <div class="adm-card" style="background:#f0f7ff;border:1px solid #b8d4f0;">
            <h2 style="color:#1a3a6b;">ℹ️ Comment ça marche ?</h2>
            <ul style="font-size:0.9rem;line-height:1.7;color:#333;padding-left:20px;">
                <li><strong>Label</strong> : texte affiché dans le menu déroulant. Modifiable directement dans le tableau.</li>
                <li><strong>Folder</strong> : dossier dans <code>pages/club/</code> (ex: <code>actu</code>, <code>fans</code>).</li>
                <li><strong>Slug</strong> : nom du fichier PHP sans extension (ex: <code>billetterie</code> → <code>pages/club/fans/billetterie.php</code>).</li>
                <li><strong>Masquer</strong> : cache la page du menu sans la supprimer.</li>
                <li><strong>Position</strong> : ordre dans le dropdown — sauvegardée automatiquement par glisser-déposer.</li>
            </ul>
        </div>

    </div><!-- /navigation panel -->

</div><!-- /adm-wrap -->

<script>
// ── Sortable drag-and-drop pour réordonner ──────────────────────────
document.querySelectorAll('[id^="nav-table-"] tbody').forEach(function(tbody) {
    var dragging = null;

    tbody.querySelectorAll('tr').forEach(function(row) {
        row.setAttribute('draggable', true);
        row.addEventListener('dragstart', function() {
            dragging = this;
            setTimeout(function() { row.style.opacity = '0.4'; }, 0);
        });
        row.addEventListener('dragend', function() {
            this.style.opacity = '';
            dragging = null;
            // Sauvegarder l'ordre
            var ids = Array.from(tbody.querySelectorAll('tr')).map(function(r){ return r.dataset.id; });
            fetch('../php/nav_actions.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'action=nav_reorder&ids=' + encodeURIComponent(JSON.stringify(ids))
            });
        });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            var bounding = this.getBoundingClientRect();
            var offset = bounding.y + bounding.height / 2;
            if (e.clientY - offset > 0) {
                this.parentNode.insertBefore(dragging, this.nextSibling);
            } else {
                this.parentNode.insertBefore(dragging, this);
            }
        });
    });
});

// ── Switch tabs via URL hash ────────────────────────────────────────
document.querySelectorAll('.adm-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.adm-tab').forEach(function(t){ t.classList.remove('active'); });
        this.classList.add('active');
    });
});
</script>

</body>
</html>

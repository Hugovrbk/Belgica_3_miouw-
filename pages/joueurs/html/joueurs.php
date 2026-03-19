<?php
ini_set("display_errors", 0);
session_start();
require_once '../../../db.php';

$root        = '../../../';
$active_page = 'joueurs';

try {
    $stmt = $pdo->query("SELECT * FROM joueurs ORDER BY poste ASC, nom ASC");
    $joueurs = $stmt->fetchAll();
} catch (PDOException $e) { $joueurs = []; }

$parPoste = [];
foreach ($joueurs as $j) { $parPoste[$j['poste']][] = $j; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joueurs — Belgica FC 3</title>
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/animations.css">
    <style>
        body { background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%); color:#fff; }
        .page-hero {
            text-align:center; padding: 3rem 1rem 2rem;
            background: linear-gradient(180deg, rgba(139,38,31,0.3) 0%, transparent 100%);
        }
        .page-hero h1 { font-size: 2.5rem; color:#e0a800; margin:0; letter-spacing:2px; }
        .page-hero p  { color:#aaa; margin-top:0.5rem; }

        .poste-section { margin: 2rem auto; max-width: 900px; padding: 0 1rem; }
        .poste-title {
            font-size: 1.1rem; text-transform: uppercase; letter-spacing: 3px;
            color: #e0a800; border-bottom: 2px solid #e0a800; padding-bottom: 0.4rem;
            margin-bottom: 1.5rem; display:flex; align-items:center; gap:0.5rem;
        }

        .joueurs-grid {
            display: flex; flex-wrap: wrap; gap: 1.2rem; justify-content: flex-start;
        }
        .joueur-card {
            background: linear-gradient(145deg, #1e1e3a, #252540);
            border: 1px solid rgba(224,168,0,0.2);
            border-radius: 14px; width: 150px; text-align:center; padding: 1.2rem 0.8rem;
            cursor: default; position:relative; overflow:hidden;
        }
        .joueur-card::before {
            content:''; position:absolute; inset:0;
            background: linear-gradient(135deg, rgba(224,168,0,0.05), transparent);
            opacity:0; transition: opacity 0.3s;
        }
        .joueur-card:hover::before { opacity:1; }
        .joueur-card img {
            width: 90px; height: 90px; border-radius:50%; object-fit:cover;
            border: 3px solid #e0a800; margin-bottom:0.6rem;
        }
        .joueur-card .numero {
            position:absolute; top:8px; right:10px;
            font-size:1.1rem; font-weight:900; color:#e0a800; opacity:0.7;
        }
        .joueur-card .nom { font-weight:700; font-size:0.9rem; margin:0.2rem 0 0.1rem; }
        .joueur-card .poste-label {
            font-size:0.75rem; color:#aaa; text-transform:uppercase; letter-spacing:1px;
        }
        .empty-msg { text-align:center; color:#aaa; padding:3rem; }

        @media (max-width:600px) {
            .joueurs-grid { justify-content:center; }
            .joueur-card { width:130px; }
        }
    </style>
</head>
<body>

    <?php include '../../../pages/includes/header.php'; ?>

    <div class="page-hero reveal">
        <h1>⚽ Effectif</h1>
        <p><?= count($joueurs) ?> joueur<?= count($joueurs) > 1 ? 's' : '' ?> enregistré<?= count($joueurs) > 1 ? 's' : '' ?></p>
    </div>

    <?php
    $icones = ['Gardien'=>'🧤','Défenseur'=>'🛡️','Milieu'=>'⚙️','Attaquant'=>'⚡'];
    $ordre  = ['Gardien','Défenseur','Milieu','Attaquant'];
    foreach ($ordre as $idx => $poste):
        if (empty($parPoste[$poste])) continue;
    ?>
    <div class="poste-section reveal" style="transition-delay:<?= $idx * 0.1 ?>s">
        <div class="poste-title">
            <span><?= $icones[$poste] ?? '⚽' ?></span>
            <?= htmlspecialchars($poste) ?>s
            <span style="color:#666;font-size:0.85rem;letter-spacing:0">(<?= count($parPoste[$poste]) ?>)</span>
        </div>
        <div class="joueurs-grid stagger-list">
            <?php foreach ($parPoste[$poste] as $j): ?>
            <div class="joueur-card">
                <?php if (!empty($j['numero'])): ?><div class="numero">#<?= $j['numero'] ?></div><?php endif; ?>
                <?php if (!empty($j['photo'])): ?>
                    <img src="<?= htmlspecialchars($j['photo']) ?>" alt="photo">
                <?php else: ?>
                    <img src="../../../multimedia/img/logo.png" alt="photo">
                <?php endif; ?>
                <div class="nom"><?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?></div>
                <div class="poste-label"><?= htmlspecialchars($j['poste']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($joueurs)): ?>
        <p class="empty-msg">Aucun joueur enregistré pour le moment.</p>
    <?php endif; ?>

    <div class="footer reveal">
        <div class="réseaux">
            <a href="https://www.instagram.com/acbellinzona_official/"><img src="../../../multimedia/img/resaux/instagram.png" alt="instagram"></a>
            <a href="https://www.tiktok.com/@acbellinzona.official"><img src="../../../multimedia/img/resaux/tiktok.png" alt="tiktok"></a>
            <a href="https://x.com/ACB1904"><img src="../../../multimedia/img/resaux/twitter.png" alt="twitter"></a>
            <a href="https://www.facebook.com/ACBellinzona1904"><img src="../../../multimedia/img/resaux/facebook.png" alt="facebook"></a>
        </div>
    </div>

    <script src="../../../js/animations.js"></script>
</body>
</html>

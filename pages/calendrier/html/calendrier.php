<?php
ini_set("display_errors", 0);
session_start();
require_once '../../../db.php';

$root        = '../../../';
$active_page = 'calendrier';

function formatDateFr($dateString) {
    $date = new DateTime($dateString);
    if (class_exists('IntlDateFormatter')) {
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT, 'Europe/Brussels');
        return $formatter->format($date);
    }
    $mois = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
    return $jours[(int)$date->format('w')] . ' ' . (int)$date->format('j') . ' ' . $mois[(int)$date->format('n')] . ' ' . $date->format('Y') . ' à ' . $date->format('H:i');
}

try {
    $stmt = $pdo->query("SELECT * FROM matches ORDER BY date_match ASC");
    $matches = $stmt->fetchAll();
} catch (PDOException $e) { $matches = []; }

$now    = new DateTime();
$avenir = array_filter($matches, fn($m) => new DateTime($m['date_match']) >= $now);
$passes = array_filter($matches, fn($m) => new DateTime($m['date_match']) < $now);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier — Belgica FC 3</title>
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/animations.css">
    <style>
        body { background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%); color:#fff; }
        .page-hero { text-align:center; padding:3rem 1rem 1rem; }
        .page-hero h1 { font-size:2.5rem; color:#e0a800; margin:0; letter-spacing:2px; }

        .cal-section { max-width:720px; margin:2rem auto; padding:0 1rem; }
        .cal-section-title {
            font-size:1rem; text-transform:uppercase; letter-spacing:3px;
            color:#e0a800; border-bottom:2px solid rgba(224,168,0,0.3);
            padding-bottom:0.4rem; margin-bottom:1.2rem;
        }

        .match-card {
            background: linear-gradient(145deg,#1e1e3a,#252540);
            border: 1px solid rgba(255,255,255,0.08);
            border-left: 4px solid #e0a800;
            border-radius:10px; padding:1.1rem 1.4rem;
            margin-bottom:1rem; color:#fff;
            display:flex; flex-direction:column; gap:0.3rem;
            position:relative; overflow:hidden;
        }
        .match-card.passe { border-left-color:#555; opacity:0.65; }
        .match-card::after {
            content:''; position:absolute; inset:0;
            background: linear-gradient(135deg, rgba(224,168,0,0.04), transparent);
            opacity:0; transition:opacity 0.3s;
        }
        .match-card:hover::after { opacity:1; }
        .match-titre { font-size:1.05rem; font-weight:700; }
        .match-detail { font-size:0.88rem; color:#bbb; }
        .badge-avenir {
            display:inline-block; background:#e0a800; color:#000;
            font-size:0.7rem; font-weight:700; padding:2px 9px;
            border-radius:20px; margin-left:0.6rem; vertical-align:middle;
            animation: pulse 2s ease-in-out infinite;
        }
        .badge-passe {
            display:inline-block; background:#555; color:#ccc;
            font-size:0.7rem; padding:2px 9px;
            border-radius:20px; margin-left:0.6rem; vertical-align:middle;
        }
        .empty-msg { text-align:center; color:#666; padding:2rem; }
    </style>
</head>
<body>

    <?php include '../../../pages/includes/header.php'; ?>

    <div class="page-hero reveal">
        <h1>📅 Calendrier</h1>
        <p style="color:#aaa"><?= count($avenir) ?> match<?= count($avenir) > 1 ? 's' : '' ?> à venir</p>
    </div>

    <?php if (!empty($avenir)): ?>
    <div class="cal-section">
        <div class="cal-section-title reveal">🔜 Prochains matchs</div>
        <div class="stagger-list">
        <?php foreach ($avenir as $m): ?>
            <div class="match-card reveal">
                <div class="match-titre">
                    Belgica FC 3 ⚔ <?= htmlspecialchars($m['equipe_adversaire']) ?>
                    <span class="badge-avenir">À venir</span>
                </div>
                <div class="match-detail">📅 <?= formatDateFr($m['date_match']) ?></div>
                <div class="match-detail">📍 <?= htmlspecialchars($m['stade']) ?></div>
                <?php if (!empty($m['competition'])): ?>
                    <div class="match-detail">🏆 <?= htmlspecialchars($m['competition']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($passes)): ?>
    <div class="cal-section">
        <div class="cal-section-title reveal">✅ Matchs passés</div>
        <div class="stagger-list">
        <?php foreach (array_reverse($passes) as $m): ?>
            <div class="match-card passe reveal">
                <div class="match-titre">
                    Belgica FC 3 ⚔ <?= htmlspecialchars($m['equipe_adversaire']) ?>
                    <span class="badge-passe">Terminé</span>
                </div>
                <div class="match-detail">📅 <?= formatDateFr($m['date_match']) ?></div>
                <div class="match-detail">📍 <?= htmlspecialchars($m['stade']) ?></div>
                <?php if (!empty($m['competition'])): ?>
                    <div class="match-detail">🏆 <?= htmlspecialchars($m['competition']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($matches)): ?>
        <p class="empty-msg">Aucun match programmé.</p>
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

<?php
ini_set("display_errors", 0);
session_start();
require_once '../../../db.php';

$root        = '../../../';
$active_page = 'historique';

try {
    $stmt = $pdo->query("SELECT * FROM historique ORDER BY saison DESC");
    $entrees = $stmt->fetchAll();
} catch (PDOException $e) { $entrees = []; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histoire — Belgica FC 3</title>
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/animations.css">
    <style>
        body { background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%); color:#fff; }
        .page-hero { text-align:center; padding:3rem 1rem 1rem; }
        .page-hero h1 { font-size:2.5rem; color:#e0a800; margin:0; letter-spacing:2px; }

        .timeline { max-width:700px; margin:2rem auto; padding:0 1rem 3rem; position:relative; }
        .timeline::before {
            content:''; position:absolute; left:36px; top:0; bottom:0;
            width:3px; background:linear-gradient(to bottom,#e0a800,rgba(224,168,0,0.1));
            border-radius:2px;
        }

        .timeline-item {
            display:flex; gap:1.5rem; margin-bottom:2.5rem; position:relative;
        }
        .timeline-dot {
            flex-shrink:0; width:72px; height:72px;
            background:linear-gradient(135deg,#e0a800,#c8960a);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-weight:900; font-size:0.72rem; color:#000; text-align:center;
            line-height:1.3; z-index:1;
            box-shadow: 0 4px 15px rgba(224,168,0,0.4);
        }
        .timeline-content {
            background:linear-gradient(145deg,#1e1e3a,#252540);
            border:1px solid rgba(224,168,0,0.15); border-radius:12px;
            padding:1.2rem 1.4rem; flex:1;
            position:relative; overflow:hidden;
        }
        .timeline-content::before {
            content:''; position:absolute; left:-8px; top:24px;
            border-width:8px 8px 8px 0; border-style:solid;
            border-color:transparent rgba(224,168,0,0.15) transparent transparent;
        }
        .timeline-content h3 { color:#e0a800; margin:0 0 0.5rem; font-size:1.05rem; }
        .timeline-content p  { color:#ccc; font-size:0.92rem; margin:0; line-height:1.6; }

        .empty-msg { text-align:center; color:#666; padding:4rem; }

        @media (max-width:600px) {
            .timeline { padding:0 0.5rem 2rem; }
            .timeline::before { left:28px; }
            .timeline-dot { width:56px; height:56px; font-size:0.65rem; }
        }
    </style>
</head>
<body>

    <?php include '../../../pages/includes/header.php'; ?>

    <div class="page-hero reveal">
        <h1>🏛️ Histoire du Club</h1>
        <p style="color:#aaa">Belgica FC 3</p>
    </div>

    <?php if (!empty($entrees)): ?>
    <div class="timeline">
        <?php foreach ($entrees as $i => $e): ?>
        <div class="timeline-item reveal" style="transition-delay:<?= $i * 0.12 ?>s">
            <div class="timeline-dot"><?= htmlspecialchars($e['saison']) ?></div>
            <div class="timeline-content">
                <h3><?= htmlspecialchars($e['titre']) ?></h3>
                <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="empty-msg">Aucune entrée historique pour le moment.</p>
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

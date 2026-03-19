<?php
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_admin']) || $_SESSION['user_admin'] != 1) {
    echo json_encode(['ok' => false, 'error' => 'Accès refusé']); exit;
}

require_once __DIR__ . '/../db.php';

$slug    = trim($_POST['slug'] ?? '');
$updates = json_decode($_POST['updates'] ?? '[]', true);

if (!$slug || !is_array($updates)) {
    echo json_encode(['ok' => false, 'error' => 'Données invalides']); exit;
}

try {
    // Créer la table si elle n'existe pas encore
    $pdo->exec("CREATE TABLE IF NOT EXISTS `page_content` (
        `id`         INT NOT NULL AUTO_INCREMENT,
        `slug`       VARCHAR(120) NOT NULL,
        `contenu`    MEDIUMTEXT DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Ajouter la colonne contenu si elle manque (migration)
    $cols = array_column($pdo->query("SHOW COLUMNS FROM page_content")->fetchAll(), 'Field');
    if (!in_array('contenu', $cols)) {
        $pdo->exec("ALTER TABLE page_content ADD COLUMN `contenu` MEDIUMTEXT DEFAULT NULL");
    }

    // Charger les sections existantes
    $stmt = $pdo->prepare("SELECT contenu FROM page_content WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $row      = $stmt->fetch();
    $sections = json_decode(($row ? $row['contenu'] : null) ?? '[]', true);
    if (!is_array($sections)) $sections = [];

    foreach ($updates as $u) {
        $idx   = (int)($u['idx']   ?? -1);
        $field = $u['field'] ?? '';
        $value = $u['value'] ?? '';

        if ($idx < 0 || !in_array($field, ['titre', 'contenu', 'items'])) continue;

        while (count($sections) <= $idx) {
            $sections[] = ['type' => 'text', 'titre' => '', 'contenu' => ''];
        }

        if ($field === 'items') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) $sections[$idx]['items'] = $decoded;
        } else {
            $sections[$idx][$field] = $value;
        }
    }

    $json = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare("
        INSERT INTO page_content (slug, contenu) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE contenu = VALUES(contenu)
    ");
    $stmt->execute([$slug, $json]);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

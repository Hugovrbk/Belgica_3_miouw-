<?php
/**
 * upload_image.php — Gestionnaire d'upload d'images CMS
 * Accepte un fichier POST['image'] et retourne {"url": "/multimedia/img/uploads/xxx.jpg"}
 */
session_start();
require_once '../../../db.php';
require_once '../../admin/html/check_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    echo json_encode(['error' => 'Aucun fichier envoyé.']);
    exit;
}

$file = $_FILES['image'];

// Vérifications
$allowed_types = ['image/jpeg','image/png','image/webp','image/gif','image/svg+xml'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, WebP ou GIF.']);
    exit;
}

if ($file['size'] > 8 * 1024 * 1024) { // 8 Mo max
    echo json_encode(['error' => 'Fichier trop lourd (max 8 Mo).']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Erreur lors de l\'upload (code ' . $file['error'] . ').']);
    exit;
}

// Dossier de destination
$upload_dir = realpath(__DIR__ . '/../../../multimedia/img/uploads');
if (!$upload_dir) {
    // Créer le dossier s'il n'existe pas
    $dir_path = __DIR__ . '/../../../multimedia/img/uploads';
    if (!mkdir($dir_path, 0755, true)) {
        echo json_encode(['error' => 'Impossible de créer le dossier uploads.']);
        exit;
    }
    $upload_dir = realpath($dir_path);
}

// Générer un nom unique
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$safe_ext = in_array($ext, ['jpg','jpeg','png','webp','gif','svg']) ? $ext : 'jpg';
$filename = uniqid('img_', true) . '.' . $safe_ext;
$dest     = $upload_dir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['error' => 'Impossible de sauvegarder le fichier.']);
    exit;
}

// Retourner l'URL publique
$url = '/Belgica_3_miouw-/multimedia/img/uploads/' . $filename;
echo json_encode(['url' => $url, 'filename' => $filename]);
exit;

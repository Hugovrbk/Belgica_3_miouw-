<?php
session_start();
require_once '../../../db.php';
require_once '../html/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/admin.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$make = isset($_POST['make']) && $_POST['make'] === '1' ? 1 : 0;

if ($id <= 0) {
    $_SESSION['message'] = 'Identifiant utilisateur invalide.';
    $_SESSION['message_type'] = 'error';
    header('Location: ../html/admin.php?tab=users');
    exit;
}

// Prevent self-toggle
if ($id == ($_SESSION['user_id'] ?? 0)) {
    $_SESSION['message'] = 'Vous ne pouvez pas modifier votre propre statut.';
    $_SESSION['message_type'] = 'error';
    header('Location: ../html/admin.php?tab=users');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE user SET isadmin=? WHERE id=?");
    $stmt->execute([$make, $id]);
    $_SESSION['message'] = 'Statut administrateur mis à jour.';
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['message'] = 'Erreur : ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header('Location: ../html/admin.php?tab=users');
exit;

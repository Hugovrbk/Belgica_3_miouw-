<?php
session_start();
require_once '../../../db.php';
require_once '../html/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['actualite_id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM actualites WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message']      = 'Actualité supprimée.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur lors de la suppression.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
header('Location: ../html/admin.php');
exit;

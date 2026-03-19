<?php
session_start();
require_once '../../../db.php';
require_once '../html/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre   = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $date    = $_POST['date_publication'] ?? '';

    if ($titre === '' || $contenu === '' || $date === '') {
        $_SESSION['message']      = 'Tous les champs sont obligatoires.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO actualites (titre, contenu, date_publication) VALUES (?, ?, ?)");
            $stmt->execute([$titre, $contenu, $date]);
            $_SESSION['message']      = 'Actualité ajoutée avec succès.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur lors de l\'ajout.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
header('Location: ../html/admin.php');
exit;

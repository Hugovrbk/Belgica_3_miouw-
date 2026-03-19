<?php
session_start();
require_once '../../../db.php';
require_once '../html/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saison      = trim($_POST['saison'] ?? '');
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($saison === '' || $titre === '' || $description === '') {
        $_SESSION['message']      = 'Tous les champs sont obligatoires.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO historique (saison, titre, description) VALUES (?, ?, ?)");
            $stmt->execute([$saison, $titre, $description]);
            $_SESSION['message']      = 'Entrée historique ajoutée.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur lors de l\'ajout.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
header('Location: ../html/admin.php');
exit;

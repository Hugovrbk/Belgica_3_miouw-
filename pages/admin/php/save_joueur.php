<?php
session_start();
require_once '../../../db.php';
require_once '../html/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $poste  = trim($_POST['poste'] ?? '');
    $numero = $_POST['numero'] !== '' ? (int)$_POST['numero'] : null;
    $photo  = trim($_POST['photo'] ?? '') ?: null;

    if ($nom === '' || $prenom === '' || $poste === '') {
        $_SESSION['message']      = 'Nom, prénom et poste sont obligatoires.';
        $_SESSION['message_type'] = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO joueurs (nom, prenom, poste, numero, photo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $poste, $numero, $photo]);
            $_SESSION['message']      = 'Joueur ajouté avec succès.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message']      = 'Erreur lors de l\'ajout.';
            $_SESSION['message_type'] = 'error';
        }
    }
}
header('Location: ../html/admin.php');
exit;

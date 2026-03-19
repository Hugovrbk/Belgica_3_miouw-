<?php
session_start();
require_once '../../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if (!$u || !$p) {
        header("Location: ../html/login.php?error=1"); exit;
    }

    $st = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $st->execute([$u]);
    $user = $st->fetch();

    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['user_admin'] = $user['isadmin'];

        // Redirection selon le rôle
        if ($user['isadmin'] == 1) {
            header("Location: ../../admin/html/admin.php"); exit;
        } else {
            header("Location: dashboard.php"); exit;
        }
    }

    header("Location: ../html/login.php?error=1"); exit;
}

header("Location: ../html/login.php"); exit;

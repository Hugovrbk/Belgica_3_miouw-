<?php
/**
 * pages/admin/php/nav_actions.php
 * Gestion CRUD de la navigation (nav_pages)
 * Appelé en POST depuis admin.php
 */
session_start();
require_once '../../../db.php';

// Vérification admin
if (!isset($_SESSION['user_admin']) || $_SESSION['user_admin'] != 1) {
    header('Location: ../html/admin.php');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── Ajouter un item ───────────────────────────────────
        case 'nav_add':
            $stmt = $pdo->prepare("INSERT INTO nav_pages (categorie, label, folder, slug, position, active) VALUES (?,?,?,?,?,1)");
            $stmt->execute([
                trim($_POST['categorie']),
                trim($_POST['label']),
                trim($_POST['folder']),
                trim($_POST['slug']),
                (int)($_POST['position'] ?? 0),
            ]);
            $_SESSION['nav_msg'] = '✅ Page ajoutée avec succès.';
            break;

        // ── Modifier un item ──────────────────────────────────
        case 'nav_edit':
            $stmt = $pdo->prepare("UPDATE nav_pages SET categorie=?, label=?, folder=?, slug=?, position=? WHERE id=?");
            $stmt->execute([
                trim($_POST['categorie']),
                trim($_POST['label']),
                trim($_POST['folder']),
                trim($_POST['slug']),
                (int)($_POST['position'] ?? 0),
                (int)$_POST['id'],
            ]);
            $_SESSION['nav_msg'] = '✅ Page mise à jour.';
            break;

        // ── Supprimer un item ─────────────────────────────────
        case 'nav_delete':
            $stmt = $pdo->prepare("DELETE FROM nav_pages WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $_SESSION['nav_msg'] = '🗑 Page supprimée.';
            break;

        // ── Activer / Désactiver ──────────────────────────────
        case 'nav_toggle':
            $stmt = $pdo->prepare("UPDATE nav_pages SET active = 1 - active WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            $_SESSION['nav_msg'] = '🔄 Visibilité modifiée.';
            break;

        // ── Réordonner (glisser-déposer) ─────────────────────
        case 'nav_reorder':
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            $stmt = $pdo->prepare("UPDATE nav_pages SET position=? WHERE id=?");
            foreach ($ids as $pos => $id) {
                $stmt->execute([$pos + 1, (int)$id]);
            }
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
    }
} catch (PDOException $e) {
    $_SESSION['nav_msg'] = '❌ Erreur : ' . $e->getMessage();
}

header('Location: ../html/admin.php?tab=navigation');
exit;

<?php
session_start();
require_once 'includes/db.php';

// Vérifie si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);

    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
}

header('Location: admin.php');
exit;
?>

<?php
session_start();
require_once 'includes/db.php';

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifie que l'ID de l'article est passé en POST
if (!isset($_POST['article_id']) || !is_numeric($_POST['article_id'])) {
    header("Location: profile.php?error=invalid");
    exit();
}

$article_id = intval($_POST['article_id']);
$user_id = $_SESSION['user_id'];

// Vérifie que l'article appartient bien à l'utilisateur
$sql = "SELECT id FROM articles WHERE id = :id AND auteur_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':id' => $article_id,
    ':user_id' => $user_id
]);

if ($stmt->rowCount() === 0) {
    // L'article n'existe pas ou ne vous appartient pas
    header("Location: profile.php?error=unauthorized");
    exit();
}

// Supprime l'article
$sql_delete = "DELETE FROM articles WHERE id = :id AND auteur_id = :user_id";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->execute([
    ':id' => $article_id,
    ':user_id' => $user_id
]);

header("Location: profile.php?deleted=1");
exit();

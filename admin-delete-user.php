<?php
session_start();
require_once 'includes/db.php';

// Vérifie si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Facultatif : empêcher suppression de soi-même
    if ($_SESSION['user_id'] != $user_id) {
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
    }
}

header('Location: admin.php');
exit;
?>

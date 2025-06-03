<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['cart_id'])) {
    header('Location: panier.php');
    exit;
}

$stmt = $coon->prepare("DELETE FROM cart WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$_POST['cart_id'], $_SESSION['user_id']]);

header('Location: panier.php');
exit;

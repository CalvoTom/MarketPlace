<?php
    require_once 'includes/db.php';

    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION["user_id"])) {
        echo "Vous devez être connecté pour voir votre solde.";
        exit();
    }

    $user_id = $_SESSION["user_id"];

    // Requête pour récupérer le solde
    $sql = "SELECT solde FROM utilisateurs WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p><strong>Solde :</strong> " . number_format($user["solde"], 2, ',', ' ') . " €</p>";
    } else {
        echo "Utilisateur introuvable.";
    }
?>

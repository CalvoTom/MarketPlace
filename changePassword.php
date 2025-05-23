<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ancien = isset($_POST["ancien_mdp"]) ? $_POST["ancien_mdp"] : "";
    $nouveau = isset($_POST["nouveau_mdp"]) ? $_POST["nouveau_mdp"] : "";
    $confirmation = isset($_POST["confirmation_mdp"]) ? $_POST["confirmation_mdp"] : "";

    // Vérification de la correspondance des deux nouveaux mots de passe
    if ($nouveau !== $confirmation) {
        $message = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        // Vérification de l'ancien mot de passe
        $stmt = $conn->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = :id");
        $stmt->execute([':id' => $_SESSION["user_id"]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($ancien, $user["mot_de_passe"])) {
            // Mise à jour du mot de passe
            $hash = password_hash($nouveau, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id");
            $update->execute([
                ':mdp' => $hash,
                ':id' => $_SESSION["user_id"]
            ]);

            header("Location: profile.php?success=1");
            exit();
        } else {
            $message = "Ancien mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Changer le mot de passe</h2>
<?php if ($message): ?>
    <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
    <label>Ancien mot de passe :</label><br>
    <input type="password" name="ancien_mdp" required><br><br>

    <label>Nouveau mot de passe :</label><br>
    <input type="password" name="nouveau_mdp" required><br><br>

    <label>Confirmer le nouveau mot de passe :</label><br>
    <input type="password" name="confirmation_mdp" required><br><br>

    <input type="submit" value="Changer le mot de passe">
</form>
</body>
</html>

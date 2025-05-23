<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mot_de_passe = $_POST["mot_de_passe"] ?? "";

    $stmt = $conn->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mot_de_passe, $user["mot_de_passe"])) {
        $_SESSION["profil_edit_authorized"] = true;
        header("Location: edit.php");
        exit();
    } else {
        $message = "Mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification mot de passe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Confirmez votre identité</h2>
<?php if ($message): ?>
    <p style="color: red;"><?= $message ?></p>
<?php endif; ?>
<form method="post">
    <label for="mot_de_passe">Mot de passe :</label><br>
    <input type="password" name="mot_de_passe" required><br><br>
    <input type="submit" value="Continuer">
</form>
</body>
</html>

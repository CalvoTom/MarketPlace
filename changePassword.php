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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Changer le mot de passe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Password Change Section -->
        <section class="password-section">
            <!-- Password Header -->
            <div class="password-header">
                <div class="password-icon">🔑</div>
                <div>
                    <h1 class="password-title">Changer le mot de passe</h1>
                    <p class="password-subtitle">Renforcez la sécurité de votre compte</p>
                </div>
            </div>

            <!-- Password Form -->
            <div class="password-form">
                <div class="security-tips">
                    <h4>💡 Conseils pour un mot de passe sécurisé :</h4>
                    <ul>
                        <li>Au moins 8 caractères</li>
                        <li>Mélange de lettres majuscules et minuscules</li>
                        <li>Au moins un chiffre et un caractère spécial</li>
                        <li>Évitez les mots du dictionnaire</li>
                    </ul>
                </div>

                <?php if ($message): ?>
                    <div class="message error-message">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label class="form-label" for="ancien_mdp">Mot de passe actuel</label>
                        <input 
                            type="password" 
                            id="ancien_mdp"
                            name="ancien_mdp" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe actuel"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nouveau_mdp">Nouveau mot de passe</label>
                        <input 
                            type="password" 
                            id="nouveau_mdp"
                            name="nouveau_mdp" 
                            class="form-input" 
                            placeholder="Entrez votre nouveau mot de passe"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmation_mdp">Confirmer le nouveau mot de passe</label>
                        <input 
                            type="password" 
                            id="confirmation_mdp"
                            name="confirmation_mdp" 
                            class="form-input" 
                            placeholder="Confirmez votre nouveau mot de passe"
                            required
                        >
                    </div>

                    <div class="form-actions">
                        <a href="edit.php" class="btn-cancel">Annuler</a>
                        <button type="submit" class="btn-save">Changer le mot de passe</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        // Input focus effects only
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
                this.parentNode.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>

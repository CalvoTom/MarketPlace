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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Vérification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link">ARTICLES</a>
                <a href="#" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link active">PROFILE</a>
                    <a href="articleLike.php" class="nav-link nav-heart">❤️</a>
                <?php endif; ?>
            </div>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary">Mon Profil</a>
                    <a href="vente.php" class="btn-primary">Vends tes articles !</a>
                <?php else: ?>
                    <a href="register.php" class="btn-secondary">S'inscrire</a>
                    <a href="login.php" class="btn-primary">Se connecter</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Verification Section -->
        <section class="verification-section">
            <div class="verification-card">
                <div class="security-icon">🔒</div>
                <h1 class="verification-title">Confirmez votre identité</h1>
                <p class="verification-subtitle">
                    Pour votre sécurité, veuillez saisir votre mot de passe actuel<br>
                    avant de modifier vos informations personnelles.
                </p>

                <?php if ($message): ?>
                    <div class="message error-message">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">Mot de passe actuel</label>
                        <input 
                            type="password" 
                            id="mot_de_passe"
                            name="mot_de_passe" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="verify-button">Continuer</button>
                    <a href="profile.php" class="cancel-link">Annuler et retourner au profil</a>
                </form>
            </div>
        </section>
    </div>

    <script>
        // Input focus effects only
        document.getElementById('mot_de_passe').addEventListener('focus', function() {
            this.parentNode.style.transform = 'translateY(-2px)';
            this.parentNode.style.transition = 'transform 0.2s ease';
        });

        document.getElementById('mot_de_passe').addEventListener('blur', function() {
            this.parentNode.style.transform = 'translateY(0)';
        });

        // Auto-focus on password field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mot_de_passe').focus();
        });
    </script>
</body>
</html>

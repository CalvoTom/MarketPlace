<?php
session_start();
require_once 'includes/db.php';

unset($_SESSION["profil_edit_authorized"]);

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Récupération de la photo de profil
$sql = "SELECT profile_picture FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération du solde utilisateur
$sql_solde = "SELECT sold FROM utilisateurs WHERE id = :id";
$stmt_solde = $conn->prepare($sql_solde);
$stmt_solde->execute([':id' => $_SESSION["user_id"]]);
$user_solde = $stmt_solde->fetch(PDO::FETCH_ASSOC);

$solde = $user_solde ? $user_solde["sold"] : 0;

// Vérifier si une image existe
$hasImageInDB = !empty($user['profile_picture']);
$profileImage = $hasImageInDB
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture'])
    : 'img/default.png';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Mon Profil</title>
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
                <a href="profile.php" class="nav-link active">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <!-- Profile Section -->
        <section class="profile-section fade-in">
            <!-- Profile Header -->
            <div class="profile-header">
                <img src="<?= $profileImage ?>" alt="Photo de profil" class="profile-avatar">
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($_SESSION["prenom"] . ' ' . $_SESSION["nom"]) ?></h1>
                    <p class="profile-email"><?= htmlspecialchars($_SESSION["email"]) ?></p>
                    <div class="profile-actions">
                        <a href="edit.php" class="btn-edit">
                            <span>✏️</span>
                            Modifier mon profil
                        </a>

                        <form method="get" class="btn-logout" action="">
                            <input style="all: unset;" type="submit" name="logout" value="🚪 Se déconnecter">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- Informations personnelles -->
                <div class="detail-card">
                    <h3>Informations personnelles</h3>
                    <div class="detail-item">
                        <span class="detail-label">Prénom</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["prenom"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nom</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["nom"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["email"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Membre depuis</span>
                        <span class="detail-value">Janvier 2024</span>
                    </div>
                </div>

                <!-- Solde et finances -->
                <div class="detail-card">
                    <h3>Mon portefeuille</h3>
                    <div class="solde-display">
                        <div class="solde-amount">
                            <?= number_format($solde, 2, ',', ' ') ?> €
                        </div>
                        <div class="solde-label">Solde disponible</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Animation d'entrée pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.detail-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Effet hover sur les cartes
        document.querySelectorAll('.detail-card, .stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Animation du solde
        function animateNumber(element, target) {
            const start = 0;
            const duration = 1000;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = start + (target - start) * progress;
                
                element.textContent = current.toFixed(2).replace('.', ',') + ' €';
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }
    </script>
</body>
</html>

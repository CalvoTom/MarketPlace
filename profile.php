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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #FBF9F5;
            position: relative;
            width: 100%;
            min-height: 100vh;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            position: relative;
        }

/* Navigation */
.navbar {
            position: absolute;
            width: 1344px;
            height: 54px;
            left: 48px;
            top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .logo {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            text-decoration: none;
            color: #000000;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-link {
            font-weight: 400;
            font-size: 16px;
            line-height: 19px;
            color: #1E1D19;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
        }

        .nav-link:hover {
            color: #F8582E;
        }

        .nav-buttons {
            display: flex;
            gap: 24px;
        }

        .btn-secondary {
            width: 117px;
            height: 46px;
            border: 1px solid #F8582E;
            border-radius: 8px;
            background: transparent;
            color: #1E1D19;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: #F8582E;
            color: #FBF9F5;
        }

        .btn-primary {
            width: 199px;
            height: 46px;
            background: #F8582E;
            border-radius: 8px;
            border: none;
            color: #FBF9F5;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            background: #e04a26;
        }

        /* Profile Section */
        .profile-section {
            position: absolute;
            top: 134px;
            left: 48px;
            width: 1344px;
            min-height: 600px;
        }

        .profile-header {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 48px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 48px;
        }

        .profile-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #F8582E;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            color: #000000;
            margin-bottom: 16px;
        }

        .profile-email {
            font-size: 18px;
            color: #666;
            margin-bottom: 32px;
        }

        .profile-actions {
            display: flex;
            gap: 16px;
        }

        .btn-edit {
            padding: 12px 24px;
            background: #F8582E;
            color: #FBF9F5;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit:hover {
            background: #e04a26;
        }

        .btn-logout {
            padding: 12px 24px;
            background: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: #dc3545;
            color: white;
        }

        /* Profile Details */
        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .detail-card {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 32px;
        }

        .detail-card h3 {
            font-weight: 700;
            font-size: 24px;
            color: #000000;
            margin-bottom: 24px;
            border-bottom: 2px solid #F8582E;
            padding-bottom: 8px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .detail-value {
            color: #666;
            text-align: right;
        }

        /* Solde Section */
        .solde-display {
            background: linear-gradient(135deg, #F8582E, #e04a26);
            color: white;
            text-align: center;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .solde-amount {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .solde-label {
            font-size: 14px;
            opacity: 0.9;
        }


        /* Responsive */
        @media (max-width: 1440px) {
            .container {
                max-width: 100%;
                padding: 0 24px;
            }
            
            .navbar {
                width: calc(100% - 48px);
                left: 24px;
            }
            
            .profile-section {
                left: 24px;
                width: calc(100% - 48px);
            }
        }

        @media (max-width: 1024px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-details {
                grid-template-columns: 1fr;
            }
            
            .profile-name {
                font-size: 36px;
                line-height: 44px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                height: auto;
                gap: 16px;
                padding: 16px;
            }
            
            .nav-links {
                order: 2;
            }
            
            .nav-buttons {
                order: 3;
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            
            .btn-secondary,
            .btn-primary {
                width: 100%;
            }
            
            .profile-section {
                top: 200px;
            }

            .profile-header {
                padding: 24px;
            }

            .profile-avatar {
                width: 150px;
                height: 150px;
            }

            .profile-actions {
                flex-direction: column;
                width: 100%;
            }

        }

        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="#" class="nav-link">ARTICLES</a>
                <a href="#" class="nav-link">PANIER</a>
                <a href="profile" class="nav-link active">PROFILE</a>
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
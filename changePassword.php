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
            color: #000000;
            text-decoration: none;
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

        .nav-link:hover {
            color: #F8582E;
        }

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
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

        /* Password Change Section */
        .password-section {
            position: absolute;
            top: 134px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            min-height: 500px;
        }

        .password-header {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 32px 48px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .password-icon {
            width: 60px;
            height: 60px;
            background: #F8582E;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .password-title {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #000000;
        }

        .password-subtitle {
            font-size: 16px;
            color: #666;
            margin-top: 8px;
        }

        .password-form {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 48px;
        }

        .security-tips {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 32px;
        }

        .security-tips h4 {
            color: #856404;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .security-tips ul {
            color: #856404;
            font-size: 12px;
            margin-left: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
        }

        .form-label {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #F8582E;
            border-radius: 8px;
            padding: 0 16px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #e04a26;
            box-shadow: 0 0 0 3px rgba(248, 88, 46, 0.1);
        }

        .form-input::placeholder {
            color: #999;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-save {
            padding: 12px 32px;
            background: #F8582E;
            color: #FBF9F5;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-save:hover {
            background: #e04a26;
        }

        .btn-cancel {
            padding: 12px 32px;
            background: transparent;
            color: #666;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .error-message {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
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
            
            .password-section {
                top: 200px;
                width: calc(100% - 48px);
                left: 50%;
                transform: translateX(-50%);
            }

            .password-form {
                padding: 24px;
            }

            .password-header {
                padding: 24px;
                flex-direction: column;
                text-align: center;
            }

            .form-actions {
                flex-direction: column;
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
                <a href="profile.php" class="nav-link active">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

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
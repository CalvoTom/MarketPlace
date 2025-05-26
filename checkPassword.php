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

        /* Verification Section */
        .verification-section {
            position: absolute;
            top: 134px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            min-height: 400px;
        }

        .verification-card {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 48px;
            text-align: center;
        }

        .security-icon {
            width: 80px;
            height: 80px;
            background: #F8582E;
            border-radius: 50%;
            margin: 0 auto 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
        }

        .verification-title {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #000000;
            margin-bottom: 16px;
        }

        .verification-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
            text-align: left;
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

        .verify-button {
            width: 100%;
            height: 46px;
            background: #F8582E;
            border-radius: 8px;
            border: none;
            color: #FBF9F5;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-bottom: 16px;
        }

        .verify-button:hover {
            background: #e04a26;
        }

        .cancel-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .cancel-link:hover {
            color: #F8582E;
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
            
            .verification-section {
                top: 200px;
                width: calc(100% - 48px);
                left: 50%;
                transform: translateX(-50%);
            }

            .verification-card {
                padding: 32px 24px;
            }

            .verification-title {
                font-size: 24px;
                line-height: 32px;
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
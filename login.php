<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $mot_de_passe = $_POST["mot_de_passe"];

    if (!empty($email) && !empty($mot_de_passe)) {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION["user_id"] = $utilisateur["id"];
            $_SESSION["email"] = $utilisateur["email"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["prenom"] = $utilisateur["prenom"];

            header("Location: index.php");
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Connexion</title>
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
            height: 739px;
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
            text-decoration: none;
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
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

        /* Login Section */
        .login-section {
            position: absolute;
            top: 134px;
            left: 48px;
            width: 1344px;
            height: 557px;
            display: flex;
            gap: 128px;
        }

        .login-illustration {
            width: 663px;
            height: 557px;
            background: linear-gradient(45deg, #F8582E, #e04a26);
            background-image: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(255,255,255,0.1) 10px,
                    rgba(255,255,255,0.1) 20px
                );
            filter: drop-shadow(0px 5px 10px rgba(0, 0, 0, 0.15));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        .login-form-container {
            width: 440px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-top: 0;
        }

        .back-link {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #F8582E;
        }

        .back-link::before {
            content: "← ";
            margin-right: 8px;
        }

        .welcome-title {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            color: #000000;
            margin: 18px 0;
        }

        .create-account-link {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            text-decoration: underline;
            text-align: right;
            cursor: pointer;
            transition: color 0.3s ease;
            margin-top: 8px;
        }

        .create-account-link:hover {
            color: #F8582E;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            margin-top: 10px;
            font-size: 16px;
            line-height: 19px;
            color: #000000;
        }

        .form-input {
            width: 100%;
            height: 43px;
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

        .forgot-password-link {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            text-decoration: underline;
            text-align: right;
            cursor: pointer;
            transition: color 0.3s ease;
            margin-top: 10px;
            margin-bottom: 24px;
        }

        .forgot-password-link:hover {
            color: #F8582E;
        }

        .login-button {
            width: 100%;
            height: 43px;
            margin-top: 16px;
            background: #F8582E;
            border-radius: 8px;
            border: none;
            color: #FBF9F5;
            font-size: 16px;
            font-weight: 400;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-button:hover {
            background: #e04a26;
        }

        .login-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }

        .error-message {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .success-message {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
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
            
            .login-section {
                left: 24px;
                width: calc(100% - 48px);
            }
        }

        @media (max-width: 1024px) {
            .login-section {
                flex-direction: column;
                gap: 48px;
                height: auto;
            }
            
            .login-illustration {
                width: 100%;
                height: 300px;
            }
            
            .login-form-container {
                width: 100%;
                max-width: 440px;
                margin: 0 auto;
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
            
            .welcome-title {
                font-size: 36px;
                line-height: 44px;
            }
            
            .login-section {
                top: 200px;
            }

            .container {
                height: auto;
                min-height: 100vh;
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

        .shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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
                <a href="profile" class="nav-link">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary active">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <!-- Login Section -->
        <section class="login-section fade-in">
            <div class="login-illustration">
                <div>Connectez-vous à votre compte</div>
            </div>
            
            <div class="login-form-container">
                <a href="index.php" class="back-link">back to website</a>
                
                <h1 class="welcome-title">Welcome</h1>
                

                <?php if ($error): ?>
                    <div class="message error-message shake">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" class="login-form" method="post" action="">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Entrez votre email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">Password</label>
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>
                    
                    <a href="#" class="forgot-password-link" onclick="forgotPassword()">Forgot password ?</a>
                    
                    <button type="submit" class="login-button">Login</button>
                </form>

                <a href="register.php" class="create-account-link">Create a free account</a>

            </div>
        </section>
    </div>

    <script>
        // Form validation
        function validateForm(form) {
            const email = form.querySelector('input[name="email"]').value.trim();
            const motDePasse = form.querySelector('input[name="mot_de_passe"]').value;

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showClientError('Veuillez entrer une adresse email valide');
                return false;
            }

            // Password validation
            if (motDePasse.length === 0) {
                showClientError('Le mot de passe est obligatoire');
                return false;
            }

            return true;
        }

        // Client-side error display
        function showClientError(message) {
            // Remove existing error messages
            const existingError = document.querySelector('.client-error-message');
            if (existingError) {
                existingError.remove();
            }

            // Create error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error-message client-error-message shake';
            errorDiv.textContent = message;

            // Insert error message
            const form = document.querySelector('.login-form');
            form.insertBefore(errorDiv, form.firstChild);

            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        // Form submission handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const button = this.querySelector('.login-button');
            const originalText = button.textContent;
            button.textContent = 'Connexion...';
            button.disabled = true;

            // If validation passes, let the form submit normally
            // The PHP will handle the server-side processing
        });

        // Forgot password function
        function forgotPassword() {
            alert('Fonctionnalité de récupération de mot de passe à venir...');
            // Here you would redirect to forgot password page
            // window.location.href = 'forgot-password.php';
        }

        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
                this.parentNode.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });

            // Real-time validation feedback
            input.addEventListener('input', function() {
                this.style.borderColor = '#F8582E';
                
                // Remove client error messages when user starts typing
                const clientError = document.querySelector('.client-error-message');
                if (clientError) {
                    clientError.remove();
                }
            });
        });

        // Auto-hide server messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message:not(.client-error-message)');
            messages.forEach(message => {
                setTimeout(() => {
                    if (message.parentNode) {
                        message.style.opacity = '0';
                        message.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            message.remove();
                        }, 300);
                    }
                }, 5000);
            });
        });

        // Button hover effects
        document.querySelectorAll('button, .btn-primary, .btn-secondary').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                }
            });
        });

        // Enter key submission
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.activeElement.tagName === 'INPUT') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>
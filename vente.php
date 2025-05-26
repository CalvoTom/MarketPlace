<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $image_link = $_POST['image_link'] ?? null;
    $author_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

    if ($name && $price) {
        $stmt = $conn->prepare('INSERT INTO Article (name, description, price, author_id, image_link, publication_date) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $description, $price, $author_id, $image_link]);
        $message = "Article ajouté avec succès.";
    } else {
        $message = "Les champs nom et prix sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Vendre un article</title>
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

        /* Sell Section */
        .sell-section {
            position: absolute;
            top: 134px;
            left: 48px;
            width: 1344px;
            min-height: 600px;
        }

        .sell-header {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 32px 48px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .sell-icon {
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

        .sell-title {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #000000;
        }

        .sell-subtitle {
            font-size: 16px;
            color: #666;
            margin-top: 8px;
        }

        .user-info {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #F8582E;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #000;
            margin-bottom: 4px;
        }

        .user-status {
            font-size: 12px;
            color: #28a745;
        }

        .sell-form {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 48px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 48px;
            margin-bottom: 32px;
        }

        .form-fields {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            font-weight: 600;
        }

        .required {
            color: #F8582E;
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

        .form-textarea {
            width: 100%;
            min-height: 120px;
            background: #FFFFFF;
            border: 1px solid #F8582E;
            border-radius: 8px;
            padding: 16px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            resize: vertical;
            transition: all 0.3s ease;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #e04a26;
            box-shadow: 0 0 0 3px rgba(248, 88, 46, 0.1);
        }

        .price-input {
            position: relative;
        }

        .price-input::before {
            content: '€';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 600;
            pointer-events: none;
        }

        .price-input input {
            padding-right: 40px;
        }

        .image-preview-section {
            text-align: center;
        }

        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #F8582E;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            background: #fff;
            overflow: hidden;
        }

        .preview-placeholder {
            color: #666;
            font-size: 14px;
            text-align: center;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-tips {
            font-size: 12px;
            color: #666;
            text-align: left;
        }

        .tips-title {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .tips-list {
            margin-left: 16px;
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
            
            .sell-section {
                left: 24px;
                width: calc(100% - 48px);
            }
        }

        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            
            .image-preview-section {
                order: -1;
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
            
            .sell-section {
                top: 200px;
            }

            .sell-form {
                padding: 24px;
            }

            .sell-header {
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">PROFILE</a>
                <?php endif; ?>
            </div>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary">Mon Profil</a>
                    <a href="vente.php" class="btn-primary active">Vends tes articles !</a>
                <?php else: ?>
                    <a href="register.php" class="btn-secondary">S'inscrire</a>
                    <a href="login.php" class="btn-primary">Se connecter</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Sell Section -->
        <section class="sell-section">
            <!-- Sell Header -->
            <div class="sell-header">
                <div class="sell-icon">💰</div>
                <div>
                    <h1 class="sell-title">Vendre un article</h1>
                    <p class="sell-subtitle">Mettez en vente vos articles en quelques clics</p>
                </div>
            </div>

            <!-- User Info -->
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <div class="user-name">
                        Connecté en tant que : <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?> <?= htmlspecialchars($_SESSION['nom'] ?? 'Utilisateur') ?>
                    </div>
                </div>
            </div>

            <!-- Sell Form -->
            <div class="sell-form">
                <?php if ($message): ?>
                    <div class="message <?= strpos($message, 'succès') !== false ? 'success-message' : 'error-message' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-grid">
                        <!-- Form Fields -->
                        <div class="form-fields">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    Nom de l'article <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="form-input" 
                                    placeholder="Ex: iPhone 13, Chaussures Nike..."
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-textarea" 
                                    placeholder="Décrivez votre article en détail : état, caractéristiques, raison de la vente..."
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="price">
                                    Prix <span class="required">*</span>
                                </label>
                                <div class="price-input">
                                    <input 
                                        type="number" 
                                        id="price" 
                                        name="price" 
                                        class="form-input" 
                                        step="0.01" 
                                        min="0"
                                        placeholder="0.00"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="image_link">Lien de l'image</label>
                                <input 
                                    type="url" 
                                    id="image_link" 
                                    name="image_link" 
                                    class="form-input" 
                                    placeholder="https://exemple.com/image.jpg"
                                >
                            </div>
                        </div>

                        <!-- Image Preview Section -->
                        <div class="image-preview-section">
                            <div class="image-preview" id="imagePreview">
                                <div class="preview-placeholder">
                                    📷<br>
                                    Aperçu de l'image<br>
                                    <small>Collez un lien d'image pour voir l'aperçu</small>
                                </div>
                            </div>
                            
                            <div class="image-tips">
                                <div class="tips-title">💡 Conseils pour de bonnes photos :</div>
                                <ul class="tips-list">
                                    <li>Utilisez un bon éclairage</li>
                                    <li>Montrez l'article sous plusieurs angles</li>
                                    <li>Évitez les images floues</li>
                                    <li>Mettez en valeur les détails importants</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="index.php" class="btn-cancel">Annuler</a>
                        <button type="submit" class="btn-save">Publier l'article</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        // Image preview
        document.getElementById('image_link').addEventListener('input', function() {
            const imageUrl = this.value.trim();
            const preview = document.getElementById('imagePreview');
            
            if (imageUrl) {
                // Test if the URL is valid by trying to load the image
                const img = new Image();
                img.onload = function() {
                    preview.innerHTML = `<img src="${imageUrl}" alt="Aperçu" class="preview-image">`;
                };
                img.onerror = function() {
                    preview.innerHTML = `
                        <div class="preview-placeholder">
                            ❌<br>
                            Image non trouvée<br>
                            <small>Vérifiez le lien de l'image</small>
                        </div>
                    `;
                };
                img.src = imageUrl;
            } else {
                preview.innerHTML = `
                    <div class="preview-placeholder">
                        📷<br>
                        Aperçu de l'image<br>
                        <small>Collez un lien d'image pour voir l'aperçu</small>
                    </div>
                `;
            }
        });

        // Input focus effects
        document.querySelectorAll('.form-input, .form-textarea').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
                this.parentNode.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });

        // Auto-format price
        document.getElementById('price').addEventListener('input', function() {
            let value = this.value;
            if (value && !isNaN(value)) {
                // Ensure max 2 decimal places
                if (value.includes('.')) {
                    const parts = value.split('.');
                    if (parts[1] && parts[1].length > 2) {
                        this.value = parseFloat(value).toFixed(2);
                    }
                }
            }
        });
    </script>
</body>
</html>
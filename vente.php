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
    $quantity = $_POST['quantite'] ?? null;
    $image_link = $_POST['image_link'] ?? null;
    $author_id = $_SESSION['user_id'];

    if ($name && $price) {
        $stmt = $conn->prepare('INSERT INTO articles (nom, description, prix, auteur_id, image_url, date_publication) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $description, $price, $author_id, $image_link]);

        $article_id = $conn->lastInsertId();

        if ($quantity !== null) {
            $stmtStock = $conn->prepare('INSERT INTO stock (article_id, quantite) VALUES (?, ?)');
            $stmtStock->execute([$article_id, $quantity]);
        }

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
                <a href="panier.php" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">PROFILE</a>
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
                                <label class="form-label" for="quantite">
                                    Quantité
                                </label>
                                <div class="quantity-input">
                                    <input 
                                        type="number" 
                                        id="quantite" 
                                        name="quantite" 
                                        class="form-input" 
                                        step="1" 
                                        min="0"
                                        placeholder="0"
                                    >
                                </div>
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

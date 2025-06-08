# 🛍️ MarketPlace

MarketPlace est une application web de type marketplace réalisée en PHP, pensée pour offrir une expérience utilisateur moderne et un design fort, loin des interfaces classiques type Leboncoin ou Amazon. Ce projet met l’accent sur l’ergonomie, la simplicité d’utilisation et l’esthétique, tout en proposant toutes les fonctionnalités essentielles d’une plateforme d’achat et de vente entre particuliers.

## 🚀 Fonctionnalités principales

- 🔐 **Inscription et connexion sécurisées**
  - Création de compte avec photo de profil (image par défaut si non fournie)
  - Authentification avec gestion des erreurs et feedback utilisateur
  - Validation côté client et serveur

- 👤 **Gestion du profil utilisateur**
  - Visualisation et modification des informations personnelles (nom, prénom, email, photo)
  - Sécurisation de la modification par vérification du mot de passe
  - Changement de mot de passe avec confirmation

- 🛒 **Vente d’articles**
  - Formulaire de mise en vente avec nom, description, prix, quantité et image (prévisualisation en temps réel)
  - Conseils pour de bonnes photos
  - Ajout automatique de l’article à la base de données et gestion du stock

- 📦 **Achat et panier**
  - Ajout d’articles au panier depuis la fiche produit
  - Visualisation du panier, suppression d’articles, calcul automatique du total
  - Accès au paiement (simulation) 💳

- ❤️ **Favoris**
  - Ajout/retrait d’articles aux favoris (likes)
  - Page dédiée listant tous les articles aimés par l’utilisateur

- 💬 **Commentaires**
  - Ajout de commentaires sur les articles
  - Affichage des derniers commentaires avec auteur et date

## 🛠️ Technologies utilisées

- **Backend** : PHP (PDO pour la base de données)
- **Frontend** : HTML5, CSS3 (design custom, animations, responsive)
- **Base de données** : MySQL

## ⚙️ Installation et utilisation

1. **Installer un environnement local** comme WAMP Server, XAMPP, Laragon, ou tout autre logiciel d’hébergement local compatible avec PHP/MySQL 🖥️
2. **Cloner le projet** dans le dossier www (ou htdocs) de votre serveur local :
    ```bash
    git clone https://github.com/votre-projet/ProjetPHP.git
    ```
3. **Créer la base de données** et importer la structure (fichier .sql fourni)
4. **Customisé la connexion** à la base dans le fichier includes/db.php
5. **Créer un compte** et profitez de la marketplace ! 🎉🛍️

## 👨‍💻 Auteur
- [@YassineSghaier](https://github.com/syassinehub)
- [@MarciniakLucas](https://github.com/Yukojuni)
- [@HammedKaffa](https://github.com/Ssnakyx)
- [@CalvoTom](https://www.github.com/CalvoTom)

> Ce projet a été conçu dans le cadre d’un projet étudiant visant à la réalisation d’un site e-commerce en PHP.
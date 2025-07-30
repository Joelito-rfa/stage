<?php
require_once 'config.php';
require_once 'auth.php'; // On inclura le fichier d'authentification pour les fonctions de vérification
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Gestion des Paiements</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/favicon.png"/> </head>
<body>
    <header>
        <nav>
            <div class="container">
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="logo">Gestion Paiement</a>
                <ul>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li><a href="<?php echo BASE_URL; ?>dashboard.php">Tableau de bord</a></li>
                        <li><a href="<?php echo BASE_URL; ?>clients/liste.php">Clients</a></li>
                        <li><a href="<?php echo BASE_URL; ?>paiements/liste.php">Paiements</a></li>
                        <li><a href="<?php echo BASE_URL; ?>logout.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
    <?php
    // Affichage des messages de succès/erreur stockés en session (messages flash)
    if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); // Supprime le message après affichage
    endif;

    if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); // Supprime le message après affichage
    endif;
    ?>
<?php
// Ce fichier doit être inclus après config.php qui démarre la session

/**
 * Vérifie si l'utilisateur est connecté.
 * Redirige vers la page de connexion si ce n'est pas le cas.
 */
function require_login() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur est connecté.
 * Redirige vers le tableau de bord si l'utilisateur essaie d'accéder à la page de connexion après s'être connecté.
 */
function redirect_if_logged_in() {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    }
}
?>
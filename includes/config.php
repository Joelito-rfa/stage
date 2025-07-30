<?php
// --- Gestion des erreurs ---
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// --- Constantes de connexion à la base de données ---
define('DB_HOST', 'localhost'); // L'hôte de la base de données
define('DB_USER', 'root');     // Ton nom d'utilisateur pour la base de données
define('DB_PASS', '');         // Ton mot de passe pour la base de données
define('DB_NAME', 'gestion_paiement_db'); // Le nom de la base de données que tu as créée

// --- Connexion à la base de données avec PDO ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- URL de base du projet ---
// Adapte cette URL à l'endroit où ton projet est accessible sur ton serveur web
define('BASE_URL', 'http://localhost/payfiscal/');

// --- Démarrage de la session PHP ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
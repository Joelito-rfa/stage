<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

require_login();

// Définit BASE_URL si pas défini (à adapter si besoin)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/payfiscal/'); // Assurez-vous que ce chemin correspond à votre installation
}

$id_paiement = $_GET['id'] ?? null;

if ($id_paiement) {
    try {
        // Préparer et exécuter la suppression
        $stmt = $pdo->prepare("DELETE FROM paiements WHERE id_paiement = :id");
        $stmt->execute([':id' => $id_paiement]);

        // Vérifier si une ligne a été affectée (si le paiement existait)
        if ($stmt->rowCount() > 0) {
            // Redirection vers la liste avec un message de succès (via un paramètre d'URL simple)
            header("Location: " . BASE_URL . "paiements/liste.php?success=deleted");
            exit();
        } else {
            // Redirection vers la liste avec un message d'erreur si le paiement n'a pas été trouvé
            header("Location: " . BASE_URL . "paiements/liste.php?error=notfound");
            exit();
        }

    } catch (PDOException $e) {
        // Gérer les erreurs de base de données
        header("Location: " . BASE_URL . "paiements/liste.php?error=db_error&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // ID manquant, rediriger avec une erreur
    header("Location: " . BASE_URL . "paiements/liste.php?error=no_id");
    exit();
}
?>
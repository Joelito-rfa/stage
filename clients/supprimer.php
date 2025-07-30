<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $client_id = $_GET['id'];

    if (!is_numeric($client_id)) {
        $_SESSION['error_message'] = "ID client invalide.";
        header('Location: ' . BASE_URL . 'clients/liste.php');
        exit;
    }

    try {
        // La contrainte FOREIGN KEY ON DELETE CASCADE dans la base de données
        // s'occupera de supprimer automatiquement les paiements liés à ce client.
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id_client = :id_client");
        $stmt->execute([':id_client' => $client_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Client et ses paiements associés supprimés avec succès.";
        } else {
            $_SESSION['error_message'] = "Client non trouvé ou impossible à supprimer.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression du client : " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Requête de suppression invalide.";
}

header('Location: ' . BASE_URL . 'clients/liste.php');
exit;
?>
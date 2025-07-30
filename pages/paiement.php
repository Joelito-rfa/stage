<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant'];
    $description = $_POST['description'];
    $date = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO paiements (user_id, montant, date_paiement, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $montant, $date, $description]);

    echo "Paiement enregistré avec succès.";
}
?>

<form method="POST">
    <input type="number" step="0.01" name="montant" placeholder="Montant" required>
    <textarea name="description" placeholder="Description"></textarea>
    <button type="submit">Enregistrer</button>
</form>

<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

redirect_if_logged_in(); // Redirige si déjà connecté

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe FROM utilisateurs WHERE nom_utilisateur = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user->mot_de_passe)) {
                // Authentification réussie
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user->id_utilisateur;
                $_SESSION['username'] = $user->nom_utilisateur;

                header('Location: ' . BASE_URL . 'dashboard.php');
                exit;
            } else {
                $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error_message = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
            // En production, vous devriez logger $e->getMessage() et non l'afficher.
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Paiements</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <img src="<?php echo BASE_URL; ?>images/logofisca.jpg" alt="Logo Fiscal" class="login-logo">
            <h2 class="login-title">Portail Paiement des Impôts</h2>
        </div>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="index.php" method="post">
            <div class="form-group">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary login-btn">Se connecter</button>
        </form>

        <p class="info-text">Pour plus d'informations, veuillez consulter notre <br><a href="https://www.impots.mg/accueil" class="official-link">impots.mg</a>
        .</p>
<div class="login-footer-dots">
    <span></span>
    <span></span>
</div>
    </div>
</body>
</html>
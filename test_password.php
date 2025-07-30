<?php
// Inclure le fichier de configuration pour s'assurer que la session est démarrée si nécessaire,
// et que le BASE_URL est défini.
require_once 'includes/config.php';

// *** IMPORTANT : Copiez le HASH EXACT de votre base de données ici ***
// C'est le hachage que vous avez confirmé être dans la colonne 'mot_de_passe' pour l'utilisateur 'admin'
$hash_stocke_db = '$2y$10$92fKjF8z/u.T/Q.t2.C.c.O/j7.w9.b.V.X.L/j7.w9.b.V.X.L/j7.w9.b.V.X.L/j7.w9.b.V.X.L.';

// Le mot de passe que l'on essaie de vérifier (celui que vous entrez dans le formulaire de login)
$mot_de_passe_saisi = 'password123';

echo "<h3>Test de validation du mot de passe</h3>";
echo "Mot de passe saisi (en clair) : <code>" . htmlspecialchars($mot_de_passe_saisi) . "</code><br>";
echo "Hash stocké dans la DB : <code>" . htmlspecialchars($hash_stocke_db) . "</code><br><br>";

if (password_verify($mot_de_passe_saisi, $hash_stocke_db)) {
    echo "<p style='color: green; font-weight: bold;'>RÉSULTAT : Le mot de passe saisi correspond au hash stocké ! 🎉</p>";
    echo "<p>Cela indique que la fonction <code>password_verify()</code> fonctionne correctement avec ces deux valeurs.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>RÉSULTAT : Le mot de passe saisi NE correspond PAS au hash stocké ! 😞</p>";
    echo "<p>Cela signifie qu'il y a une incohérence entre ce que vous tapez et ce qui est haché.</p>";
}

echo "<hr><h4>Vérification du Nom d'utilisateur (directement depuis la DB)</h4>";
try {
    $stmt = $pdo->prepare("SELECT nom_utilisateur, mot_de_passe FROM utilisateurs WHERE nom_utilisateur = :username");
    $stmt->execute([':username' => 'admin']);
    $user_from_db = $stmt->fetch();

    if ($user_from_db) {
        echo "<p>Utilisateur 'admin' trouvé dans la base de données.</p>";
        echo "Nom d'utilisateur DB : <code>" . htmlspecialchars($user_from_db->nom_utilisateur) . "</code><br>";
        echo "Mot de passe haché DB : <code>" . htmlspecialchars($user_from_db->mot_de_passe) . "</code><br>";

        if (password_verify($mot_de_passe_saisi, $user_from_db->mot_de_passe)) {
            echo "<p style='color: green; font-weight: bold;'>TEST DB : Le mot de passe saisi correspond au hash récupéré de la DB ! 🎉</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>TEST DB : Le mot de passe saisi NE correspond PAS au hash récupéré de la DB ! 😞</p>";
        }

    } else {
        echo "<p style='color: red;'>Utilisateur 'admin' NON trouvé dans la base de données. Il y a un problème avec l'insertion.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur PDO lors de la vérification DB : " . htmlspecialchars($e->getMessage()) . "</p>";
}

?>
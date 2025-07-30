<?php 
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

$page_title = "Liste des clients";
include_once '../includes/header.php';

$clients = [];
$search_query = trim($_GET['search'] ?? '');
$error_nif = false;

if ($search_query !== '') {
    // Valider le NIF (doit être exactement 10 chiffres)
    if (preg_match('/^\d{10}$/', $search_query)) {
        // Requête sécurisée
        $search_param = $search_query; // on recherche exactement ce NIF, pas LIKE
        try {
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE nif = :search ORDER BY nom ASC");
            $stmt->execute([':search' => $search_param]);
            $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "<p class='error-message'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        $error_nif = true; // NIF invalide
    }
} else {
    // Pas de recherche : afficher tous les clients
    try {
        $stmt = $pdo->query("SELECT * FROM clients ORDER BY nom ASC");
        $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        echo "<p class='error-message'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<style>
    /* ton CSS inchangé */
    .form-floating {
        position: relative;
        margin-bottom: 1rem;
    }
    .form-floating input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: none;
        outline: none;
        font-size: 16px;
    }
    .form-floating label {
        position: absolute;
        top: 10px;
        left: 10px;
        color: #888;
        background-color: white;
        padding: 0 5px;
        transition: all 0.2s ease;
        pointer-events: none;
    }
    .form-floating input:focus + label,
    .form-floating input:not(:placeholder-shown) + label {
        top: -10px;
        left: 8px;
        font-size: 12px;
        color: #0066cc;
    }
    .error-text {
        font-size: 14px;
        color: red;
        margin-top: -0.75rem;
        margin-bottom: 1rem;
    }
    .btn-search {
        padding: 10px 20px;
        background-color: #0066cc;
        border: none;
        color: white;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-search:hover {
        background-color: #004c99;
    }
    .search-container {
        max-width: 300px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }
    th, td {
        padding: 12px;
        border: 1px solid #ccc;
        text-align: left;
    }
    th {
        background-color: #f5f5f5;
    }
</style>

<h2 style="margin-bottom: 20px;">📋 Liste des clients</h2>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="ajouter.php" class="btn btn-success">➕ Ajouter un client</a>

    <form action="" method="GET" class="search-container">
        <div class="form-floating">
            <input 
                type="text" 
                name="search" 
                id="search" 
                class="form-control" 
                placeholder=" " 
                pattern="\d{10}" 
                maxlength="10"
                value="<?php echo htmlspecialchars($search_query); ?>" 
                title="NIF de 10 chiffres"
                <?php if ($error_nif) echo 'style="border-color: red;"'; ?>
            >
            <label for="search">NIF du client (10 chiffres)</label>
        </div>
        <button type="submit" class="btn-search">🔍 Rechercher</button>
        <?php if ($error_nif): ?>
            <div class="error-text">❌ NIF invalide. Doit contenir exactement 10 chiffres.</div>
        <?php endif; ?>
    </form>
</div>

<?php if (!$error_nif): ?>
    <?php if (empty($clients)): ?>
        <div class="alert alert-warning">
            Aucun client trouvé avec ce NIF. <a href="ajouter.php">Ajouter un client ?</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>NIF</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Sexe</th>
                    <th>Profession</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client->nif); ?></td>
                        <td><?php echo htmlspecialchars($client->nom); ?></td>
                        <td><?php echo htmlspecialchars($client->prenom); ?></td>
                        <td><?php echo htmlspecialchars($client->sexe); ?></td>
                        <td><?php echo htmlspecialchars($client->profession); ?></td>
                        <td><?php echo htmlspecialchars($client->telephone); ?></td>
                        <td>
                            <a href="modifier.php?id=<?php echo htmlspecialchars($client->id_client ?? $client->id); ?>" class="btn btn-warning btn-sm">✏️</a>
                            <a href="supprimer.php?id=<?php echo htmlspecialchars($client->id_client ?? $client->id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?');">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>

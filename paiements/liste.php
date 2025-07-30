<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

$page_title = "Liste des paiements";
include_once '../includes/header.php';

$paiements = [];
$search_query = trim($_GET['search'] ?? '');
$error_nif = false;

if ($search_query !== '') {
    if (preg_match('/^\d{10}$/', $search_query)) {
        $search_param = $search_query;
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, c.nom, c.prenom, c.nif 
                FROM paiements p
                JOIN clients c ON p.id_client = c.id_client
                WHERE c.nif = :search
                ORDER BY p.date_paiement DESC
            ");
            $stmt->execute([':search' => $search_param]);
            $paiements = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "<p class='error-message'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        $error_nif = true;
    }
} else {
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.nom, c.prenom, c.nif 
            FROM paiements p
            JOIN clients c ON p.id_client = c.id_client
            ORDER BY p.date_paiement DESC
        ");
        $paiements = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        echo "<p class='error-message'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<style>
.form-floating {
    position: relative;
    margin-bottom: 1rem;
    max-width: 300px;
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
    vertical-align: middle;
}
th {
    background-color: #f5f5f5;
}
.btn-action {
    padding: 5px 10px;
    margin-right: 5px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}
.btn-edit {
    background-color: #f0ad4e;
    color: white;
}
.btn-delete {
    background-color: #d9534f;
    color: white;
}
.btn-edit:hover {
    background-color: #ec971f;
}
.btn-delete:hover {
    background-color: #c9302c;
}
</style>

<h2 style="margin-bottom: 20px;">📋 Liste des paiements</h2>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="enregistrer.php" class="btn btn-success">➕ Enregistrer un nouveau paiement</a>

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
    <?php if (empty($paiements)): ?>
        <div class="alert alert-warning">
            Aucun paiement trouvé<?php echo $search_query ? " pour ce NIF." : "."; ?>
            <a href="enregistrer.php">Enregistrez-en un !</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Paiement</th>
                    <th>NIF Client</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Montant (Ar)</th>
                    <th>Date</th>
                    <th>Motif</th>
                    <th>Mode de paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paiements as $paiement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($paiement->id_paiement); ?></td>
                        <td><?php echo htmlspecialchars($paiement->nif); ?></td>
                        <td><?php echo htmlspecialchars($paiement->nom); ?></td>
                        <td><?php echo htmlspecialchars($paiement->prenom); ?></td>
                        <td><?php echo number_format($paiement->montant, 2, ',', ' '); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($paiement->date_paiement)); ?></td>
                        <td><?php echo htmlspecialchars($paiement->motif); ?></td>
                        <td><?php echo htmlspecialchars($paiement->mode_paiement); ?></td>
                        <td>
                        <a href="modifier.php?id=<?php echo htmlspecialchars($paiement->id_paiement); ?>" class="btn-action btn-edit" title="Modifier">✏️</a>
                        <a href="supprimer.php?id=<?php echo htmlspecialchars($paiement->id_paiement); ?>" class="btn-action btn-delete" title="Supprimer" onclick="return confirm('Confirmer la suppression ?');">🗑️</a>
                        <a href="recu.php?id=<?php echo htmlspecialchars($paiement->id_paiement); ?>" class="btn-action btn-primary" title="Voir le reçu" target="_blank" style="background-color: #007bff; color: white;">🧾</a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>

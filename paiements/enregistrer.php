<?php
require '../includes/auth.php';
require '../includes/config.php';

// Assurez-vous que BASE_URL est défini. Adaptez ce chemin si votre projet est dans un autre sous-dossier de localhost.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/payfiscal/'); 
}

$errors = [];
$values = ['id_client' => '', 'montant' => '', 'motif' => '', 'date' => '', 'mode_paiement' => ''];

try {
    $stmt = $pdo->query("SELECT id_client, nif, nom, prenom FROM clients ORDER BY nom ASC");
    $clients = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $clients = [];
    // En développement, die() est utile. En production, loggez l'erreur et affichez un message générique.
    die("Erreur: Impossible de récupérer la liste des clients. " . $e->getMessage()); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $val) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if (empty($values['id_client'])) {
        $errors['id_client'] = "Veuillez sélectionner un client.";
    }
    if (!is_numeric($values['montant']) || floatval($values['montant']) <= 0) {
        $errors['montant'] = "Le montant doit être un nombre positif.";
    }
    if (empty($values['motif'])) {
        $errors['motif'] = "Le motif est requis.";
    }
    if (empty($values['date'])) {
        $errors['date'] = "La date est requise.";
    }
    if (empty($values['mode_paiement'])) {
        $errors['mode_paiement'] = "Le mode de paiement est requis.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO paiements (id_client, montant, motif, date_paiement, mode_paiement) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$values['id_client'], $values['montant'], $values['motif'], $values['date'], $values['mode_paiement']]);
            
            // --- CORRECTION CLÉ : REDIRECTION VERS LE BON FICHIER AVEC BASE_URL ---
            // Redirige vers la page de liste des paiements après un ajout réussi
            header("Location: " . BASE_URL . "paiements/liste.php");
            exit;

        } catch (PDOException $e) {
            $errors['bdd'] = "Erreur lors de l'enregistrement du paiement : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enregistrer un Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-floating .form-error { font-size: 0.85em; color: red; margin-top: 4px; position: absolute; bottom: -1.4em; left: 0.75em; }
        .form-floating { position: relative; margin-bottom: 2.5rem; }
        select.form-select { height: 3.5rem; padding: 1rem 0.75rem 0 0.75rem; }
        select.form-select:focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25); }
        .form-floating > label { left: 0.75rem; padding: 0 0.25rem; pointer-events: none; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Enregistrer un nouveau paiement</h3>
    
    <?php if (isset($errors['bdd'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['bdd']) ?></div>
    <?php endif; ?>
    
    <form method="post" class="row g-3">

        <div class='col-md-4 position-relative'>
            <div class='form-floating'>
                <select name='id_client' id='id_client_select' class='form-select <?php echo isset($errors['id_client']) ? "is-invalid" : ""; ?>'>
                    <option value='' data-nom="">-- Sélectionnez un NIF --</option>
                    <?php foreach ($clients as $client): ?>
                        <option 
                            value="<?= htmlspecialchars($client->id_client) ?>"
                            data-nom="<?= htmlspecialchars($client->nom . ' ' . $client->prenom) ?>"
                            <?php if ($values['id_client'] == $client->id_client) echo 'selected'; ?>>
                            <?= htmlspecialchars($client->nif) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for='id_client_select'>NIF du Client</label>
                <?php if (isset($errors['id_client'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['id_client']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class='col-md-8 position-relative'>
            <div class='form-floating'>
                <input type="text" id="nom_client_display" class="form-control" placeholder="Nom du client" disabled>
                <label for="nom_client_display">Nom du Client</label>
            </div>
        </div>

        <?php
        function floatingInput($label, $name, $placeholder, $value, $errors, $type = 'text', $col_class = 'col-md-6') {
            $hasError = isset($errors[$name]) ? 'is-invalid' : '';
            echo "
                <div class='$col_class position-relative'>
                    <div class='form-floating'>
                        <input type='$type' name='$name' id='$name' value='" . htmlspecialchars($value) . "' placeholder='$placeholder' class='form-control $hasError'>
                        <label for='$name'>$label</label>
                        " . (isset($errors[$name]) ? "<div class='form-error'>{$errors[$name]}</div>" : "") . "
                    </div>
                </div>
            ";
        }
        
        floatingInput("Montant (Ar)", "montant", "Ex: 150000", $values['montant'], $errors, 'number');
        floatingInput("Motif du paiement", "motif", "Ex: Acompte", $values['motif'], $errors);
        floatingInput("Date du paiement", "date", "Ex: 2025-07-21", $values['date'], $errors, 'date');
        ?>

        <div class='col-md-6 position-relative'>
            <div class='form-floating'>
                <select name='mode_paiement' id='mode_paiement' class='form-select <?php echo isset($errors['mode_paiement']) ? "is-invalid" : ""; ?>'>
                    <option value="">-- Sélectionnez un mode --</option>
                    <option value="Especes" <?php if ($values['mode_paiement'] == 'Especes') echo 'selected'; ?>>Espèces</option>
                    <option value="Virement" <?php if ($values['mode_paiement'] == 'Virement') echo 'selected'; ?>>Virement</option>
                    <option value="Cheque" <?php if ($values['mode_paiement'] == 'Cheque') echo 'selected'; ?>>Chèque</option>
                    <option value="Carte" <?php if ($values['mode_paiement'] == 'Carte') echo 'selected'; ?>>Carte</option>
                </select>
                <label for='mode_paiement'>Mode de paiement</label>
                <?php if (isset($errors['mode_paiement'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['mode_paiement']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-success">Enregistrer le paiement</button>
            <a href="liste.php" class="btn btn-secondary ms-2">Retour</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nifSelect = document.getElementById('id_client_select');
    const nomDisplay = document.getElementById('nom_client_display');

    function updateNomDisplay() {
        const selectedOption = nifSelect.options[nifSelect.selectedIndex];
        const nomComplet = selectedOption ? selectedOption.getAttribute('data-nom') : '';
        nomDisplay.value = nomComplet || '';
    }

    updateNomDisplay();

    nifSelect.addEventListener('change', updateNomDisplay);
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

require_login();

$page_title = "Modifier un Paiement";
include_once '../includes/header.php';

// Définit BASE_URL si pas défini (à adapter si besoin)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/payfiscal/'); // Assurez-vous que ce chemin correspond à votre installation
}

$id_paiement = $_GET['id'] ?? null;
$paiement = null;
$clients = [];
$errors = [];
$success_message = '';

// Récupérer la liste des clients pour le sélecteur
try {
    $stmt_clients = $pdo->query("SELECT id_client, nif, nom, prenom FROM clients ORDER BY nom ASC");
    $clients = $stmt_clients->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $errors['clients'] = "Erreur lors du chargement des clients : " . htmlspecialchars($e->getMessage());
}

// Récupérer les informations du paiement à modifier
if ($id_paiement) {
    try {
        $stmt_paiement = $pdo->prepare("SELECT p.*, c.nif, c.nom, c.prenom FROM paiements p JOIN clients c ON p.id_client = c.id_client WHERE p.id_paiement = :id");
        $stmt_paiement->execute([':id' => $id_paiement]);
        $paiement = $stmt_paiement->fetch(PDO::FETCH_OBJ);

        if (!$paiement) {
            echo "<div class='alert alert-danger'>Paiement non trouvé.</div>";
            exit;
        }
    } catch (PDOException $e) {
        $errors['paiement_load'] = "Erreur lors du chargement du paiement : " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "<div class='alert alert-danger'>ID de paiement manquant.</div>";
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paiement) {
    // Utilisez les valeurs du POST si le formulaire a été soumis, sinon utilisez les valeurs existantes du paiement
    $id_client = trim($_POST['id_client'] ?? $paiement->id_client);
    $montant = trim($_POST['montant'] ?? $paiement->montant);
    $motif = trim($_POST['motif'] ?? $paiement->motif);
    $date_paiement = trim($_POST['date_paiement'] ?? date('Y-m-d', strtotime($paiement->date_paiement))); 
    $mode_paiement = trim($_POST['mode_paiement'] ?? $paiement->mode_paiement);

    // Validation des données
    if (empty($id_client)) {
        $errors['id_client'] = "Veuillez sélectionner un client.";
    }
    if (!is_numeric($montant) || floatval($montant) <= 0) {
        $errors['montant'] = "Le montant doit être un nombre positif.";
    }
    if (empty($motif)) {
        $errors['motif'] = "Le motif est requis.";
    }
    if (empty($date_paiement)) {
        $errors['date_paiement'] = "La date est requise.";
    }
    if (empty($mode_paiement)) {
        $errors['mode_paiement'] = "Le mode de paiement est requis.";
    }

    if (empty($errors)) {
        try {
            $stmt_update = $pdo->prepare("
                UPDATE paiements 
                SET id_client = :id_client, montant = :montant, motif = :motif, date_paiement = :date_paiement, mode_paiement = :mode_paiement
                WHERE id_paiement = :id_paiement
            ");
            $stmt_update->execute([
                ':id_client' => $id_client,
                ':montant' => $montant,
                ':motif' => $motif,
                ':date_paiement' => $date_paiement,
                ':mode_paiement' => $mode_paiement,
                ':id_paiement' => $paiement->id_paiement // Utilise l'ID original du paiement
            ]);

            $success_message = "Paiement modifié avec succès !";
            // Recharger le paiement pour afficher les nouvelles données après modification
            $stmt_paiement->execute([':id' => $id_paiement]);
            $paiement = $stmt_paiement->fetch(PDO::FETCH_OBJ);

            // Redirection après succès si désiré
            // header("Location: " . BASE_URL . "paiements/liste.php?success=modified");
            // exit();

        } catch (PDOException $e) {
            $errors['db_update'] = "Erreur lors de la modification du paiement : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Styles copiés de enregistrer.php */
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
    <h3 class="mb-4">Modifier un paiement (ID: <?= htmlspecialchars($paiement->id_paiement ?? '') ?>)</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3">

        <div class="col-md-4 position-relative">
            <div class="form-floating">
                <select name="id_client" id="id_client_select" class="form-select <?= isset($errors['id_client']) ? 'is-invalid' : '' ?>">
                    <option value="">-- Sélectionnez un NIF --</option>
                    <?php foreach ($clients as $client): ?>
                        <option 
                            value="<?= htmlspecialchars($client->id_client) ?>"
                            data-nom="<?= htmlspecialchars($client->nom . ' ' . $client->prenom) ?>"
                            <?= ($paiement && $paiement->id_client == $client->id_client) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client->nif) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="id_client_select">NIF du Client</label>
                <?php if (isset($errors['id_client'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['id_client']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-8 position-relative">
            <div class="form-floating">
                <input 
                    type="text" 
                    id="nom_client_display" 
                    class="form-control" 
                    placeholder="Nom du client" 
                    disabled 
                    value="<?= htmlspecialchars($paiement->nom . ' ' . $paiement->prenom ?? '') ?>"
                >
                <label for="nom_client_display">Nom du Client</label>
            </div>
        </div>
        
        <?php
        // Fonction utilitaire pour générer les champs flottants
        function floatingInputModifier($label, $name, $placeholder, $value, $errors, $type = 'text', $col_class = 'col-md-6') {
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

        // Utiliser les valeurs du POST si disponibles, sinon celles du paiement existant
        floatingInputModifier("Montant (Ar)", "montant", "Ex: 150000", $_POST['montant'] ?? ($paiement->montant ?? ''), $errors, 'number');
        floatingInputModifier("Motif du paiement", "motif", "Ex: Acompte", $_POST['motif'] ?? ($paiement->motif ?? ''), $errors);
        // Pour la date, assurez-vous qu'elle est au format 'YYYY-MM-DD' pour input type="date"
        floatingInputModifier("Date du paiement", "date_paiement", "Ex: 2025-07-21", $_POST['date_paiement'] ?? (date('Y-m-d', strtotime($paiement->date_paiement ?? ''))), $errors, 'date');
        ?>

        <div class='col-md-6 position-relative'>
            <div class='form-floating'>
                <select name='mode_paiement' id='mode_paiement' class='form-select <?php echo isset($errors['mode_paiement']) ? "is-invalid" : ""; ?>'>
                    <option value="">-- Sélectionnez un mode --</option>
                    <?php
                    $modes_paiement = ['Especes', 'Virement', 'Cheque', 'Carte'];
                    foreach ($modes_paiement as $mode) {
                        $selected = (($_POST['mode_paiement'] ?? ($paiement->mode_paiement ?? '')) == $mode) ? 'selected' : '';
                        echo "<option value=\"". htmlspecialchars($mode) . "\" " . $selected . ">" . htmlspecialchars($mode) . "</option>";
                    }
                    ?>
                </select>
                <label for='mode_paiement'>Mode de paiement</label>
                <?php if (isset($errors['mode_paiement'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['mode_paiement']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="<?php echo BASE_URL; ?>paiements/liste.php" class="btn btn-secondary ms-2">Annuler</a>
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

    // Mettre à jour à l'initialisation pour le client pré-sélectionné
    updateNomDisplay();

    nifSelect.addEventListener('change', updateNomDisplay);
});
</script>

<?php include_once '../includes/footer.php'; ?>
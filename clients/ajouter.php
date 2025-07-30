<?php
require '../includes/auth.php';
require '../includes/config.php';

$errors = [];
$values = ['nif' => '', 'nom' => '', 'prenom' => '', 'profession' => '', 'telephone' => '', 'cin' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $val) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    // VALIDATIONS
    if (!preg_match("/^[0-9]{10}$/", $values['nif'])) {
        $errors['nif'] = "Le NIF doit contenir exactement 10 chiffres.";
    }

    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-]+$/", $values['nom'])) {
        $errors['nom'] = "Le nom ne doit contenir que des lettres.";
    }

    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-]+$/", $values['prenom'])) {
        $errors['prenom'] = "Le prénom ne doit contenir que des lettres.";
    }

    if (!preg_match("/^[a-zA-ZÀ-ÿ\s\-]+$/", $values['profession'])) {
        $errors['profession'] = "La profession doit contenir uniquement des lettres.";
    }

    if (!preg_match("/^(032|033|034|039|038|030|020)[0-9]{7}$/", $values['telephone'])) {
        $errors['telephone'] = "Téléphone invalide. Ex: 0321234567";
    }

    if (!preg_match("/^[0-9]{12}$/", $values['cin'])) {
        $errors['cin'] = "Le CIN doit contenir exactement 12 chiffres.";
    }

    // INSERTION
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO clients (nif, nom, prenom, profession, telephone, cin)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $values['nif'], $values['nom'], $values['prenom'],
            $values['profession'], $values['telephone'], $values['cin']
        ]);
        header("Location: liste.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-floating .form-error {
            font-size: 0.85em;
            color: red;
            margin-top: 4px;
            position: absolute;
            bottom: -1.4em;
            left: 0.75em;
        }
        .form-floating {
            position: relative;
            margin-bottom: 2.5rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Ajouter un nouveau client</h3>
    <form method="post" class="row g-3">

        <?php
        function floatingInput($label, $name, $placeholder, $value, $errors) {
            $hasError = isset($errors[$name]) ? 'is-invalid' : '';
            echo "
                <div class='col-md-6 position-relative'>
                    <div class='form-floating'>
                        <input type='text' name='$name' id='$name' value='" . htmlspecialchars($value) . "' placeholder='$placeholder' class='form-control $hasError'>
                        <label for='$name'>$label</label>
                        " . (isset($errors[$name]) ? "<div class='form-error'>{$errors[$name]}</div>" : "") . "
                    </div>
                </div>
            ";
        }

        floatingInput("NIF", "nif", "Ex: 1234567890", $values['nif'], $errors);
        floatingInput("Nom", "nom", "Ex: Rakoto", $values['nom'], $errors);
        floatingInput("Prénom", "prenom", "Ex: Jean", $values['prenom'], $errors);
        floatingInput("Profession", "profession", "Ex: Médecin", $values['profession'], $errors);
        floatingInput("Téléphone", "telephone", "Ex: 0321234567", $values['telephone'], $errors);
        floatingInput("CIN", "cin", "Ex: 123456789012", $values['cin'], $errors);
        ?>

        <div class="col-12">
            <button type="submit" class="btn btn-success">Ajouter le client</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

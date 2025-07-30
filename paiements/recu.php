<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

$id_paiement = $_GET['id'] ?? null;

if (!$id_paiement) {
    http_response_code(400);
    exit("❌ Identifiant de paiement manquant.");
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.nom, c.prenom, c.nif 
        FROM paiements p
        JOIN clients c ON p.id_client = c.id_client
        WHERE p.id_paiement = :id
    ");
    $stmt->execute([':id' => $id_paiement]);
    $paiement = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$paiement) {
        http_response_code(404);
        exit("❌ Paiement introuvable.");
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit("❌ Erreur serveur : " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reçu de Paiement #<?= htmlspecialchars($paiement->id_paiement) ?></title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />

<style>
    /* Fond et reset de base */
    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        background: #fefefe;
        /* Fond style liste paiements : fond blanc + ombre + bord arrondi */
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 40px 20px;
        background: #f9f9f9; /* clair neutre */
    }

    .recu-container {
        background: #fff;
        max-width: 700px;
        width: 100%;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgb(0 0 0 / 0.1);
        padding: 30px 40px;
        color: #333;
        position: relative;
    }

    /* Logo centré */
    .logo {
        text-align: center;
        margin-bottom: 2rem;
    }
    .logo img {
        max-height: 80px;
        filter: drop-shadow(0 0 2px rgba(0,0,0,0.1));
    }

    /* Titres */
    h1 {
        font-weight: 700;
        font-size: 2rem;
        color: #0066cc;
        text-align: center;
        margin-bottom: 2rem;
        letter-spacing: 0.04em;
    }

    /* Bouton imprimer */
    .btn-print {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: #0066cc;
        color: white;
        padding: 10px 18px;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 10px rgb(0 102 204 / 0.5);
        transition: background-color 0.25s ease;
    }
    .btn-print:hover {
        background-color: #004c99;
    }
    .btn-print svg {
        width: 18px;
        height: 18px;
        fill: white;
    }

    /* Sections infos */
    .section {
        margin-bottom: 2.5rem;
    }
    .section-title {
        font-weight: 600;
        color: #004c99;
        font-size: 1.1rem;
        border-bottom: 2px solid #cce4ff;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        font-size: 1rem;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #666;
        flex-basis: 40%;
        user-select: none;
    }
    .info-value {
        flex-basis: 60%;
        text-align: right;
        color: #111;
        font-variant-numeric: tabular-nums;
        user-select: text;
    }

    /* Footer */
    .footer {
        font-size: 0.85rem;
        color: #999;
        text-align: center;
        border-top: 1px solid #eee;
        padding-top: 1.5rem;
        user-select: none;
    }

    /* Responsive */
    @media print {
        body {
            margin: 0;
            background: white;
        }
        .btn-print {
            display: none;
        }
        .recu-container {
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            max-width: 100%;
            margin: 0;
        }
    }
    @media (max-width: 480px) {
        .recu-container {
            padding: 20px;
        }
        .info-row {
            flex-direction: column;
            text-align: left;
        }
        .info-label, .info-value {
            flex-basis: 100%;
            text-align: left;
            margin-bottom: 6px;
        }
    }
</style>
</head>
<body>

<div class="recu-container" role="main" aria-label="Reçu de paiement">

    <button class="btn-print" onclick="window.print()" aria-label="Imprimer le reçu">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7M6 14h12v7H6v-7zM18 7V3H6v4h12zM8 16h8v2H8v-2z"/></svg>
        Imprimer
    </button>

    <div class="logo" role="img" aria-label="Logo de l'entreprise">
        <!-- Change ici le chemin vers ton logo -->
        <img src="../images/img.webp" alt="Logo de fisca" />


    </div>

    <h1>Reçu de Paiement</h1>

    <section class="section" aria-labelledby="details-paiement">
        <h2 id="details-paiement" class="section-title">Détails du paiement</h2>
        <div class="info-row">
            <div class="info-label">Numéro du reçu</div>
            <div class="info-value"><?= htmlspecialchars($paiement->id_paiement) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Date du paiement</div>
            <div class="info-value"><?= date('d/m/Y', strtotime($paiement->date_paiement)) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Montant payé</div>
            <div class="info-value"><?= number_format($paiement->montant, 0, ',', ' ') ?> Ar</div>
        </div>
        <div class="info-row">
            <div class="info-label">Mode de paiement</div>
            <div class="info-value"><?= htmlspecialchars($paiement->mode_paiement) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Motif</div>
            <div class="info-value"><?= htmlspecialchars($paiement->motif) ?></div>
        </div>
    </section>

    <section class="section" aria-labelledby="details-client">
        <h2 id="details-client" class="section-title">Informations client</h2>
        <div class="info-row">
            <div class="info-label">Nom complet</div>
            <div class="info-value"><?= htmlspecialchars($paiement->nom . ' ' . $paiement->prenom) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">NIF</div>
            <div class="info-value"><?= htmlspecialchars($paiement->nif) ?></div>
        </div>
    </section>

    <footer class="footer" role="contentinfo">
        &copy; <?= date('Y') ?> Votre entreprise - Tous droits réservés
    </footer>
</div>

</body>
</html>

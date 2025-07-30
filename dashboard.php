<?php 
require_once 'includes/config.php';
require_once 'includes/auth.php';

require_login();

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

try {
    $stmt_clients = $pdo->query("SELECT COUNT(*) AS total_clients FROM clients");
    $total_clients = (int)$stmt_clients->fetch()->total_clients;

    $stmt_paiements = $pdo->query("SELECT SUM(montant) AS total_paiements, COUNT(*) AS total_paiements_count FROM paiements");
    $paiements_data = $stmt_paiements->fetch();
    $total_paiements = (float)$paiements_data->total_paiements;
    $total_paiements_count = (int)$paiements_data->total_paiements_count;

    $stmt_chart = $pdo->prepare("
        SELECT DATE_FORMAT(date_paiement, '%Y-%m') AS mois, 
               COUNT(*) AS nb_paiements, 
               SUM(montant) AS total_montant 
        FROM paiements
        WHERE date_paiement >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mois
        ORDER BY mois ASC
    ");
    $stmt_chart->execute();
    $paiements_par_mois = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total_clients = 'N/A';
    $total_paiements = 'N/A';
    $total_paiements_count = 'N/A';
    $paiements_par_mois = [];
    error_log("Erreur base de données : " . $e->getMessage());
}

$page_title = "Tableau de bord";
include_once 'includes/header.php';
?>

<!-- Header sticky moderne -->
<header class="topbar">
  <div class="container-topbar">
    <div class="user-info" aria-label="Nom utilisateur connecté">
      <span>👋 Bonjour, <strong><?= e($_SESSION['username']); ?></strong></span>
    </div>
    <a href="<?= BASE_URL; ?>logout.php" class="btn btn-danger logout-btn" role="button" aria-label="Déconnexion">
      🔒 Déconnexion
    </a>
  </div>
</header>

<div class="dashboard-container" role="main">
    <p class="dashboard-subtitle" id="dashboard-desc">Voici un aperçu rapide de vos données</p>

    <div class="dashboard-grid" aria-describedby="dashboard-desc">
        <section class="dashboard-card" tabindex="0" aria-label="Nombre total de clients enregistrés">
            <h3>Clients enregistrés</h3>
            <p class="stat-number"><?= e($total_clients); ?></p>
            <a href="<?= BASE_URL; ?>clients/liste.php" class="btn btn-primary" role="button">Voir les clients</a>
        </section>

        <section class="dashboard-card" tabindex="0" aria-label="Total des paiements">
            <h3>Total des paiements</h3>
            <p class="stat-number"><?= is_numeric($total_paiements) ? number_format($total_paiements, 2, ',', ' ') . ' Ar' : e($total_paiements); ?></p>
            <a href="<?= BASE_URL; ?>paiements/liste.php" class="btn btn-primary" role="button">Voir les paiements</a>
        </section>

        <section class="dashboard-card" tabindex="0" aria-label="Nombre total de paiements effectués">
            <h3>Nombre de paiements</h3>
            <p class="stat-number"><?= e($total_paiements_count); ?></p>
            <a href="<?= BASE_URL; ?>paiements/liste.php" class="btn btn-primary" role="button">Détails des paiements</a>
        </section>
    </div>

    <section class="dashboard-chart-container" aria-label="Graphique des paiements par mois">
        <h3>Évolution des paiements sur les 6 derniers mois</h3>
        <canvas id="paiementsChart" aria-describedby="paiementsChartDesc"></canvas>
        <p id="paiementsChartDesc" class="sr-only">
            Graphique représentant le nombre de paiements et le montant total des paiements pour chaque mois des 6 derniers mois.
        </p>
    </section>
</div>

<footer class="dashboard-footer">
    <p>© 2025 Gestion des Paiements. Tous droits réservés.</p>
</footer>

<style>
/* Reset simple */
* {
  box-sizing: border-box;
}

/* Barre sticky en haut */
.topbar {
  position: sticky;
  top: 0;
  width: 100%;
  background: rgba(0,0,0,0.75);
  backdrop-filter: saturate(180%) blur(10px);
  box-shadow: 0 2px 10px rgba(0,0,0,0.5);
  z-index: 999;
  padding: 12px 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #fff;
}

/* Conteneur flex pour espace entre */
.container-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1140px;
  margin: 0 auto;
}

/* Texte utilisateur */
.user-info strong {
  color: #00e5ff;
  font-weight: 700;
}

/* Bouton déconnexion stylé */
.btn.logout-btn {
  background-color: #dc3545;
  border: 2px solid #dc3545;
  color: #fff;
  padding: 8px 20px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: background-color 0.3s ease, border-color 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 1rem;
  cursor: pointer;
}
.btn.logout-btn:hover,
.btn.logout-btn:focus {
  background-color: #a71d2a;
  border-color: #a71d2a;
  outline: none;
  text-decoration: none;
}

/* Le reste de la page */
.dashboard-container {
    padding: 40px 20px 60px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('assets/bg-bois.jpg') center/cover no-repeat fixed;
    backdrop-filter: blur(5px);
    color: #fff;
    min-height: calc(100vh - 120px);
    max-width: 1140px;
    margin: 0 auto;
}
.dashboard-subtitle {
    font-size: 1.2rem;
    color: #ccc;
    margin-bottom: 40px;
    text-align: center;
}
.dashboard-grid {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-bottom: 50px;
}
.dashboard-card {
    background-color: rgba(0, 0, 0, 0.65);
    border-radius: 12px;
    padding: 30px;
    flex: 1 1 280px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    text-align: center;
    transition: transform 0.2s ease-in-out;
    outline-offset: 4px;
}
.dashboard-card:focus,
.dashboard-card:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px #00e5ff;
}
.stat-number {
    font-size: 3rem;
    font-weight: 700;
    margin: 20px 0;
    color: #00e5ff;
}

/* Tous les boutons bleus */
.btn.btn-primary {
    background-color: #007bff;
    color: #fff;
    border: 2px solid #007bff;
    padding: 10px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s, color 0.3s;
    display: inline-block;
    text-align: center;
    cursor: pointer;
}
.btn.btn-primary:hover,
.btn.btn-primary:focus {
    background-color: #0056b3;
    border-color: #0056b3;
    color: #fff;
    outline: none;
}

.dashboard-footer {
    text-align: center;
    padding: 20px;
    color: #eee;
    background-color: rgba(0, 0, 0, 0.7);
    margin-top: 40px;
    font-size: 0.9rem;
}

.dashboard-chart-container {
    background-color: rgba(0,0,0,0.6);
    border-radius: 12px;
    padding: 20px;
    max-width: 900px;
    margin: 0 auto 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

@media (max-width: 768px) {
    .dashboard-grid {
        flex-direction: column;
    }
    .dashboard-card {
        flex: none;
        width: 100%;
    }
}

/* Accessibilité */
.sr-only {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    border: 0 !important;
}
</style>

<!-- Chart.js depuis CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('paiementsChart').getContext('2d');

    const data = {
        labels: <?= json_encode(array_map(fn($m) => $m['mois'], $paiements_par_mois)); ?>,
        datasets: [
            {
                label: 'Nombre de paiements',
                data: <?= json_encode(array_map(fn($m) => (int)$m['nb_paiements'], $paiements_par_mois)); ?>,
                backgroundColor: 'rgba(0, 229, 255, 0.6)',
                borderColor: 'rgba(0, 229, 255, 1)',
                borderWidth: 1,
                yAxisID: 'y',
            },
            {
                label: 'Montant total (Ar)',
                data: <?= json_encode(array_map(fn($m) => (float)$m['total_montant'], $paiements_par_mois)); ?>,
                backgroundColor: 'rgba(0, 128, 128, 0.6)',
                borderColor: 'rgba(0, 128, 128, 1)',
                borderWidth: 1,
                yAxisID: 'y1',
                type: 'line',
                tension: 0.3,
            }
        ]
    };

    const options = {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        stacked: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Nombre de paiements',
                },
                beginAtZero: true,
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Montant total (Ar)',
                },
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false,
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#fff'
                }
            },
            tooltip: {
                enabled: true,
                mode: 'nearest',
                intersect: false,
            }
        }
    };

    new Chart(ctx, {
        data: data,
        options: options
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>

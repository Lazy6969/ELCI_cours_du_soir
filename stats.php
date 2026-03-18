<?php
require 'config.php';
checkAuth();

// Récupérer les stats par langue
$stmt = $pdo->query("
    SELECT 
        language,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Admis' THEN 1 ELSE 0 END) as admis,
        SUM(CASE WHEN status = 'Échec' THEN 1 ELSE 0 END) as echec
    FROM grades
    WHERE is_active = 1
    GROUP BY language
    ORDER BY language
");
$langStats = $stmt->fetchAll();

// Récupérer les stats par niveau
$stmt2 = $pdo->query("
    SELECT 
        CONCAT(language, ' - ', level) as label,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Admis' THEN 1 ELSE 0 END) as admis,
        SUM(CASE WHEN status = 'Échec' THEN 1 ELSE 0 END) as echec
    FROM grades
    WHERE is_active = 1
    GROUP BY language, level
    ORDER BY language, level
");
$levelStats = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Statistiques - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="assets/js/chart.umd.js"></script>
</head>
<body class="dark-theme">

<?php include 'header_nav.php'; ?><br><br>

    <div class="main-content">
    <h2>📊 Statistiques des Résultats</h2>
   
        <div class="stat-card">
            <i class="fas fa-chart-bar"></i>
            <h3>
        <?php
           // $avg = $pdo->query("SELECT AVG(average) FROM grades WHERE is_active = 1")->fetchColumn();
            $avg = $pdo->query("SELECT AVG(average) FROM grades WHERE status != 'En cours'")->fetchColumn();
            echo $avg === null ? '0.0' : round($avg, 1);
        ?>%
        </h3>
            <p>Moyenne globale</p>
        </div>
    </div>

    <!-- Graphique par langue -->
    <div class="card">
        
        <center>
            <h3>Par Langue</h3>
        <canvas id="chartLangue" width="600" height="400"></canvas>
        
        

        <h3>Par Niveau</h3>
        <canvas id="chartNiveau" width="900" height="400"></canvas>
        </center>
        
    </div>

</div>
<?php include 'footer.php'; ?>
<script>
// === Données par langue ===
const langLabels = <?= json_encode(array_column($langStats, 'language')) ?>;
const langAdmis = <?= json_encode(array_column($langStats, 'admis')) ?>;
const langEchec = <?= json_encode(array_column($langStats, 'echec')) ?>;

// Couleurs
const ctx1 = document.getElementById('chartLangue').getContext('2d');
new Chart(ctx1, {
    type: 'pie',
    data: {
        labels: ['Admis', 'Échec'],
        datasets: [{
            data: [
                langAdmis.reduce((a,b) => a + b, 0),
                langEchec.reduce((a,b) => a + b, 0)
            ],
            backgroundColor: [
                'rgba(76, 175, 80, 0.8)',
                'rgba(244, 67, 54, 0.8)'
            ],
            borderColor: [
                'rgba(76, 175, 80, 1)',
                'rgba(244, 67, 54, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: {
            title: { display: true, text: 'Taux global de réussite' }
        }
    }
});

// === Données par niveau ===
const levelLabels = <?= json_encode(array_column($levelStats, 'label')) ?>;
const levelAdmis = <?= json_encode(array_column($levelStats, 'admis')) ?>;
const levelEchec = <?= json_encode(array_column($levelStats, 'echec')) ?>;

const ctx2 = document.getElementById('chartNiveau').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: levelLabels,
        datasets: [
            {
                label: 'Admis',
                data: levelAdmis,
                backgroundColor: 'rgba(76, 175, 80, 0.7)'
            },
            {
                label: 'Échec',
                data: levelEchec,
                backgroundColor: 'rgba(244, 67, 54, 0.7)'
            }
        ]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            title: { display: true, text: 'Résultats par niveau' }
        },
        scales: {
            y: { beginAtZero: true, ticks: { color: 'var(--text)' } },
            x: { 
                ticks: { color: 'var(--text)', autoSkip: false, maxRotation: 45, minRotation: 45 }
            }
        }
    }
});
</script>
<script src="assets/js/main.js"></script>
</body>
</html>
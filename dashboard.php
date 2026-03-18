<?php
require 'config.php';
checkAuth();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<body class="dark-theme">

<?php include 'header_nav.php'; ?>
<div class="dashboard-overlay"></div>
<div class="dashboard-container">
    <!-- Fond de drapeaux -->
<div class="flags-bg">
    <div class="flag flag-uk" style="background-image: url('photos/ang.png');"></div>
    <div class="flag flag-fr" style="background-image: url('photos/fr.png');"></div>
    <div class="flag flag-de" style="background-image: url('photos/de.png');"></div>
    <div class="flag flag-cn" style="background-image: url('photos/cn.png');"></div>
</div>

<br><br><br>
 <!-- Contenu principal -->
    <div class="welcome-card">
        <h2>👋 Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> !</h2>
        <p>Gérez les étudiants, les cours et les résultats ici.</p>
    </div>


    <!-- Statistiques rapides -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-users fa-2x"></i>
            <h3><?= $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?></h3>
            <p>Étudiants</p>
        </div>
        <div class="stat-card">
    <i class="fas fa-book"></i>
    <h3><?= $pdo->query("SELECT COUNT(*) FROM grades WHERE is_active = 1")->fetchColumn() ?></h3>
    <p>Notes </p>
</div>
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

    <!-- Liens rapides -->
    <div class="quick-links">
        <a href="students.php" class="quick-link">
            <i class="fas fa-user-plus"></i>
            <span>Gérer les étudiants</span>
        </a>
        <a href="courses.php" class="quick-link">
            <span>➕ </span>
            <span> Ajouter des notes</span>
        </a>
        <a href="students_by_level.php" class="quick-link">
            <span>👥 </span>
            <span> Étudiants par Niveau</span>
        </a>
        <a href="stats.php" class="quick-link">
            <i class="fas fa-chart-pie"></i>
            <span>Voir les statistiques</span>
        </a>
        <a href="notes.php" class="quick-link">
            <i class="fas fa-clipboard-list"></i>
            <span>Bloc-notes</span>
        </a>
        <a href="message.php" class="quick-link" <?= ($current_page === 'message.php') ? 'active' : '' ?>">
            <span>📤 </span>
            <span>Messages</span>
        </a>
    </div>
</div>
<!-- Footer -->
<footer class="footer">
    © 2025 ELCI - Centre de Langues | Tous droits réservés
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
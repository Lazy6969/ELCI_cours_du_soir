<?php
// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit();
}
?>

<?php
// Détermine la page active
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="navbar">
    <div class="nav-brand">

            <h1 class="site-title"><img class="logo_elci"src="photos/logo.png" alt="logo elci">         <span style="color:red;">E</span><span style="color:blue;">L</span><span style="color:red;">C</span><span style="color:blue;">I</span>- Cours du Soir<span>🎓</span> </h1>

    </div>

    <div class="nav-menu">
        <a href="dashboard.php" class="nav-btn <?= ($current_page === 'dashboard.php') ? 'active' : '' ?>">🏠 Accueil</a>
        <a href="students.php" class="nav-btn <?= ($current_page === 'students.php') ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> Étudiants</a>
        <a href="courses.php" class="nav-btn <?= ($current_page === 'courses.php') ? 'active' : '' ?>">📚 Cours & Notes</a>
        <a href="students_by_level.php" class="nav-btn <?= ($current_page === 'students_by_level.php') ? 'active' : '' ?>"">👥 Étudiants par Niveau</a>
        <a href="stats.php" class="nav-btn <?= ($current_page === 'stats.php') ? 'active' : '' ?>">📊 Statistiques</a>
        <a href="#" class="nav-btn nav-logout" onclick="confirmLogout()">🚪 Déconnexion</a>      
        <div id="theme-toggle" class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </div>


</header>

<!-- Modal de déconnexion -->
<div id="logout-modal" class="modal">
    <div class="modal-content">
        <h3>❓ Déconnexion</h3>
        <p>Voulez-vous vraiment vous déconnecter ?</p>
        <div class="modal-buttons">
            <button class="btn btn-cancel" onclick="closeModal()">Annuler</button>
            <a href="logout.php" class="btn btn-danger">Déconnecter</a>
        </div>
    </div>
</div>







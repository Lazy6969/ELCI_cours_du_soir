<?php
error_reporting(0); // Désactive les warnings (à utiliser seulement en prod)
// Ou pour développement :
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<?php
require 'config.php';
checkAuth();

// Ajout
if ($_POST && isset($_POST['add_student'])) {
    $stmt = $pdo->prepare("INSERT INTO students (fullname, birth_date, birth_place, student_phone, parent_phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fullname'],
        $_POST['birth_date'],
        $_POST['birth_place'],
        $_POST['student_phone'],
        $_POST['parent_phone']
    ]);
    // === Sauvegarde dans fichier TXT ===
    $log = "ID: $lastId | Nom: {$_POST['fullname']} | Naissance: {$_POST['birth_date']} à {$_POST['birth_place']} | Étud: {$_POST['student_phone']} | Parent: {$_POST['parent_phone']} | Date: " . date('Y-m-d H:i:s') . "\n";
    file_put_contents('data/etudiants.txt', $log, FILE_APPEND | LOCK_EX);
    header('Location: students.php?msg=added');
    exit();
}

// Suppression
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: students.php?msg=deleted');
    exit();
}

// Recherche
$search = $_GET['search'] ?? '';
$condition = $search ? "WHERE fullname LIKE ? OR birth_place LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%"] : [];
$stmt = $pdo->prepare("SELECT * FROM students $condition ORDER BY id DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Étudiants - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="modal">
    <div class="modal-content delete-modal">
        <h3>⚠️ Confirmation de suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer cet étudiant ?<br><strong>Cette action est irréversible.</strong></p>
        <div class="modal-buttons">
            <button class="btn btn-cancel" onclick="closeDeleteModal()">Annuler</button>
            <a id="delete-confirm-link" class="btn btn-danger">Supprimer</a>
        </div>
    </div>
</div>
<body class="dark-theme">
   <?php include 'header_nav.php'; ?>

    <div class="main-content"><br>
        <h2>👥 Gestion des Étudiants</h2>

        <!-- Formulaire d'ajout -->
        <form method="POST" class="card form-student">
            <h3>➕ Ajouter un étudiant</h3>
            <input type="text" name="fullname" placeholder="Nom et prénoms" required>
            <input type="date" name="birth_date" required>
            <input type="text" name="birth_place" placeholder="Lieu de naissance" required>
            <input type="tel" name="student_phone" placeholder="Contact étudiant/parents s'il n'y a pas" required>
            <input type="tel" name="parent_phone" placeholder="Contact parent" required>
            <button type="submit" name="add_student" class="btn btn-success">Ajouter</button>
        </form>
<div class="stat-card">
            <i class="fas fa-users fa-2x"></i>
            <h3><?= $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?></h3>
            <p>Étudiants</p>
        </div>
        <!-- Barre de recherche -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Rechercher un étudiant..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="filterTable()">🔍 Rechercher</button>
        </div>

        <!-- Liste -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Date Naissance</th>
                    <th>Lieu de Naissance</th>
                    <th>Contact Étudiant</th>
                    <th>Contact Parent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['fullname']) ?></td>
                    <td><?= htmlspecialchars($s['birth_date']) ?></td>
                    <td><?= htmlspecialchars($s['birth_place']) ?></td>
                    <td><?= htmlspecialchars($s['student_phone']) ?></td>
                    <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                    <td>
                        <center><a href="student_detail.php?id=<?= $s['id'] ?>" class="btn btn-view">👁️ Voir</a></center>
                        
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn btn-print" onclick="printFilteredStudents()">🖨️ Imprimer</button>
    </div>

    <script src="assets/js/main.js"></script>
    <?php include 'footer.php'; ?>
    <!-- Formulaire caché pour impression -->
<form id="printStudentForm" method="POST" action="print.php" target="_blank">
    <input type="hidden" name="filtered_students" id="filteredStudentsInput">
    <input type="hidden" name="print_type" value="students_filtered">
</form>
</body>
</html>
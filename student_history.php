<?php
require 'config.php';
checkAuth();

// Récupérer l'étudiant
$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    die("ID étudiant manquant.");
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Étudiant non trouvé.");
}

// Récupérer TOUTES les notes (actives + archivées)
$stmt = $pdo->prepare("
    SELECT * FROM grades 
    WHERE student_id = ? 
    ORDER BY exam_date DESC, id DESC
");
$stmt->execute([$student_id]);
$allGrades = $stmt->fetchAll();

// Mapping des drapeaux
$flagMap = [
    'Anglais' => 'gb.png',
    'Français' => 'fr.png',
    'Allemand' => 'de.png',
    'Mandarin' => 'cn.png'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique - <?= htmlspecialchars($student['fullname']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
     <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<body class="dark-theme">
    <?php include 'header_nav.php'; ?>
    <div class="main-content">
        <div class="card">
            <h2>🎓 Historique complet de <?= htmlspecialchars($student['fullname']) ?></h2>
            
            <!-- Informations personnelles -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
                <div><strong>Date de naissance :</strong> <?= htmlspecialchars($student['birth_date']) ?></div>
                <div><strong>Lieu de naissance :</strong> <?= htmlspecialchars($student['birth_place']) ?></div>
                <div><strong>Contact étudiant :</strong> <?= htmlspecialchars($student['student_phone']) ?></div>
                <div><strong>Contact parent :</strong> <?= htmlspecialchars($student['parent_phone']) ?></div>
            </div>

            <!-- Historique des notes -->
            <?php if ($allGrades): ?>
                <h3>📚 Historique des inscriptions</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Drapeau</th>
                            <th>Langue</th>
                            <th>Niveau</th>
                            <th>T1</th>
                            <th>T2</th>
                            <th>Moyenne</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>État</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allGrades as $g): ?>
                        <tr>
                            <td class="flag-cell">
                                <?php if (!empty($g['language']) && isset($flagMap[$g['language']])): ?>
                                    <img src="assets/img/flags/<?= $flagMap[$g['language']] ?>" alt="<?= htmlspecialchars($g['language']) ?>" class="flag-img">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($g['language']) ?></td>
                            <td><?= htmlspecialchars($g['level']) ?></td>
                            <td><?= number_format($g['t1'], 3) ?></td>
                            <td><?= number_format($g['t2'], 3) ?></td>
                            <td class="<?= $g['average'] >= 50 ? 'pass' : ($g['average'] == 0 ? 'en-cours' : 'fail') ?>">
                                <?= number_format($g['average'], 3) ?>
                            </td>
                            <td><?= htmlspecialchars($g['status']) ?></td>
                            <td><?= htmlspecialchars($g['exam_date']) ?></td>
                            <td>
                                <?php if ($g['is_active']): ?>
                                    <span style="color: #4CAF50; font-weight: bold;">Actif</span>
                                <?php else: ?>
                                    <span style="color: #ff9800; font-weight: bold;">Archivé</span>
                                <?php endif; ?>
                            </td>
                            <td class="locked-cell">
                                <?php if (!$g['is_active']): ?>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="confirmDeleteGrade(<?= $g['id'] ?>)">
                                        🗑️ Supprimer
                                    </button>
                                <?php else: ?>
                                    <span style="color: #666;">🔒 Actif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 20px; text-align: center;">
    <a href="export_student_history.php?student_id=<?= $student_id ?>" 
       class="btn btn-print" target="_blank">
        📥 Exporter l'historique en TXT
    </a>
</div>

                <!-- Bouton de suppression -->
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn btn-danger" onclick="confirmDeleteHistory(<?= $student_id ?>)">
                        🗑️ Supprimer tout l'historique de cet étudiant
                    </button>
                </div>
            <?php else: ?>
                <p>Aucun historique trouvé pour cet étudiant.</p>
            <?php endif; ?>

            <div style="margin-top: 20px; text-align: center;">
                <a href="student_detail.php?id=<?= $student_id ?>" class="btn btn-primary">🔙 Retour à la fiche</a>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div id="delete-modal" class="modal">
        <div class="modal-content delete-modal">
            <h3>⚠️ Confirmation de suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer <strong>tout l'historique</strong> de cet étudiant ?<br>
            <strong>Cette action est irréversible.</strong></p>
            <div class="modal-buttons">
                <button class="btn btn-cancel" onclick="closeDeleteModal()">Annuler</button>
                <button class="btn btn-danger" onclick="deleteHistory(<?= $student_id ?>)">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteHistory(studentId) {
            document.getElementById('delete-modal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').style.display = 'none';
        }

        function deleteHistory(studentId) {
            fetch(`delete_student_history.php?student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Historique supprimé avec succès !");
                        window.location.href = 'students.php';
                    } else {
                        alert("Erreur : " + data.message);
                    }
                })
                .catch(() => {
                    alert("Une erreur est survenue.");
                });
            closeDeleteModal();
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
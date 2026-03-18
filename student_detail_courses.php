<?php
require 'config.php';
checkAuth();
// === Mise à jour d'une note ===
if ($_POST && isset($_POST['update_grade'])) {
    $grade_id = (int)$_POST['grade_id'];
    $t1 = (float)$_POST['t1'];
    $t2 = (float)$_POST['t2'];
    $avg = ($t1 + $t2) / 2;
    // Déterminer le statut
    if ($t1 == 0 && $t2 == 0) {
        $status = 'En cours';
    } elseif ($t1 != 0 && $t2 == 0) {
        $status = 'En cours';
    } else {
        // T1 et T2 ≠ 0 → calculer le statut final
        $status = $avg >= 50 ? 'Admis' : 'Échec';
    }

    // Mettre à jour en BDD
    $stmt = $pdo->prepare("UPDATE grades SET t1 = ?, t2 = ?, average = ?, status = ? WHERE id = ?");
    $stmt->execute([$t1, $t2, $avg, $status, $grade_id]);

    // Mettre à jour le fichier .txt
    // Récupérer les infos pour le log
    $info = $pdo->prepare("SELECT g.*, s.fullname FROM grades g JOIN students s ON g.student_id = s.id WHERE g.id = ?");
    $info->execute([$grade_id]);
    $row = $info->fetch();

    if ($row) {
        $langue = strtolower($row['language']);
        $langue = str_replace(['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ô', 'ö', 'û', 'ü', 'ç'],
                              ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'o', 'o', 'u', 'u', 'c'], $langue);
        $langue = preg_replace('/[^a-z0-9]/', '', $langue);

        $log = "MODIFICATION | Étudiant: {$row['fullname']} | Langue: {$row['language']} | Niveau: {$row['level']} | T1: $t1 | T2: $t2 | Moy: $avg | Statut: $status | Date: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents("data/$langue/notes.txt", $log, FILE_APPEND | LOCK_EX);
    }
saveStudentHistoryToFile($student_id, $pdo);
    // Rediriger pour éviter la resoumission
    header("Location: student_detail.php?id=" . $row['student_id'] . "&msg=updated");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID étudiant invalide.");
}

$id = (int)$_GET['id'];

// Récupérer les données de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    die("Étudiant non trouvé.");
}

// Récupérer les notes de cet étudiant
$gradesStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? AND is_active = 1 ORDER BY exam_date DESC");
$gradesStmt->execute([$id]);
$grades = $gradesStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails - <?= htmlspecialchars($student['fullname']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<body class="dark-theme">

<?php include 'header_nav.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>👁️ Détails de l’étudiant</h2>
        <p><strong>Nom complet :</strong> <?= htmlspecialchars($student['fullname']) ?></p>
        <p><strong>Date de naissance :</strong> <?= htmlspecialchars($student['birth_date']) ?></p>
        <p><strong>Lieu de naissance :</strong> <?= htmlspecialchars($student['birth_place']) ?></p>
        <p><strong>Contact étudiant :</strong> <?= htmlspecialchars($student['student_phone']) ?></p>
        <p><strong>Contact parent :</strong> <?= htmlspecialchars($student['parent_phone']) ?></p>
    </div>

    <!-- Notes -->
    <h3>📚 Cours suivis & Notes</h3>
    <?php if (count($grades) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Langue</th>
                    <th>Drapeau</th>
                    <th>Niveau</th>
                    <th>T1</th>
                    <th>T2</th>
                    <th>Moyenne</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Modification des notes</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($grades as $g): ?>
    <tr>
        <form method="POST" class="edit-grade-form">
            <input type="hidden" name="grade_id" value="<?= $g['id'] ?>">
            <td><?= htmlspecialchars($g['language']) ?></td>
            <?php
$flagMap = [
    'Anglais' => 'gb.png',
    'Français' => 'fr.png',
    'Allemand' => 'de.png',
    'Mandarin' => 'cn.png'
];
$flagFile = '';
if (!empty($g['language']) && isset($flagMap[$g['language']])) {
    $flagFile = $flagMap[$g['language']];
}
?>
<td class="flag-cell">
    <?php if ($flagFile): ?>
        <img src="assets/img/flags/<?= $flagFile ?>" alt="<?= htmlspecialchars($g['language']) ?>" class="flag-img">
    <?php endif; ?>
</td>
            <td><?= htmlspecialchars($g['level']) ?></td>
            <td>
                <input type="number" step="0.001" min="0" max="100" 
                       name="t1" value="<?= number_format($g['t1'], 3) ?>" 
                       class="grade-input">
            </td>
            <td>
                <input type="number" step="0.001" min="0" max="100" 
                       name="t2" value="<?= number_format($g['t2'], 3) ?>" 
                       class="grade-input">
            </td>
            <td class="<?= $g['average'] >= 50 ? 'pass' : 'fail' ?>">
                <?= number_format($g['average'], 3) ?>
            </td>
            <td><?= htmlspecialchars($g['status']) ?></td>
            <td><?= $g['exam_date'] ?></td>
            <td>
    <form method="POST" class="edit-grade-form">
        <!-- ... -->
        <button type="submit" name="update_grade" class="btn btn-warning btn-sm">✏️ Modifier les notes</button>
        <?php if ($g['status'] === 'Admis'): ?>
            <button type="button" class="btn btn-success btn-sm" 
        onclick="migrateThisGrade(this)" 
        data-id="<?= $g['id'] ?>" 
        data-language="<?= htmlspecialchars($g['language']) ?>" 
        data-level="<?= htmlspecialchars($g['level']) ?>">
    ➡️ Migrer
</button>
        <?php endif; ?>
        <!-- Bouton Supprimer -->
        <button type="button" class="btn btn-danger btn-sm" 
                onclick="confirmDeleteGrade(<?= $g['id'] ?>)">
            🗑️ Supprimer
        </button>
    </form>
</td>
        </form>
    </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    <?php else: ?>
        <p>Aucune note enregistrée pour cet étudiant.</p>
    <?php endif; ?>


    <!-- Boutons d'action - Alignés horizontalement -->
    <!-- Boutons d'action alignés -->
    <div class="action-buttons">
        <a href="courses.php" class="btn btn-primary btn-action">🔙 Retour à la liste</a>
        <a href="edit_student.php?id=<?= $id ?>" class="btn btn-success btn-action">✏️ Modifier</a> <!-- Fond vert ici -->
        <button class="btn btn-danger btn-action" onclick="showDeleteConfirm(<?= $id ?>)">🗑️ Supprimer</button>
        <a href="student_history.php?id=<?= $id ?>" class="btn btn-info">📜 Voir l'historique</a>
        <!-- Dans le tableau des notes, pour chaque ligne "Admis" -->                                              
    </div>
</div>

<script src="assets/js/main.js"></script>
<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="modal">
    <div class="modal-content delete-modal">
        <h3>⚠️ Confirmation de suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer définitivement cet étudiant ?<br><strong>Cette action est irréversible et supprimera aussi ses notes.</strong></p>
        <div class="modal-buttons">
            <button class="btn btn-cancel" onclick="closeDeleteModal()">Annuler</button>
            <button id="delete-confirm-link" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>
<!-- Modal de migration -->
<div id="migrate-modal" class="modal">
    <div class="modal-content">
        <h3>🔄 Choisir la direction de migration</h3>
        <p id="migrate-info"></p>
        <div class="modal-buttons">
            <button type="button" class="btn btn-primary btn-sm" 
        onclick="migrateStudent(<?= $g['id'] ?>, 'up')">
    🔼 Monter
</button>
<button type="button" class="btn btn-warning btn-sm" 
        onclick="migrateStudent(<?= $g['id'] ?>, 'down')">
    🔽 Descendre
</button>
            <button class="btn btn-cancel" onclick="closeMigrateModal()">Annuler</button>
        </div>
    </div>
</div>
<script>
function migrateStudent(gradeId, direction) {
    if (!gradeId) return;

    fetch(`migrate_level.php?grade_id=${gradeId}&direction=${direction}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                // Créer un modal d'erreur simple
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center;
                    z-index: 10000;
                `;
                modal.innerHTML = `
                    <div style="background: #2a1a1a; padding: 20px; border-radius: 10px; color: white; text-align: center;">
                        <h3 style="color: #ff6b6b;">⚠️ Erreur</h3>
                        <p>${data.message}</p>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                style="background: #f44336; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                            Fermer
                        </button>
                    </div>
                `;
                document.body.appendChild(modal);
            }
        })
        .catch(() => {
            alert("Une erreur est survenue.");
        });
}
</script>
</body>
</html>
<?php
require 'config.php';
checkAuth();

if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    die("ID étudiant invalide.");
}

$student_id = (int)$_GET['student_id'];

// Récupérer les infos de l'étudiant
$stmt = $pdo->prepare("SELECT fullname FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Étudiant non trouvé.");
}

// Récupérer ses notes
$grades = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? ORDER BY language, level, exam_date DESC");
$grades->execute([$student_id]);
$allGrades = $grades->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de résultats - <?= htmlspecialchars($student['fullname']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: white;
            color: black;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header h2 {
            margin: 8px 0;
            color: #7f8c8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .no-print {
            display: none;
        }
        @media print {
            body { font-size: 12pt; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>🎓 ELCI - Centre de Langues</h1>
    <h2>Fiche de Résultats</h2>
    <p><strong>Étudiant :</strong> <?= htmlspecialchars($student['fullname']) ?></p>
</div>

<?php if (count($allGrades) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Langue</th>
                <th>Niveau</th>
                <th>T1</th>
                <th>T2</th>
                <th>Moyenne</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allGrades as $g): ?>
            <tr>
                <td><?= htmlspecialchars($g['language']) ?></td>
                <td><?= htmlspecialchars($g['level']) ?></td>
                <td><?= number_format($g['t1'], 1) ?></td>
                <td><?= number_format($g['t2'], 1) ?></td>
                <td class="<?= $g['average'] >= 50 ? 'pass' : 'fail' ?>"><?= number_format($g['average'], 1) ?></td>
                <td><?= htmlspecialchars($g['status']) ?></td>
                <td><?= date('d/m/Y', strtotime($g['exam_date'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align: center; font-style: italic;">Aucune note enregistrée pour cet étudiant.</p>
<?php endif; ?>

<!-- Bouton d'impression (visible à l'écran, masqué à l'impression) -->
<div class="no-print" style="text-align: center; margin-top: 30px;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">
        🖨️ Imprimer cette fiche
    </button>
    <br><br>
    <a href="javascript:history.back()" style="color: #2196F3; text-decoration: none;">🔙 Retour</a>
</div>

</body>
</html>
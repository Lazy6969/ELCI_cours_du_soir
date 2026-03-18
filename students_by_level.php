<?php
require 'config.php';
checkAuth();

// Récupérer toutes les langues avec niveaux
$stmt = $pdo->query("SELECT DISTINCT language, level FROM grades ORDER BY language, level");
$levels = $stmt->fetchAll();

// Récupérer les filtres
$selectedLang = $_GET['lang'] ?? null;
$selectedLevel = $_GET['level'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étudiants par Niveau - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<body class="dark-theme">

<?php include 'header_nav.php'; ?>

<div class="main-content"><br>
    <h2>👥 Étudiants par Niveau</h2>

    <!-- Filtre -->
    <div class="filter-section card">
        <form method="GET">
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label for="lang">Langue :</label>
                    <select id="lang" name="lang" onchange="this.form.submit()">
    <option value="">Toutes les langues</option>
    <?php
    // Récupérer les langues uniques
    $stmt = $pdo->query("SELECT DISTINCT language FROM grades ORDER BY language");
    $uniqueLanguages = $stmt->fetchAll();
    foreach ($uniqueLanguages as $lang):
    ?>
        <option value="<?= htmlspecialchars($lang['language']) ?>" 
                <?= $selectedLang === $lang['language'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($lang['language']) ?>
        </option>
    <?php endforeach; ?>
</select>
                </div>

                <?php if ($selectedLang): ?>
                <div>
                    <label for="level">Niveau :</label>
                    <select id="level" name="level" onchange="this.form.submit()">
                        <option value="">Tous les niveaux</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT DISTINCT level FROM grades WHERE language = ? ORDER BY level");
                        $stmt->execute([$selectedLang]);
                        $filteredLevels = $stmt->fetchAll();
                        foreach ($filteredLevels as $fl):
                        ?>
                            <option value="<?= htmlspecialchars($fl['level']) ?>" 
                                    <?= $selectedLevel === $fl['level'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($fl['level']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
<?php if ($selectedLang): ?>
    <div style="text-align: right; margin: 10px 0;">
        <a href="print_students_level.php?lang=<?= urlencode($selectedLang) ?>&level=all" 
           target="_blank" class="btn btn-print">
            🖨️ Imprimer tous les niveaux
        </a>
    </div>
<?php endif; ?>
    <!-- Affichage des résultats -->
    <?php if ($selectedLang && $selectedLevel): ?>
        <!-- Un seul niveau -->
        <h3>📝 <?= htmlspecialchars($selectedLang) ?> - <?= htmlspecialchars($selectedLevel) ?></h3>
        <?php

    $stmt = $pdo->prepare("
    SELECT 
        s.fullname,
        s.birth_place,
        s.student_phone,
        s.parent_phone,
        g.language,
        g.level,
        COALESCE(g.t1, 0) as t1,
        COALESCE(g.t2, 0) as t2,
        COALESCE(g.average, 0) as average,
        CASE 
            WHEN g.status IS NULL THEN 'En cours'
            ELSE g.status
        END as status,
        COALESCE(g.exam_date, NOW()) as exam_date
    FROM students s
    JOIN grades g ON s.id = g.student_id
    WHERE g.language = ? AND g.level = ? AND g.is_active = 1
    ORDER BY s.fullname
");
        $stmt->execute([$selectedLang, $selectedLevel]);
        $students = $stmt->fetchAll();
        ?>

        <?php if ($students): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Lieu de naissance</th>
                        <th>Contact Étudiant</th>
                        <th>Contact Parent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['fullname']) ?></td>
                        <td><?= htmlspecialchars($s['birth_place']) ?></td>
                        <td><?= htmlspecialchars($s['student_phone']) ?></td>
                        <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total :</strong> <?= count($students) ?> étudiant(s)</p>
        <?php else: ?>
            <p>Aucun étudiant actif trouvé pour ce niveau.</p>
        <?php endif; ?>

    <?php elseif ($selectedLang): ?>
        <!-- Tous les niveaux de la langue -->
        <h3>📚 Tous les niveaux de <?= htmlspecialchars($selectedLang) ?></h3>
        <?php
        $stmt = $pdo->prepare("SELECT DISTINCT level FROM grades WHERE language = ? ORDER BY level");
        $stmt->execute([$selectedLang]);
        $allLevels = $stmt->fetchAll();

        foreach ($allLevels as $lvl):
            $levelName = $lvl['level'];
            $stmt2 = $pdo->prepare("
                SELECT s.fullname, s.birth_place, s.student_phone, s.parent_phone
                FROM students s
                INNER JOIN grades g ON s.id = g.student_id
                WHERE g.language = ? AND g.level = ? AND g.is_active = 1
                ORDER BY s.fullname
            ");
            $stmt2->execute([$selectedLang, $levelName]);
            $studentsForLevel = $stmt2->fetchAll();
        ?>
            <div class="level-section">
                <h4>📄 <?= htmlspecialchars($selectedLang) ?> - <?= htmlspecialchars($levelName) ?></h4>
                <?php if ($studentsForLevel): ?>
                    <div style="text-align: right; margin: 10px 0;">
    <a href="print_students_level.php?lang=<?= urlencode($selectedLang) ?>&level=<?= urlencode($levelName) ?>" 
       target="_blank" class="btn btn-print">
        🖨️ Imprimer ce niveau
    </a>
</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Lieu de naissance</th>
                                <th>Contact Étudiant</th>
                                <th>Contact Parent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentsForLevel as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['fullname']) ?></td>
                                <td><?= htmlspecialchars($s['birth_place']) ?></td>
                                <td><?= htmlspecialchars($s['student_phone']) ?></td>
                                <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Total :</strong> <?= count($studentsForLevel) ?> étudiant(s)</p>
                <?php else: ?>
                    <p>Aucun étudiant actif trouvé pour ce niveau.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p>Sélectionnez une langue pour voir les étudiants.</p>
    <?php endif; ?>
</div>

<!-- CSS pour les sections de niveau -->
<style>
.level-section {
    margin-bottom: 30px;
    padding: 20px;
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.level-section h4 {
    margin-top: 0;
    color: #2196F3;
    font-size: 18px;
}
</style>

<script src="assets/js/main.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
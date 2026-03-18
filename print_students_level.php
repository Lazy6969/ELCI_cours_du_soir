<?php
require 'config.php';
checkAuth();

$selectedLang = $_GET['lang'] ?? '';
$selectedLevel = $_GET['level'] ?? '';

// Récupérer les étudiants
if ($selectedLevel === 'all') {
    // Tous les niveaux
    $stmt = $pdo->prepare("
        SELECT s.fullname, s.birth_place, s.student_phone, s.parent_phone, g.level
        FROM students s
        JOIN grades g ON s.id = g.student_id
        WHERE g.language = ? AND g.is_active = 1
        ORDER BY g.level, s.fullname
    ");
    $stmt->execute([$selectedLang]);
    $students = $stmt->fetchAll();
    $title = "Tous les niveaux de $selectedLang";
} else {
    // Un seul niveau
    $stmt = $pdo->prepare("
        SELECT s.fullname, s.birth_place, s.student_phone, s.parent_phone
        FROM students s
        JOIN grades g ON s.id = g.student_id
        WHERE g.language = ? AND g.level = ? AND g.is_active = 1
        ORDER BY s.fullname
    ");
    $stmt->execute([$selectedLang, $selectedLevel]);
    $students = $stmt->fetchAll();
    $title = "$selectedLang - $selectedLevel";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Impression - <?= htmlspecialchars($title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
        @media print {
            body { font-size: 12pt; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <tr id="table_logo">
           <td id="table_logo">
                <img style="  position: absolute;
                                    top: 10px;
                                    left: 10px;
                                    width: 150px;
                                    height: 150px;
                                    opacity: 0.8;
                                    transition: opacity 0.3s, transform 0.3s;
                                    cursor: pointer;" 
                class="umg"src="photos/logo.png" alt="logo elci">
            <td id="table_logo">
            </td><br>
            <td id="table_logo">
                <h2>Étudiants <br> <?= htmlspecialchars($title) ?></h2>
            </td>
            <td id="table_logo">
                <img style="  position: absolute;
                                    top: 10px;
                                    right: 10px;
                                    width: 150px;
                                    height: 150px;
                                    opacity: 0.8;
                                    transition: opacity 0.3s, transform 0.3s;
                                    cursor: pointer;" 
                class="umg"src="photos/umg.png" alt="logo elci">
            <td>
        </tr>

<br><br><br>

    
    <?php if ($selectedLevel === 'all'): ?>
        <!-- Regrouper par niveau -->
        <?php
        $grouped = [];
        foreach ($students as $s) {
            $grouped[$s['level']][] = $s;
        }
        foreach ($grouped as $level => $list):
        ?>
            <h3><?= htmlspecialchars($selectedLang) ?> - <?= htmlspecialchars($level) ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Lieu de naissance</th>
                        <th>Contact Étudiant</th>
                        <th>Contact Parent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['fullname']) ?></td>
                        <td><?= htmlspecialchars($s['birth_place']) ?></td>
                        <td><?= htmlspecialchars($s['student_phone']) ?></td>
                        <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Un seul niveau -->
        <table>
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
    <?php endif; ?>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Imprimer cette page
        </button>
    </div>
</body>
</html>
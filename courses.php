<?php
require 'config.php';
checkAuth();

$languages = [
    'Anglais' => ['Real Beginner', 'Level 1', 'Level 2', 'Level 3', 'Level 4'],
    'Français' => ['N1', 'N2A', 'N2B'],
    'Allemand' => ['A1', 'A2', 'B1'],
    'Mandarin' => ['HSK1', 'HSK2', 'HSK3', 'HSK4']
];

// Ajout de note
if ($_POST && isset($_POST['add_grade'])) {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $language = trim($_POST['language'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $t1 = (float)($_POST['t1'] ?? 0);
    $t2 = (float)($_POST['t2'] ?? 0);
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

    if ($student_id > 0 && $language && $level) {
        // Désactiver les inscriptions actives existantes pour ce (étudiant, langue, niveau)
        $pdo->prepare("UPDATE grades SET is_active = 0 WHERE student_id = ? AND language = ? AND level = ?")
     ->execute([$student_id, $language, $level]);
        // Enregistrement
        $stmt = $pdo->prepare("INSERT INTO grades (student_id, language, level, t1, t2, average, status, exam_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->execute([$student_id, $language, $level, $t1, $t2, $avg, $status]);

        // Sauvegarde dans fichier .txt
        $dossier = strtolower($language);
        $dossier = str_replace(['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ô', 'ö', 'û', 'ü', 'ç'],
                              ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'o', 'o', 'u', 'u', 'c'], $dossier);
        $dossier = preg_replace('/[^a-z0-9]/', '', $dossier);
        
        $stmt = $pdo->prepare("SELECT fullname FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $studentName = $stmt->fetchColumn() ?: 'Inconnu';
        
        $log = "Étudiant: $studentName | Langue: $language | Niveau: $level | T1: $t1 | T2: $t2 | Moy: $avg | Statut: $status | Date: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents("data/$dossier/notes.txt", $log, FILE_APPEND | LOCK_EX);
    }
    saveStudentHistoryToFile($student_id, $pdo);

    header('Location: courses.php?msg=grade_added');
    exit();
}

// Recherche étudiants
$search = $_GET['search'] ?? '';
$students = $pdo->query("SELECT id, fullname FROM students WHERE fullname LIKE '%" . addslashes($search) . "%'")->fetchAll();

function safeHtml($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cours & Notes - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/all.min.css">
</head>
<body class="dark-theme">
    <?php include 'header_nav.php'; ?>
    <div class="main-content"><br>
        <h2>📚 Gestion des Cours et Notes</h2>

        <!-- Formulaire d'ajout de note -->
        <form method="POST" class="card form-grade">
            <h3>➕ Ajouter une note</h3>
            <div class="form-group">
                <label>Étudiant :</label>
                <input type="text" id="studentSearch" placeholder="🔍 Rechercher un étudiant..." class="search-input" onkeyup="filterStudents()">
                <div id="studentList" class="student-list">
                    <?php foreach ($students as $s): ?>
                        <div class="student-item" data-id="<?= $s['id'] ?>" onclick="selectStudent(<?= $s['id'] ?>, '<?= addslashes(htmlspecialchars($s['fullname'])) ?>')">
                            <?= htmlspecialchars($s['fullname']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selectedStudentId" name="student_id" required>
                <div id="selectedDisplay" class="selected-student" style="display: none;"></div>
                <span id="errorSelect" style="color: #f44336; display: none;">Veuillez sélectionner un étudiant.</span>
            </div>
            <select name="language" onchange="updateLevels(this.value)" required>
                <option value="">Langue</option>
                <?php foreach ($languages as $lang => $levels): ?>
                    <option value="<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($lang) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="level" id="levelSelect" required>
                <option value="">Niveau</option>
            </select>
            <input type="number" step="0.001" min="0" max="100" name="t1" placeholder="Note Trimestre 1 (0-100)" >
            <input type="number" step="0.001" min="0" max="100" name="t2" placeholder="Note Trimestre 2 (0-100)" >
            <button type="submit" name="add_grade" class="btn btn-success">Enregistrer la note</button>
        </form>

        <div class="stat-card">
    <i class="fas fa-book"></i>

    <h3><?= $pdo->query("SELECT COUNT(*) FROM grades WHERE is_active = 1")->fetchColumn() ?></h3>
    <p>Notes </p>
</div>

        <!-- Barre de recherche pour les résultats -->
        <div class="search-bar">
            <input type="text" id="searchGrades" placeholder="Rechercher par étudiant, langue ou niveau..." onkeyup="filterGradesTable()">
            <button class="btn btn-search" onclick="filterGradesTable()">🔍 Rechercher</button>
        </div>

        <!-- Résultats -->
        <h3>📋 Fiches de résultats</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Langue</th>
                    <th>Drapeau</th>
                    <th>Niveau</th>
                    <th>T1</th>
                    <th>T2</th>
                    <th>Moyenne</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("
    SELECT 
        s.id as student_id,
        s.fullname,
        g.language,
        g.level,
        COALESCE(g.t1, 0) as t1,
        COALESCE(g.t2, 0) as t2,
        COALESCE(g.average, 0) as average,
        COALESCE(g.status, 'En cours') as status,
        COALESCE(g.exam_date, NOW()) as exam_date
    FROM students s
    LEFT JOIN grades g ON s.id = g.student_id AND g.is_active = 1
    ORDER BY average DESC, exam_date DESC
");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td class="print-fullname"><?= safeHtml($row['fullname']), '' ?></td>
                    <td class="print-language"><?= safeHtml($row['language']), '' ?></td>
                    <?php
$flagMap = [
    'Anglais' => 'gb.png',
    'Français' => 'fr.png',
    'Allemand' => 'de.png',
    'Mandarin' => 'cn.png'
];
$flagFile = '';
if (!empty($row['language']) && isset($flagMap[$row['language']])) {
    $flagFile = $flagMap[$row['language']];
}
?>
<td class="flag-cell">
    <?php if ($flagFile): ?>
        <img src="assets/img/flags/<?= $flagFile ?>" alt="<?= htmlspecialchars($row['language']) ?>" class="flag-img">
    <?php endif; ?>
</td>
                    <td class="print-level"><?= safeHtml($row['level']) , '' ?></td>
                    <td><?= number_format($row['t1'], 3) ?></td>
                    <td><?= number_format($row['t2'], 3) ?></td>
                    <td class="<?= $row['average'] >= 50 ? 'pass' : 'fail' ?>"><?= number_format($row['average'], 3) ?></td>
                    <td class="print-status" style="<?= $row['status'] === 'Admis' ? 'color: #4CAF50;' : ($row['status'] === 'Échec' ? 'color: #f44336;' : 'color: #FF9800;') ?>">
    <?= htmlspecialchars($row['status']) ?>
</td>
                    <td><?= htmlspecialchars($row['exam_date']) ?></td>
                    <td>
    <a href="student_detail_courses.php?id=<?= $row['student_id'] ?>" class="btn btn-view">👁️ Voir</a>
</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button class="btn btn-print" onclick="preparePrint()">🖨️ Imprimer les résultats</button>
    </div>

    <!-- Formulaire caché pour impression -->
    <form id="printForm" method="POST" action="print_search.php" target="_blank">
        <input type="hidden" name="results_json" id="resultsJsonInput">
    </form>

    <script>
        const levelsData = <?= json_encode($languages) ?>;
        function updateLevels(lang) {
            const select = document.getElementById('levelSelect');
            select.innerHTML = '<option value="">Niveau</option>';
            if (levelsData[lang]) {
                levelsData[lang].forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l;
                    opt.textContent = l;
                    select.appendChild(opt);
                });
            }
        }

        // Fonction de filtrage des étudiants
        function filterStudents() {
            const input = document.getElementById('studentSearch');
            const filter = input.value.toLowerCase();
            const list = document.getElementById('studentList');
            const items = list.querySelectorAll('.student-item');
            let found = false;

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = '';
                    found = true;
                } else {
                    item.style.display = 'none';
                }
            });

            list.classList.toggle('show', found || filter.length > 0);
        }

        // Sélectionner un étudiant
        function selectStudent(id, name) {
            document.getElementById('selectedStudentId').value = id;
            document.getElementById('studentSearch').value = name;
            document.getElementById('selectedDisplay').textContent = '✅ ' + name;
            document.getElementById('selectedDisplay').style.display = 'block';
            document.getElementById('errorSelect').style.display = 'none';
            document.getElementById('studentList').classList.remove('show');
        }

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const studentId = document.getElementById('selectedStudentId').value;
            if (!studentId) {
                e.preventDefault();
                document.getElementById('errorSelect').style.display = 'block';
                alert("Veuillez sélectionner un étudiant.");
            }
        });

        // Filtrage des résultats
        function filterGradesTable() {
            const input = document.getElementById('searchGrades');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        // Impression
        function preparePrint() {
            const rows = document.querySelectorAll('table.data-table tbody tr');
            const results = [];

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 8) {
                        results.push({
                            fullname: cells[0].textContent.trim(),
                            language: cells[1].textContent.trim(),
                            level: cells[2].textContent.trim(),
                            status: cells[6].textContent.trim()
                        });
                    }
                }
            });

            if (results.length === 0) {
                alert("Aucun résultat à imprimer.");
                return;
            }

            document.getElementById('resultsJsonInput').value = JSON.stringify(results);
            document.getElementById('printForm').submit();
        }
    </script>
    <script src="assets/js/main.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>
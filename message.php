<?php
require 'config.php';
checkAuth();

$message = '';
$target = 'all'; // 'all', 'level', 'student'
$selectedLevel = '';
$selectedStudent = '';
$sent = false;
$error = '';

if ($_POST && isset($_POST['send_message'])) {
    $message = trim($_POST['message'] ?? '');
// Autoriser seulement lettres, chiffres, espaces, et ponctuation basique
$message = preg_replace('/[^a-zA-Z0-9\s\.\,\;\:\!\?\-\(\)\[\]\'\"\éèêëàâäôöûüçÉÈÊËÀÂÄÔÖÛÜÇ]/u', '', $message);
$message = trim($message);
    $target = $_POST['target'] ?? 'all';
    $selectedLevel = $_POST['level'] ?? '';
    $selectedStudent = $_POST['student_id'] ?? '';
    $selectedStudents = $_POST['selected_students'] ?? []; // Tableau d'IDs

    if (!$message) {
        $error = "Veuillez écrire un message.";
    } else {
        $numbers = [];

        if ($target === 'all') {
            // Tous les numéros
            $stmt = $pdo->query("SELECT student_phone, parent_phone FROM students");
            while ($row = $stmt->fetch()) {
                if (!empty($row['student_phone'])) $numbers[] = $row['student_phone'];
                if (!empty($row['parent_phone'])) $numbers[] = $row['parent_phone'];
            }
        } elseif ($target === 'level' && $selectedLevel) {
            // Par niveau
            $stmt = $pdo->prepare("
                SELECT s.student_phone, s.parent_phone
                FROM students s
                JOIN grades g ON s.id = g.student_id
                WHERE g.level = ? AND g.is_active = 1
            ");
            $stmt->execute([$selectedLevel]);
            while ($row = $stmt->fetch()) {
                if (!empty($row['student_phone'])) $numbers[] = $row['student_phone'];
                if (!empty($row['parent_phone'])) $numbers[] = $row['parent_phone'];
            }
        } elseif ($target === 'student' && $selectedStudent) {
            // À un seul étudiant
            $stmt = $pdo->prepare("SELECT student_phone, parent_phone FROM students WHERE id = ?");
            $stmt->execute([$selectedStudent]);
            $student = $stmt->fetch();
            if ($student) {
                if (!empty($student['student_phone'])) $numbers[] = $student['student_phone'];
                if (!empty($student['parent_phone'])) $numbers[] = $student['parent_phone'];
            }
        } elseif ($target === 'multiple' && !empty($selectedStudents)) {
            // À plusieurs étudiants
            $placeholders = str_repeat('?,', count($selectedStudents) - 1) . '?';
            $stmt = $pdo->prepare("SELECT student_phone, parent_phone FROM students WHERE id IN ($placeholders)");
            $stmt->execute($selectedStudents);
            while ($row = $stmt->fetch()) {
                if (!empty($row['student_phone'])) $numbers[] = $row['student_phone'];
                if (!empty($row['parent_phone'])) $numbers[] = $row['parent_phone'];
            }
        }

        if (empty($numbers)) {
            $error = "Aucun numéro de téléphone trouvé pour ce critère.";
        } else {
            // Sauvegarder dans le log
            $log = "[" . date('Y-m-d H:i:s') . "] Par: " . $_SESSION['username'] . "\nMessage: $message\nDestinataires: " . implode(', ', $numbers) . "\n\n";
            file_put_contents('data/sms_log.txt', $log, FILE_APPEND | LOCK_EX);
            
            $sent = true;
            $count = count($numbers);
        }
    }
}

// Récupérer les niveaux pour le select
$stmt = $pdo->query("SELECT DISTINCT level FROM grades ORDER BY level");
$levels = $stmt->fetchAll();

// Récupérer les étudiants pour le select
$stmt = $pdo->query("SELECT id, fullname FROM students ORDER BY fullname");
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Envoyer un message - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">
    <?php include 'header_nav.php'; ?><br><br>
    <div class="main-content">
        <h2>📤 Envoyer un message à vos contacts</h2>

        <?php if ($sent): ?>
            <div class="alert success">
                ✅ Message envoyé à <strong><?= $count ?></strong> numéros !<br>
                <small>Le message a été enregistré dans <code>data/sms_log.txt</code></small>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card notes-card">
            <h3>📝 Bloc-notes Administratif</h3>
            <p>Utilisez cet espace pour noter des rappels, des tâches ou des informations importantes.</p>

            <form method="POST" class="notes-form">
                <textarea name="message" class="notes-textarea" placeholder="Écrivez votre message ici..."><?= htmlspecialchars($message) ?></textarea>

                <!-- Options d'envoi -->
            
<div style="margin: 15px 0;">
    <label>
        <table>
            <tr>
                <td>
                    📱 Envoyer à tous les numéros
                </td>
                <td style="padding-left:52px;">
                    <input type="radio" name="target" value="all" <?= $target === 'all' ? 'checked' : '' ?> onclick="toggleTargets('all')">
                </td>
            </tr>
        </table>
        
        
    </label><br>

    <label>
        <table>
            <tr>
                <td>
                        🎯 Envoyer par niveau
                </td>
                <td style="padding-left:104px;">
                     <input type="radio" name="target" value="level" <?= $target === 'level' ? 'checked' : '' ?> onclick="toggleTargets('level')">
                </td>
            </tr>
        </table>
       
        
    </label>
    <select name="level" id="levelSelect" style="margin-left: 10px;" <?= $target !== 'level' ? 'disabled' : '' ?>>
        <option value="">Sélectionner un niveau</option>
        <?php foreach ($levels as $l): ?>
            <option value="<?= htmlspecialchars($l['level']) ?>" <?= $selectedLevel === $l['level'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['level']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>
        <table>
            <tr>
                <td>
                    👤 Envoyer à un seul étudiant
                </td>
                <td style="padding-left:54px;">
                    <input type="radio" name="target" value="student" <?= $target === 'student' ? 'checked' : '' ?> onclick="toggleTargets('student')">
                </td>
            </tr>
        </table>
        
        
    </label>
    
   <!-- Champ de recherche pour les étudiants -->
<div id="studentSearchContainer" style="margin-left: 10px; display: <?= $target === 'student' ? 'block' : 'none' ?>;">
    <input type="text" id="studentSearchMsg" placeholder="🔍 Rechercher un étudiant..." class="search-input" onkeyup="filterStudentsMsg()">
    
    <!-- Liste filtrable -->
    <div id="studentListMsg" class="student-list">
        <?php foreach ($students as $s): ?>
            <div class="student-item" data-id="<?= $s['id'] ?>" onclick="selectStudentMsg(<?= $s['id'] ?>, '<?= addslashes(htmlspecialchars($s['fullname'])) ?>')">
                <?= htmlspecialchars($s['fullname']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Champ caché pour le formulaire -->
    <input type="hidden" id="selectedStudentIdMsg" name="student_id" value="<?= htmlspecialchars($selectedStudent) ?>">

    <!-- Affichage de l'étudiant sélectionné -->
    <div id="selectedDisplayMsg" class="selected-student" style="display: <?= $selectedStudent ? 'block' : 'none' ?>;">
        ✅ <?= htmlspecialchars($students[array_search($selectedStudent, array_column($students, 'id'))]['fullname'] ?? '') ?>
    </div>
</div>
<label>
    <table>
            <tr>
                <td>
                    👥 Envoyer à plusieurs étudiants
                </td>
                <td style="padding-left:30px;">
                    <input type="radio" name="target" value="multiple" <?= $target === 'multiple' ? 'checked' : '' ?> onclick="toggleTargets('multiple')">
                </td>
            </tr>
        </table>
    
    
</label>
<div id="multipleStudentsContainer" style="margin-left: 10px; display: <?= $target === 'multiple' ? 'block' : 'none' ?>;">
    <!-- Barre de recherche -->
    <input type="text" id="searchMultiple" placeholder="🔍 Rechercher un étudiant..." 
           class="search-input" onkeyup="filterMultipleStudents()">
    
    <!-- Liste des étudiants -->
    <div id="multipleStudentsList" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border); border-radius: 6px; padding: 10px; background: var(--card-bg); margin-top: 10px;">
        <?php foreach ($students as $s): ?>
            <label class="student-checkbox" style="display: block; margin: 5px 0; cursor: pointer;" data-name="<?= htmlspecialchars(strtolower($s['fullname'])) ?>">
                <input type="checkbox" name="selected_students[]" value="<?= $s['id'] ?>" 
                       <?= in_array($s['id'], $selectedStudents ?? []) ? 'checked' : '' ?>>
                <?= htmlspecialchars($s['fullname']) ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>
</div>

                <button type="submit" name="send_message" class="btn btn-success">💾 Enregistrer et envoyer</button>
            </form>
        </div>

        <?php if (!$sent && !$error): ?>
            <div class="card" style="margin-top: 20px;">
                <h3>ℹ️ Informations</h3>
                <p>Ce système <strong>simule l'envoi de SMS</strong>. En production, vous devrez intégrer une API comme :</p>
                <ul>
                    <li>Twilio (international)</li>
                    <li>Africa's Talking (Afrique)</li>
                    <li>Orange SMS API (Madagascar)</li>
                </ul>
                <p>Les numéros sont extraits de la base de données (étudiants + parents).</p>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
function toggleTargets(target) {
    const levelSelect = document.getElementById('levelSelect');
    const studentSearchContainer = document.getElementById('studentSearchContainer');
    
    // Cacher tous les conteneurs
    levelSelect.style.display = 'none';
    studentSearchContainer.style.display = 'none';
    
    // Activer le bon conteneur
    if (target === 'level') {
        levelSelect.style.display = 'inline-block';
        levelSelect.disabled = false;
    } else if (target === 'student') {
        studentSearchContainer.style.display = 'block';
        document.getElementById('selectedStudentIdMsg').disabled = false;
    } else {
        levelSelect.disabled = true;
        document.getElementById('selectedStudentIdMsg').disabled = true;
    }
    document.getElementById('levelSelect').style.display = 'none';
    document.getElementById('studentSearchContainer').style.display = 'none';
    document.getElementById('multipleStudentsContainer').style.display = 'none';
    
    if (target === 'level') {
        document.getElementById('levelSelect').style.display = 'inline-block';
    } else if (target === 'student') {
        document.getElementById('studentSearchContainer').style.display = 'block';
    } else if (target === 'multiple') {
        document.getElementById('multipleStudentsContainer').style.display = 'block';
    }

}

// Filtrer les étudiants dans message.php
function filterStudentsMsg() {
    const input = document.getElementById('studentSearchMsg');
    const filter = input.value.toLowerCase();
    const list = document.getElementById('studentListMsg');
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

// Sélectionner un étudiant dans message.php
function selectStudentMsg(id, name) {
    document.getElementById('selectedStudentIdMsg').value = id;
    document.getElementById('studentSearchMsg').value = name;
    document.getElementById('selectedDisplayMsg').textContent = '✅ ' + name;
    document.getElementById('selectedDisplayMsg').style.display = 'block';
    document.getElementById('studentListMsg').classList.remove('show');
}

// Filtrer les étudiants dans la sélection multiple
function filterMultipleStudents() {
    const input = document.getElementById('searchMultiple');
    const filter = input.value.toLowerCase();
    const labels = document.querySelectorAll('#multipleStudentsList .student-checkbox');
    
    labels.forEach(label => {
        const name = label.getAttribute('data-name');
        if (name.includes(filter)) {
            label.style.display = 'block';
        } else {
            label.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
<?php
require 'config.php';
checkAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = (int)$_GET['id'];

if ($_POST && isset($_POST['update_student'])) {
    $stmt = $pdo->prepare("UPDATE students SET fullname=?, birth_date=?, birth_place=?, student_phone=?, parent_phone=? WHERE id=?");
    $stmt->execute([
        $_POST['fullname'],
        $_POST['birth_date'],
        $_POST['birth_place'],
        $_POST['student_phone'],
        $_POST['parent_phone'],
        $id
    ]);
    header("Location: student_detail.php?id=$id&msg=updated");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    die("Étudiant non trouvé.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier - <?= htmlspecialchars($student['fullname']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

<?php include 'header_nav.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>✏️ Modifier l’étudiant</h2>
        <form method="POST">
            <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($student['birth_date']) ?>" required>
            <input type="text" name="birth_place" value="<?= htmlspecialchars($student['birth_place']) ?>" required>
            <input type="tel" name="student_phone" value="<?= htmlspecialchars($student['student_phone']) ?>" required>
            <input type="tel" name="parent_phone" value="<?= htmlspecialchars($student['parent_phone']) ?>" required>
            <button type="submit" name="update_student" class="btn btn-success">✅ Enregistrer les modifications</button>
        </form>
    </div>
    <a href="student_detail.php?id=<?= $id ?>" class="btn btn-primary">🔙 Annuler</a>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
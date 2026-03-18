<?php
require 'config.php';
checkAuth();

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'ID étudiant manquant.']);
    exit();
}

// Vérifier que l'étudiant existe
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Étudiant non trouvé.']);
    exit();
}

// Supprimer TOUTES les notes de l'étudiant
$pdo->prepare("DELETE FROM grades WHERE student_id = ?")->execute([$student_id]);

// Sauvegarder dans le log
$log = "[" . date('Y-m-d H:i:s') . "] Par: " . $_SESSION['username'] . "\nSuppression de tout l'historique de l'étudiant :\nID: $student_id\nNom: " . $student['fullname'] . "\n\n";
file_put_contents('data/admin_notes.txt', $log, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true]);
exit();
?>
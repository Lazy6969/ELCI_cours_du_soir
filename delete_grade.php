<?php
// Démarrer la session et vérifier l'authentification
require 'config.php';
checkAuth();

// Désactiver les erreurs (en production)
error_reporting(0);

$grade_id = $_GET['grade_id'] ?? null;

if (!$grade_id) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit();
}

try {
    // Vérifier que la note existe
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([$grade_id]);
    $grade = $stmt->fetch();

    if ($grade['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Impossible de supprimer une inscription active.']);
    exit();
}

    // Supprimer la note
    $pdo->prepare("DELETE FROM grades WHERE id = ?")->execute([$grade_id]);

    // Sauvegarder dans le fichier
    $log = "[" . date('Y-m-d H:i:s') . "] Par: " . $_SESSION['username'] . "\nSuppression de la note ID: $grade_id\n";
    file_put_contents('data/admin_notes.txt', $log, FILE_APPEND | LOCK_EX);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}

exit();
?>
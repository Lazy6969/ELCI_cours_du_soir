<?php
require 'config.php';
checkAuth();

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    die("ID étudiant manquant.");
}

// Récupérer les données
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Étudiant non trouvé.");
}

$stmt = $pdo->prepare("
    SELECT * FROM grades 
    WHERE student_id = ? 
    ORDER BY exam_date DESC
");
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();

// Générer le contenu
$content = "===============================\n";
$content .= "HISTORIQUE DE L'ÉTUDIANT\n";
$content .= "===============================\n\n";

$content .= "Nom complet : " . $student['fullname'] . "\n";
$content .= "Date de naissance : " . $student['birth_date'] . "\n";
$content .= "Lieu de naissance : " . $student['birth_place'] . "\n";
$content .= "Contact étudiant : " . $student['student_phone'] . "\n";
$content .= "Contact parent : " . $student['parent_phone'] . "\n";
$content .= "ID étudiant : " . $student['id'] . "\n";
$content .= "Date d'export : " . date('d/m/Y H:i:s') . "\n\n";

$content .= "===============================\n";
$content .= "INSCRIPTIONS\n";
$content .= "===============================\n\n";

if ($grades) {
    foreach ($grades as $g) {
        $content .= "Langue : " . $g['language'] . "\n";
        $content .= "Niveau : " . $g['level'] . "\n";
        $content .= "T1 : " . $g['t1'] . "\n";
        $content .= "T2 : " . $g['t2'] . "\n";
        $content .= "Moyenne : " . $g['average'] . "\n";
        $content .= "Statut : " . $g['status'] . "\n";
        $content .= "Date : " . $g['exam_date'] . "\n";
        $content .= "État : " . ($g['is_active'] ? 'Actif' : 'Archivé') . "\n";
        $content .= "ID inscription : " . $g['id'] . "\n";
        $content .= "-------------------------------\n\n";
    }
} else {
    $content .= "Aucune inscription trouvée.\n";
}

// Nom du fichier
$filename = 'historique_' . strtolower(str_replace(' ', '_', $student['fullname'])) . '.txt';

// En-têtes pour le téléchargement
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));

echo $content;
exit();
?>
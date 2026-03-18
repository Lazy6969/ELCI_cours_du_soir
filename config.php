<?php

session_start();

$admins = [
    'FIANTSOANARIVO' => '$2y$10$vKQJOp0/zOXOzptyhHmS5ehVuKC6JOW4Hfc2Fi7o4kCBa1s7iEUqO', // ← Remplacez par le vrai hash
    'MIRINDRA' => '$2y$10$XQejQVmiEnEUtoh68AwxqOPWJin9q17AfLxLp59uvp.gStA1YPtCu',
    // 'NouvelAdmin' => '$2y$10$YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',
];

// Connexion BDD (Laragon local)
$host = 'localhost';
$dbname = 'elci_cours_soir';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification de l'authentification
function checkAuth() {
    global $admins;
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header('Location: index.php');
        exit();
    }
}
function saveStudentHistoryToFile($student_id, $pdo) {
    // Récupérer les données
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) return;
    
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? ORDER BY exam_date DESC");
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll();
    
    // Générer le contenu
    $content = "Dernière mise à jour : " . date('Y-m-d H:i:s') . "\n\n";
    $content .= "Étudiant : " . $student['fullname'] . " (ID: $student_id)\n";
    $content .= "Contact : " . $student['student_phone'] . " / " . $student['parent_phone'] . "\n\n";
    
    foreach ($grades as $g) {
        $content .= "[" . $g['exam_date'] . "] " . $g['language'] . " - " . $g['level'] . " | T1:" . $g['t1'] . " T2:" . $g['t2'] . " Moy:" . $g['average'] . " (" . $g['status'] . ")\n";
    }
    
    // Sauvegarder dans data/historiques/
    $filename = 'data/historiques/historique_' . $student_id . '.txt';
    file_put_contents($filename, $content);
}
?>
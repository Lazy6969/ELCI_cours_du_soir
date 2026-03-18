<?php
require 'config.php';
checkAuth();

header('Content-Type: application/json');

try {
    $grade_id = $_GET['grade_id'] ?? null;
    $direction = $_GET['direction'] ?? 'up';

    if (!$grade_id) {
        throw new Exception("ID d'inscription manquant.");
    }

    // Récupérer l'ancienne inscription
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([$grade_id]);
    $old = $stmt->fetch();

    if (!$old) {
        throw new Exception("Inscription non trouvée.");
    }

    // Désactiver l'ancienne
    $pdo->prepare("UPDATE grades SET is_active = 0 WHERE id = ?")->execute([$grade_id]);

    // Définir les niveaux
    $languages = [
        'Anglais' => ['Real Beginner', 'Level 1', 'Level 2', 'Level 3', 'Level 4'],
        'Français' => ['N1', 'N2A', 'N2B'],
        'Allemand' => ['A1', 'A2', 'B1'],
        'Mandarin' => ['HSK1', 'HSK2', 'HSK3', 'HSK4']
    ];
// Déterminer le niveau suivant
    $next_level = '';
    if (isset($languages[$old['language']])) {
        $levels = $languages[$old['language']];
        $key = array_search($old['level'], $levels);
        
        if ($key !== false) {
            if ($direction === 'up' && isset($levels[$key + 1])) {
                $next_level = $levels[$key + 1];
            } elseif ($direction === 'down' && $key > 0) {
                $next_level = $levels[$key - 1];
            }
        }
    }

    if ($next_level) {
    // Désactiver TOUTES les inscriptions actives pour ce (étudiant, langue, nouveau_niveau)
    $pdo->prepare("UPDATE grades SET is_active = 0 WHERE student_id = ? AND language = ? AND level = ?")
         ->execute([$old['student_id'], $old['language'], $next_level]);
    
    // Créer la nouvelle inscription
$pdo->prepare("INSERT INTO grades (student_id, language, level, t1, t2, average, status, is_active, exam_date) 
               VALUES (?, ?, ?, 0, 0, 0, 'En cours', 1, NOW())")
     ->execute([$old['student_id'], $old['language'], $next_level]);

echo json_encode(['success' => true]);
 
} else {
        $message = $direction === 'up' 
            ? "Impossible de monter : vous êtes déjà au niveau le plus élevé pour la langue «{$old['language']}»."
            : "Impossible de descendre : vous êtes déjà au niveau le plus bas pour la langue «{$old['language']}».";
        echo json_encode(['success' => false, 'message' => $message]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit();
?>
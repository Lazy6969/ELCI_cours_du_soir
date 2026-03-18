<?php
require 'config.php';
checkAuth();

$currentContent = '';

// 1. Enregistrer si formulaire soumis
if ($_POST && isset($_POST['save_note'])) {
    // Récupérer le contenu SANS filtrage agressif
    $content = trim($_POST['content'] ?? '');
    
    // Nettoyer seulement les balises HTML (optionnel)
    // $content = strip_tags($content); // Décommentez si vous ne voulez pas de HTML
    
    if ($content !== '') {
        // Sauvegarder en BDD
        if ($note) {
            $pdo->prepare("UPDATE admin_notes SET content = ? WHERE id = ?")->execute([$content, $note['id']]);
        } else {
            $pdo->prepare("INSERT INTO admin_notes (content) VALUES (?)")->execute([$content]);
        }

        // Sauvegarder dans fichier .txt (encodage UTF-8)
        $log = "[" . date('Y-m-d H:i:s') . "] Par: " . $_SESSION['username'] . "\n" . $content . "\n\n";
        file_put_contents('data/admin_notes.txt', $log, FILE_APPEND | LOCK_EX);
    }

    header('Location: notes.php?msg=saved');
    exit();
}

// 2. LIRE LA NOTE APRÈS TOUTE LOGIQUE DE SAUVEGARDE
$stmt = $pdo->query("SELECT content FROM admin_notes ORDER BY id DESC LIMIT 1");
$note = $stmt->fetch();
$currentContent = $note ? htmlspecialchars($note['content']) : '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bloc-notes - ELCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme"><br><br>

<?php include 'header_nav.php'; ?>

<div class="main-content">
    <div class="card notes-card">
        <h2>📝 Bloc-notes Administratif</h2>
        <p>Utilisez cet espace pour noter des rappels, des tâches ou des informations importantes.</p>

        <form method="POST">
           <textarea name="content" class="notes-textarea" placeholder="Écrivez vos notes ici..."><?= htmlspecialchars($currentContent, ENT_QUOTES, 'UTF-8') ?></textarea>
            <button type="submit" name="save_note" class="btn btn-success">💾 Enregistrer la note</button>
        </form>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
            <div class="alert success" style="margin-top: 15px;">
                ✅ Note enregistrée avec succès !
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
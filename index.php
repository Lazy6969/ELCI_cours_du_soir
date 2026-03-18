<?php
error_reporting(0); // Désactive les warnings (à utiliser seulement en prod)
// Ou pour développement :
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<?php
require 'config.php';

// Gestion des tentatives
$attemptsKey = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
$lastAttemptKey = 'last_attempt_' . $_SERVER['REMOTE_ADDR'];

$attempts = $_SESSION[$attemptsKey] ?? 0;
$lastAttempt = $_SESSION[$lastAttemptKey] ?? 0;
$now = time();

// Réinitialiser si plus de 60 secondes
if ($now - $lastAttempt > 60) {
    $attempts = 0;
    $_SESSION[$attemptsKey] = 0;
}

$error = '';
$countdown = 0;

if ($_POST) {
    if ($attempts >= 3 && ($now - $lastAttempt) < 60) {
        $countdown = 60 - ($now - $lastAttempt);
        $error = "Trop de tentatives. Veuillez attendre $countdown secondes.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (isset($admins[$username]) && password_verify($password, $admins[$username])) {
            // Réinitialiser les tentatives
            $_SESSION[$attemptsKey] = 0;
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit();
        } else {
            // Incrémenter les tentatives
            $attempts++;
            $_SESSION[$attemptsKey] = $attempts;
            $_SESSION[$lastAttemptKey] = $now;
            
            if ($attempts >= 3) {
                $countdown = 60;
                $error = "Trop de tentatives. Veuillez attendre 60 secondes.";
            } else {
                $error = "Identifiants incorrects. Tentatives restantes: " . (3 - $attempts);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - ELCI Cours du Soir</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container animated fadeIn">
        <h2>🔐 ELCI - Cours du Soir</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($countdown > 0): ?>
            <div class="countdown" id="countdown">
                ⏳ Veuillez patienter... <span id="timer"><?= $countdown ?></span> secondes
            </div>
            <script>
                let timeLeft = <?= $countdown ?>;
                const timerElement = document.getElementById('timer');
                const countdownElement = document.getElementById('countdown');
                
                const countdown = setInterval(() => {
                    timeLeft--;
                    timerElement.textContent = timeLeft;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        countdownElement.style.display = 'none';
                        location.reload(); // Recharger pour réactiver le formulaire
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <form method="POST" autocomplete="off">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        <?php endif; ?>
    </div>
    
    <style>
        .countdown {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
        }
        .alert.error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
    </style>
</body>
</html>
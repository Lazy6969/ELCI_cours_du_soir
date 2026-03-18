<?php
error_reporting(0); // Désactive les warnings (à utiliser seulement en prod)
// Ou pour développement :
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<?php
// logout.php

session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, supprimez aussi le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: index.php");
exit();
?>
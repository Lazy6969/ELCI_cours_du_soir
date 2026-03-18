<?php
// Mot de passe à hacher
$password = 'mirindra123';

// Générer le hash
$hash = password_hash($password, PASSWORD_DEFAULT);

// Afficher le hash
echo "<h2>Hash pour '$password' :</h2>";
echo '<code style="background:#f0f0f0; padding:10px; display:block;">' . $hash . '</code>';
echo "<p>Copiez ce hash et collez-le dans config.php</p>";
?>
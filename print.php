<?php
error_reporting(0); // Désactive les warnings (à utiliser seulement en prod)
// Ou pour développement :
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<?php
require 'config.php';
checkAuth();

// Vérifier si c'est une impression de recherche filtrée
if (isset($_POST['print_type']) && $_POST['print_type'] === 'students_filtered') {
    $students = json_decode($_POST['filtered_students'], true);
    
    if (!is_array($students)) {
        die("Aucune donnée à imprimer.");
    }
} else {
    // Ancien comportement (tous les étudiants)
    $stmt = $pdo->query("SELECT * FROM students");
    $students = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Impression - Étudiants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        @media print {
            body { font-size: 12pt; }
        }
    </style>
</head>
<body>

<tr id="table_logo">
            <td id="table_logo">
                <img style="  position: absolute;
                                    top: 10px;
                                    left: 10px;
                                    width: 150px;
                                    height: 150px;
                                    opacity: 0.8;
                                    transition: opacity 0.3s, transform 0.3s;
                                    cursor: pointer;" 
                class="umg"src="photos/logo.png" alt="logo elci">
           <td id="table_logo">
           </td><br>
            <td id="table_logo">
                <h4 style="text-align:center;font-size:20px;">Liste des étudiants - ELCI</h4>
            </td>
            <td id="table_logo">
                <img style="  position: absolute;
                                    top: 10px;
                                    right: 10px;
                                    width: 150px;
                                    height: 150px;
                                    opacity: 0.8;
                                    transition: opacity 0.3s, transform 0.3s;
                                    cursor: pointer;" 
                class="umg"src="photos/umg.png" alt="logo elci">
           <td>
        </tr>

<br><br><br>

<table>
    <thead>
        <tr>
            <th>Nom et prénoms</th>
            <th>Date de naissance</th>
            <th>Lieu de naissance</th>
            <th>Contact étudiant</th>
            <th>Contact parent</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['fullname']) ?></td>
            <td><?= htmlspecialchars($s['birth_date']) ?></td>
            <td><?= htmlspecialchars($s['birth_place']) ?></td>
            <td><?= htmlspecialchars($s['student_phone']) ?></td>
            <td><?= htmlspecialchars($s['parent_phone']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Optionnel : imprimer automatiquement
// window.print();
</script>

</body>
</html>
<?php
require 'config.php';
checkAuth();

if (!isset($_POST['results_json'])) {
    die("Aucune donnée reçue.");
}

$results = json_decode($_POST['results_json'], true);

if (!is_array($results) || count($results) === 0) {
    die("Aucune donnée valide à imprimer.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de l'examen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .admis { color: green; font-weight: bold; }
        .echec { color: red; font-weight: bold; }
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
            </td><br>';
            <td id="table_logo">
                <h1 style="text-align:center;font-size:20px;">Résultats de l'examen</h1>
            </td>';
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
            <th>Étudiant</th>
            <th>Langue</th>
            <th>Niveau</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['language']) ?></td>
            <td><?= htmlspecialchars($row['level']) ?></td>
            <td class="<?= strtolower($row['status']) === 'admis' ? 'admis' : 'echec' ?>">
                <?= htmlspecialchars($row['status']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
window.print();
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trajets Tours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #dc3545;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← Retour à l'accueil</a>
        <h1>Trajets Tours avec Détails</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trajet</th>
                    <th>Chauffeur</th>
                    <th>Départ → Arrivée</th>
                    <th>Km</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Recette</th>
                    <th>Carburant</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($trajets)): ?>
                    <?php foreach ($trajets as $trajet): ?>
                        <tr>
                            <td><?= htmlspecialchars($trajet['id']) ?></td>
                            <td><?= htmlspecialchars($trajet['nom_trajet']) ?></td>
                            <td><?= htmlspecialchars($trajet['chauffeur_nom']) ?></td>
                            <td><?= htmlspecialchars($trajet['point_depart']) ?> → <?= htmlspecialchars($trajet['point_arriver']) ?></td>
                            <td><?= htmlspecialchars($trajet['kilometre']) ?> km</td>
                            <td><?= htmlspecialchars($trajet['date_debut']) ?></td>
                            <td><?= htmlspecialchars($trajet['date_fin']) ?></td>
                            <td><?= htmlspecialchars($trajet['recette']) ?> €</td>
                            <td><?= htmlspecialchars($trajet['carburant']) ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Aucun trajet tour trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Transport</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        h2 {
            color: #555;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .navigation a {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .navigation a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .section {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Système de Gestion de Transport</h1>
        
        <div class="navigation">
            <a href="/">Accueil</a>
            <a href="/vehicules">Véhicules</a>
            <a href="/chauffeurs">Chauffeurs</a>
            <a href="/trajets-fixes">Trajets Fixes</a>
            <a href="/trajets-tours">Trajets Tours</a>
            <a href="/histo-jour">Historique Journalier</a>
        </div>

        <!-- Vehicules Section -->
        <div class="section">
            <h2>Liste des Véhicules</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Véhicule</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehicules)): ?>
                        <?php foreach ($vehicules as $vehicule): ?>
                            <tr>
                                <td><?= htmlspecialchars($vehicule['id']) ?></td>
                                <td><?= htmlspecialchars($vehicule['nom']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">Aucun véhicule trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Chauffeurs Section -->
        <div class="section">
            <h2>Liste des Chauffeurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Chauffeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($chauffeurs)): ?>
                        <?php foreach ($chauffeurs as $chauffeur): ?>
                            <tr>
                                <td><?= htmlspecialchars($chauffeur['id']) ?></td>
                                <td><?= htmlspecialchars($chauffeur['nom']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">Aucun chauffeur trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Trajets Fixes Section -->
        <div class="section">
            <h2>Liste des Trajets Fixes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Trajet</th>
                        <th>Point de Départ</th>
                        <th>Point d'Arrivée</th>
                        <th>Kilomètres</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trajets_fixes)): ?>
                        <?php foreach ($trajets_fixes as $trajet): ?>
                            <tr>
                                <td><?= htmlspecialchars($trajet['id']) ?></td>
                                <td><?= htmlspecialchars($trajet['nom_trajet']) ?></td>
                                <td><?= htmlspecialchars($trajet['point_depart']) ?></td>
                                <td><?= htmlspecialchars($trajet['point_arriver']) ?></td>
                                <td><?= htmlspecialchars($trajet['kilometre']) ?> km</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucun trajet fixe trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
#!/usr/bin/env php
<?php
/**
 * TEST AUTOMATISÉ DES 3 MODES DE SIMULATION V3
 * =============================================
 * Ce script:
 * 1. Teste le mode CHRONOLOGIQUE
 * 2. Reset et teste le mode CROISSANT
 * 3. Reset et teste le mode PROPORTIONNEL
 * 4. Vérifie que les résultats correspondent aux attentes
 * 
 * Exécuter: php database/test_3_modes.php
 */

// Configuration DB
$dbConfig = [
    'host' => '127.0.0.1',
    'dbname' => 'takalo_db',
    'user' => 'root',
    'password' => ''
];

// Résultats attendus pour le RIZ (Don=100, Besoins=140)
$resultatsAttendus = [
    'chronologique' => [
        'Antananarivo' => 30,
        'Toamasina' => 50,
        'Mahajanga' => 20,  // Partiel (reste 0 après Toamasina)
        'Fianarantsoa' => 0
    ],
    'croissant' => [
        'Fianarantsoa' => 20,
        'Antananarivo' => 30,
        'Mahajanga' => 40,
        'Toamasina' => 10    // Partiel (reste 10 après Mahajanga)
    ],
    'proportionnel' => [
        'Antananarivo' => 21,   // floor(30/140 × 100)
        'Toamasina' => 36,      // floor(50/140 × 100) + 1 reste
        'Mahajanga' => 29,      // floor(40/140 × 100) + 1 reste
        'Fianarantsoa' => 14    // floor(20/140 × 100)
    ]
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║    TEST AUTOMATISÉ DES 3 MODES DE SIMULATION V3             ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";

    /**
     * Fonction de reset global
     */
    function resetGlobal($pdo) {
        $pdo->exec("DELETE FROM attribution");
        $pdo->exec("DELETE FROM achat");
        $pdo->exec("UPDATE don SET quantite = quantite_initiale");
        $pdo->exec("UPDATE besoin SET quantite_satisfaite = 0");
        $pdo->exec("UPDATE don_argent SET montant = montant_initial, montant_utilise = 0, statut = 'disponible'");
    }

    /**
     * Fonction simulation Chronologique
     */
    function simulationChronologique($pdo, $produit) {
        $dons = $pdo->query("SELECT * FROM don WHERE type_produit = '$produit' AND quantite > 0 ORDER BY date_saisie")->fetchAll(PDO::FETCH_ASSOC);
        $besoins = $pdo->query("
            SELECT b.*, v.nom as ville_nom
            FROM besoin b JOIN ville v ON b.ville_id = v.id
            WHERE b.produit = '$produit' AND b.quantite > b.quantite_satisfaite
            ORDER BY b.date_saisie
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $donRestant = array_sum(array_column($dons, 'quantite'));
        $attributions = [];
        
        foreach ($besoins as $b) {
            $besoinRestant = $b['quantite'] - $b['quantite_satisfaite'];
            $attribue = min($donRestant, $besoinRestant);
            $attributions[$b['ville_nom']] = $attribue;
            $donRestant -= $attribue;
        }
        
        return $attributions;
    }

    /**
     * Fonction simulation Croissant
     */
    function simulationCroissant($pdo, $produit) {
        $dons = $pdo->query("SELECT * FROM don WHERE type_produit = '$produit' AND quantite > 0")->fetchAll(PDO::FETCH_ASSOC);
        $besoins = $pdo->query("
            SELECT b.*, v.nom as ville_nom, (b.quantite - b.quantite_satisfaite) as qte_restante
            FROM besoin b JOIN ville v ON b.ville_id = v.id
            WHERE b.produit = '$produit' AND b.quantite > b.quantite_satisfaite
            ORDER BY (b.quantite - b.quantite_satisfaite) ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $donRestant = array_sum(array_column($dons, 'quantite'));
        $attributions = [];
        
        foreach ($besoins as $b) {
            $besoinRestant = $b['qte_restante'];
            $attribue = min($donRestant, $besoinRestant);
            $attributions[$b['ville_nom']] = $attribue;
            $donRestant -= $attribue;
        }
        
        return $attributions;
    }

    /**
     * Fonction simulation Proportionnel
     */
    function simulationProportionnel($pdo, $produit) {
        $dons = $pdo->query("SELECT * FROM don WHERE type_produit = '$produit' AND quantite > 0")->fetchAll(PDO::FETCH_ASSOC);
        $besoins = $pdo->query("
            SELECT b.*, v.nom as ville_nom, (b.quantite - b.quantite_satisfaite) as qte_restante
            FROM besoin b JOIN ville v ON b.ville_id = v.id
            WHERE b.produit = '$produit' AND b.quantite > b.quantite_satisfaite
            ORDER BY b.date_saisie
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $totalDon = array_sum(array_column($dons, 'quantite'));
        $totalBesoin = array_sum(array_column($besoins, 'qte_restante'));
        
        $attributions = [];
        $distribue = 0;
        
        // Phase 1: Distribution proportionnelle (floor)
        foreach ($besoins as $b) {
            $part = floor(($b['qte_restante'] / $totalBesoin) * $totalDon);
            $attributions[$b['ville_nom']] = $part;
            $distribue += $part;
        }
        
        // Phase 2: Redistribution du reste
        $reste = $totalDon - $distribue;
        if ($reste > 0) {
            // Trier par besoin restant décroissant
            arsort($attributions);
            $keys = array_keys($attributions);
            // Trouver les plus grands besoins restants
            $besoinsRestants = [];
            foreach ($besoins as $b) {
                $besoinsRestants[$b['ville_nom']] = $b['qte_restante'] - $attributions[$b['ville_nom']];
            }
            arsort($besoinsRestants);
            
            // Distribuer le reste
            $i = 0;
            foreach (array_keys($besoinsRestants) as $ville) {
                if ($i >= $reste) break;
                $attributions[$ville]++;
                $i++;
            }
        }
        
        return $attributions;
    }

    /**
     * Fonction de test d'un mode
     */
    function testerMode($pdo, $mode, $resultatsAttendus) {
        $modeLabels = [
            'chronologique' => '📅 MODE 1: CHRONOLOGIQUE',
            'croissant' => '📊 MODE 2: CROISSANT',
            'proportionnel' => '⚖️ MODE 3: PROPORTIONNEL'
        ];
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo $modeLabels[$mode] . "\n";
        echo str_repeat("=", 60) . "\n";
        
        // Reset avant test
        resetGlobal($pdo);
        echo "✓ Reset effectué\n";
        
        // Exécuter la simulation
        switch ($mode) {
            case 'chronologique':
                $resultat = simulationChronologique($pdo, 'Riz');
                break;
            case 'croissant':
                $resultat = simulationCroissant($pdo, 'Riz');
                break;
            case 'proportionnel':
                $resultat = simulationProportionnel($pdo, 'Riz');
                break;
        }
        
        echo "\nRésultats obtenus vs attendus (RIZ):\n";
        echo "┌────────────────┬─────────┬─────────┬────────┐\n";
        echo "│ Ville          │ Obtenu  │ Attendu │ Statut │\n";
        echo "├────────────────┼─────────┼─────────┼────────┤\n";
        
        $allOk = true;
        foreach ($resultatsAttendus[$mode] as $ville => $attendu) {
            $obtenu = $resultat[$ville] ?? 0;
            $ok = ($obtenu == $attendu);
            if (!$ok) $allOk = false;
            $statut = $ok ? "✅" : "❌";
            printf("│ %-14s │ %7d │ %7d │   %s   │\n", $ville, $obtenu, $attendu, $statut);
        }
        
        echo "└────────────────┴─────────┴─────────┴────────┘\n";
        
        $totalObtenu = array_sum($resultat);
        echo "\nTotal distribué: $totalObtenu / 100\n";
        
        return $allOk;
    }

    // ================================================================
    // EXÉCUTION DES TESTS
    // ================================================================
    
    $tests = ['chronologique', 'croissant', 'proportionnel'];
    $resultats = [];
    
    foreach ($tests as $mode) {
        $resultats[$mode] = testerMode($pdo, $mode, $resultatsAttendus);
    }
    
    // ================================================================
    // RÉSUMÉ DES TESTS
    // ================================================================
    echo "\n\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║                    RÉSUMÉ DES TESTS                          ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    
    $allPassed = true;
    foreach ($resultats as $mode => $passed) {
        $statut = $passed ? "✅ PASSÉ" : "❌ ÉCHEC";
        echo "   • Mode $mode: $statut\n";
        if (!$passed) $allPassed = false;
    }
    
    echo "\n";
    if ($allPassed) {
        echo "🎉 TOUS LES TESTS SONT PASSÉS!\n";
    } else {
        echo "⚠️ CERTAINS TESTS ONT ÉCHOUÉ. Vérifiez l'implémentation.\n";
    }
    
    // Reset final
    echo "\n📋 Reset final effectué.\n";
    resetGlobal($pdo);
    
    echo "\n✅ Script de test terminé!\n";
    echo "   → Testez maintenant via l'interface: http://localhost:8000/simulation\n\n";

} catch (PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

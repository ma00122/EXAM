<?php
/**
 * Script de Test V3 - Vérification des 3 modes de simulation
 * 
 * Structure réelle de la BD:
 * - don: id, type_produit, quantite, quantite_initiale
 * - besoin: id, ville_id, produit, quantite, quantite_initiale
 * - attribution: don_id, besoin_id, quantite_attribuee, mode_simulation
 */

// Configuration directe de la base de données
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'takalo_db',
    'user' => 'root',
    'password' => '',
    'socket' => '/opt/lampp/var/mysql/mysql.sock'
];

// Connexion à la base de données
try {
    $dsn = "mysql:unix_socket={$dbConfig['socket']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Connexion à la base de données réussie\n\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage() . "\n");
}

// ============================================
// FONCTIONS UTILITAIRES
// ============================================

function resetData($pdo) {
    echo "🔄 Reset des données...\n";
    $pdo->exec("DELETE FROM attribution");
    $pdo->exec("UPDATE don SET quantite = quantite_initiale WHERE quantite_initiale IS NOT NULL");
    $pdo->exec("UPDATE besoin SET quantite = quantite_initiale, quantite_satisfaite = 0 WHERE quantite_initiale IS NOT NULL");
    echo "✅ Reset effectué\n\n";
}

function getDons($pdo) {
    return $pdo->query("SELECT * FROM don ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
}

function getBesoins($pdo) {
    return $pdo->query("
        SELECT b.*, v.nom as ville_nom 
        FROM besoin b 
        JOIN ville v ON b.ville_id = v.id 
        ORDER BY b.id
    ")->fetchAll(PDO::FETCH_ASSOC);
}

function getAttributions($pdo) {
    return $pdo->query("
        SELECT a.*, b.produit, v.nom as ville_nom, d.type_produit as don_produit
        FROM attribution a 
        JOIN besoin b ON a.besoin_id = b.id
        JOIN ville v ON b.ville_id = v.id
        JOIN don d ON a.don_id = d.id
        ORDER BY a.id
    ")->fetchAll(PDO::FETCH_ASSOC);
}

function afficherEtatInitial($pdo) {
    echo "📊 ÉTAT INITIAL DES DONNÉES\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "🎁 DONS DISPONIBLES:\n";
    foreach (getDons($pdo) as $don) {
        echo "   - {$don['type_produit']}: {$don['quantite']} unités\n";
    }
    
    echo "\n📋 BESOINS PAR VILLE ET PRODUIT:\n";
    $besoins = getBesoins($pdo);
    $parProduit = [];
    foreach ($besoins as $b) {
        $parProduit[$b['produit']][] = $b;
    }
    foreach ($parProduit as $produit => $bs) {
        $total = array_sum(array_column($bs, 'quantite'));
        echo "   $produit (Total: $total):\n";
        foreach ($bs as $b) {
            echo "      - {$b['ville_nom']}: {$b['quantite']}\n";
        }
    }
    echo "\n";
}

function afficherResultats($pdo, $mode) {
    echo "\n📈 RÉSULTATS MODE: " . strtoupper($mode) . "\n";
    echo str_repeat("-", 60) . "\n";
    
    $attributions = getAttributions($pdo);
    
    if (empty($attributions)) {
        echo "   ⚠️ Aucune attribution créée\n";
        return;
    }
    
    $parProduit = [];
    foreach ($attributions as $a) {
        $parProduit[$a['produit']][] = $a;
    }
    
    foreach ($parProduit as $produit => $attrs) {
        echo "\n   🏷️ $produit:\n";
        $total = 0;
        foreach ($attrs as $a) {
            echo "      → {$a['ville_nom']}: {$a['quantite_attribuee']} unités\n";
            $total += $a['quantite_attribuee'];
        }
        echo "      Total distribué: $total\n";
    }
    
    echo "\n   📉 BESOINS RESTANTS:\n";
    $besoins = getBesoins($pdo);
    $hasRestants = false;
    foreach ($besoins as $b) {
        if ($b['quantite'] > 0) {
            echo "      - {$b['ville_nom']} / {$b['produit']}: {$b['quantite']} restants\n";
            $hasRestants = true;
        }
    }
    if (!$hasRestants) {
        echo "      ✅ Tous les besoins sont satisfaits!\n";
    }
}

// ============================================
// SIMULATION CHRONOLOGIQUE
// ============================================
function simulationChronologique($pdo) {
    echo "🕐 EXÉCUTION MODE CHRONOLOGIQUE\n";
    echo "   (Priorité par ordre de création des besoins - ID croissant)\n";
    echo str_repeat("-", 60) . "\n";
    
    $dons = $pdo->query("SELECT * FROM don WHERE quantite > 0 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dons as $don) {
        $quantiteDispo = $don['quantite'];
        echo "\n   Don: {$don['type_produit']} (Qté: $quantiteDispo)\n";
        
        // Besoins triés par ID (ordre chronologique de création)
        $stmtBesoins = $pdo->prepare("
            SELECT b.*, v.nom as ville_nom 
            FROM besoin b 
            JOIN ville v ON b.ville_id = v.id 
            WHERE b.produit = ? AND b.quantite > 0 
            ORDER BY b.id ASC
        ");
        $stmtBesoins->execute([$don['type_produit']]);
        $besoins = $stmtBesoins->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($besoins as $besoin) {
            if ($quantiteDispo <= 0) break;
            
            $quantiteAttribuee = min($quantiteDispo, $besoin['quantite']);
            
            $pdo->prepare("
                INSERT INTO attribution (don_id, besoin_id, quantite_attribuee, mode_simulation)
                VALUES (?, ?, ?, 'chronologique')
            ")->execute([$don['id'], $besoin['id'], $quantiteAttribuee]);
            
            $pdo->prepare("UPDATE besoin SET quantite = quantite - ?, quantite_satisfaite = quantite_satisfaite + ? WHERE id = ?")
                ->execute([$quantiteAttribuee, $quantiteAttribuee, $besoin['id']]);
            
            $quantiteDispo -= $quantiteAttribuee;
            
            echo "      → {$besoin['ville_nom']}: $quantiteAttribuee / {$besoin['quantite']}\n";
        }
        
        $pdo->prepare("UPDATE don SET quantite = ? WHERE id = ?")->execute([$quantiteDispo, $don['id']]);
    }
}

// ============================================
// SIMULATION CROISSANT
// ============================================
function simulationCroissant($pdo) {
    echo "📈 EXÉCUTION MODE CROISSANT\n";
    echo "   (Priorité aux plus petits besoins d'abord)\n";
    echo str_repeat("-", 60) . "\n";
    
    $dons = $pdo->query("SELECT * FROM don WHERE quantite > 0 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dons as $don) {
        $quantiteDispo = $don['quantite'];
        echo "\n   Don: {$don['type_produit']} (Qté: $quantiteDispo)\n";
        
        // Besoins triés par quantité croissante
        $stmtBesoins = $pdo->prepare("
            SELECT b.*, v.nom as ville_nom 
            FROM besoin b 
            JOIN ville v ON b.ville_id = v.id 
            WHERE b.produit = ? AND b.quantite > 0 
            ORDER BY b.quantite_initiale ASC, b.id ASC
        ");
        $stmtBesoins->execute([$don['type_produit']]);
        $besoins = $stmtBesoins->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($besoins as $besoin) {
            if ($quantiteDispo <= 0) break;
            
            $quantiteAttribuee = min($quantiteDispo, $besoin['quantite']);
            
            $pdo->prepare("
                INSERT INTO attribution (don_id, besoin_id, quantite_attribuee, mode_simulation)
                VALUES (?, ?, ?, 'croissant')
            ")->execute([$don['id'], $besoin['id'], $quantiteAttribuee]);
            
            $pdo->prepare("UPDATE besoin SET quantite = quantite - ?, quantite_satisfaite = quantite_satisfaite + ? WHERE id = ?")
                ->execute([$quantiteAttribuee, $quantiteAttribuee, $besoin['id']]);
            
            $quantiteDispo -= $quantiteAttribuee;
            
            echo "      → {$besoin['ville_nom']}: $quantiteAttribuee (besoin: {$besoin['quantite_initiale']})\n";
        }
        
        $pdo->prepare("UPDATE don SET quantite = ? WHERE id = ?")->execute([$quantiteDispo, $don['id']]);
    }
}

// ============================================
// SIMULATION PROPORTIONNEL
// ============================================
function simulationProportionnel($pdo) {
    echo "⚖️ EXÉCUTION MODE PROPORTIONNEL\n";
    echo "   (Distribution selon le ratio des besoins)\n";
    echo str_repeat("-", 60) . "\n";
    
    $dons = $pdo->query("SELECT * FROM don WHERE quantite > 0 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dons as $don) {
        $quantiteDispo = $don['quantite'];
        echo "\n   Don: {$don['type_produit']} (Qté: $quantiteDispo)\n";
        
        $stmtBesoins = $pdo->prepare("
            SELECT b.*, v.nom as ville_nom 
            FROM besoin b 
            JOIN ville v ON b.ville_id = v.id 
            WHERE b.produit = ? AND b.quantite > 0 
            ORDER BY b.id ASC
        ");
        $stmtBesoins->execute([$don['type_produit']]);
        $besoins = $stmtBesoins->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($besoins)) continue;
        
        $totalBesoins = array_sum(array_column($besoins, 'quantite'));
        echo "      Total besoins: $totalBesoins\n";
        
        if ($totalBesoins == 0) continue;
        
        // Calculer la distribution proportionnelle
        $distributions = [];
        $totalDistribue = 0;
        
        foreach ($besoins as $idx => $besoin) {
            $ratio = $besoin['quantite'] / $totalBesoins;
            $quantiteCalc = floor($quantiteDispo * $ratio);
            $quantiteAttribuee = min($quantiteCalc, $besoin['quantite']);
            
            $distributions[$idx] = [
                'besoin' => $besoin,
                'quantite' => $quantiteAttribuee,
                'ratio' => $ratio,
                'besoin_original' => $besoin['quantite']
            ];
            $totalDistribue += $quantiteAttribuee;
        }
        
        // Distribuer le reste (arrondis) - par besoin décroissant mais sans perdre l'indexation
        $reste = min($quantiteDispo, $totalBesoins) - $totalDistribue;
        if ($reste > 0) {
            // Créer un tableau d'indices triés par besoin décroissant
            $indices = array_keys($distributions);
            usort($indices, fn($a, $b) => $distributions[$b]['besoin_original'] - $distributions[$a]['besoin_original']);
            
            foreach ($indices as $idx) {
                if ($reste <= 0) break;
                $peutAjouter = min($reste, $distributions[$idx]['besoin']['quantite'] - $distributions[$idx]['quantite']);
                $distributions[$idx]['quantite'] += $peutAjouter;
                $reste -= $peutAjouter;
            }
        }
        
        // Appliquer les distributions dans l'ordre original
        foreach ($distributions as $dist) {
            if ($dist['quantite'] <= 0) continue;
            
            $besoin = $dist['besoin'];
            $quantiteAttribuee = $dist['quantite'];
            
            $pdo->prepare("
                INSERT INTO attribution (don_id, besoin_id, quantite_attribuee, mode_simulation)
                VALUES (?, ?, ?, 'proportionnel')
            ")->execute([$don['id'], $besoin['id'], $quantiteAttribuee]);
            
            $pdo->prepare("UPDATE besoin SET quantite = quantite - ?, quantite_satisfaite = quantite_satisfaite + ? WHERE id = ?")
                ->execute([$quantiteAttribuee, $quantiteAttribuee, $besoin['id']]);
            
            $ratio = round($dist['ratio'] * 100, 1);
            echo "      → {$besoin['ville_nom']}: $quantiteAttribuee ({$ratio}%)\n";
        }
        
        $quantiteRestante = max(0, $quantiteDispo - min($quantiteDispo, $totalBesoins));
        $pdo->prepare("UPDATE don SET quantite = ? WHERE id = ?")->execute([$quantiteRestante, $don['id']]);
    }
}

// ============================================
// VÉRIFICATION DES RÉSULTATS
// ============================================
function verifierResultats($pdo, $mode, $attendus) {
    echo "\n🔍 VÉRIFICATION MODE: " . strtoupper($mode) . "\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $pdo->query("
        SELECT v.nom as ville, b.produit, SUM(a.quantite_attribuee) as total
        FROM attribution a
        JOIN besoin b ON a.besoin_id = b.id
        JOIN ville v ON b.ville_id = v.id
        GROUP BY v.nom, b.produit
        ORDER BY b.produit, v.nom
    ");
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultatMap = [];
    foreach ($resultats as $r) {
        $key = $r['produit'] . '_' . $r['ville'];
        $resultatMap[$key] = (int)$r['total'];
    }
    
    $erreurs = 0;
    foreach ($attendus as $produit => $villes) {
        echo "\n   📦 $produit:\n";
        foreach ($villes as $ville => $quantiteAttendue) {
            $key = $produit . '_' . $ville;
            $quantiteObtenue = $resultatMap[$key] ?? 0;
            
            // Tolérance de 1 pour les arrondis
            $diff = abs($quantiteObtenue - $quantiteAttendue);
            if ($diff <= 1) {
                echo "      ✅ $ville: $quantiteObtenue (attendu: $quantiteAttendue)\n";
            } else {
                echo "      ❌ $ville: $quantiteObtenue (attendu: $quantiteAttendue)\n";
                $erreurs++;
            }
        }
    }
    
    if ($erreurs == 0) {
        echo "\n   ✅ TOUS LES RÉSULTATS SONT CORRECTS!\n";
    } else {
        echo "\n   ⚠️ $erreurs erreur(s) détectée(s)\n";
    }
    
    return $erreurs == 0;
}

// ============================================
// EXÉCUTION DES TESTS
// ============================================

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     TEST V3 - SIMULATION BNGRC - 3 MODES                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

/*
 * Données de test pour RIZ:
 * - Don RIZ: 100 unités
 * - Besoins: Antananarivo=30 (id=1), Toamasina=50 (id=2), Mahajanga=40 (id=3), Fianarantsoa=20 (id=4)
 * - Total besoins: 140
 * - Déficit: 40
 */

// Résultats attendus CHRONOLOGIQUE (ordre des IDs = ordre de création)
// Ordre: Tana(30) -> Toamasina(50) -> Mahajanga(40) -> Fianara(20)
// Stock: 100 -> 70 -> 20 -> 0 (Mahajanga reçoit 20, Fianara 0)
$attendusChronologique = [
    'Riz' => [
        'Antananarivo' => 30,
        'Toamasina' => 50,
        'Mahajanga' => 20,
        'Fianarantsoa' => 0
    ]
];

// Résultats attendus CROISSANT (plus petit besoin en premier)
// Ordre: Fianara(20) -> Tana(30) -> Mahajanga(40) -> Toamasina(50)
// Stock: 100 -> 80 -> 50 -> 10 -> 0 (Toamasina reçoit 10)
$attendusCroissant = [
    'Riz' => [
        'Fianarantsoa' => 20,
        'Antananarivo' => 30,
        'Mahajanga' => 40,
        'Toamasina' => 10
    ]
];

// Résultats attendus PROPORTIONNEL (ratio des besoins)
// Tana: 30/140 * 100 = 21.43 -> 21
// Toamasina: 50/140 * 100 = 35.71 -> 36
// Mahajanga: 40/140 * 100 = 28.57 -> 29
// Fianara: 20/140 * 100 = 14.29 -> 14
$attendusProportionnel = [
    'Riz' => [
        'Antananarivo' => 21,
        'Toamasina' => 36,
        'Mahajanga' => 29,
        'Fianarantsoa' => 14
    ]
];

// ============================================
// TEST 1: MODE CHRONOLOGIQUE
// ============================================

echo "\n" . str_repeat("═", 60) . "\n";
echo "TEST 1: MODE CHRONOLOGIQUE\n";
echo str_repeat("═", 60) . "\n";

resetData($pdo);
afficherEtatInitial($pdo);
simulationChronologique($pdo);
afficherResultats($pdo, 'chronologique');
$test1 = verifierResultats($pdo, 'chronologique', $attendusChronologique);

// ============================================
// TEST 2: MODE CROISSANT
// ============================================

echo "\n" . str_repeat("═", 60) . "\n";
echo "TEST 2: MODE CROISSANT\n";
echo str_repeat("═", 60) . "\n";

resetData($pdo);
simulationCroissant($pdo);
afficherResultats($pdo, 'croissant');
$test2 = verifierResultats($pdo, 'croissant', $attendusCroissant);

// ============================================
// TEST 3: MODE PROPORTIONNEL
// ============================================

echo "\n" . str_repeat("═", 60) . "\n";
echo "TEST 3: MODE PROPORTIONNEL\n";
echo str_repeat("═", 60) . "\n";

resetData($pdo);
simulationProportionnel($pdo);
afficherResultats($pdo, 'proportionnel');
$test3 = verifierResultats($pdo, 'proportionnel', $attendusProportionnel);

// ============================================
// RÉSUMÉ FINAL
// ============================================

echo "\n" . str_repeat("═", 60) . "\n";
echo "RÉSUMÉ DES TESTS\n";
echo str_repeat("═", 60) . "\n\n";

echo "   Mode Chronologique: " . ($test1 ? "✅ PASS" : "❌ FAIL") . "\n";
echo "   Mode Croissant:     " . ($test2 ? "✅ PASS" : "❌ FAIL") . "\n";
echo "   Mode Proportionnel: " . ($test3 ? "✅ PASS" : "❌ FAIL") . "\n";

$allPass = $test1 && $test2 && $test3;
echo "\n" . ($allPass ? "🎉 TOUS LES TESTS ONT RÉUSSI!" : "⚠️ CERTAINS TESTS ONT ÉCHOUÉ") . "\n";

// Reset final
resetData($pdo);
echo "✅ Données réinitialisées pour utilisation manuelle\n\n";

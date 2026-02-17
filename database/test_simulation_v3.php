#!/usr/bin/env php
<?php
/**
 * SCRIPT DE TEST SIMULATION V3
 * ============================
 * Ce script:
 * 1. Réinitialise toutes les données à leur état initial
 * 2. Affiche les données de test
 * 3. Montre les résultats attendus pour chaque mode
 * 
 * Exécuter: php database/test_simulation_v3.php
 */

// Configuration DB
$dbConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'dbname' => 'takalo_db',
    'user' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║          SCRIPT DE TEST SIMULATION V3 - BNGRC                ║\n";
    echo "║     ETU004546 (Mahery) | ETU004172 (Bolton) | ETU004141      ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";

    // ================================================================
    // ÉTAPE 1: RESET GLOBAL - Réinitialiser toutes les données
    // ================================================================
    echo "🔄 ÉTAPE 1: RESET GLOBAL\n";
    echo str_repeat("-", 60) . "\n";
    
    $pdo->beginTransaction();
    
    // Supprimer toutes les attributions
    $deleted = $pdo->exec("DELETE FROM attribution");
    echo "   ✓ Attributions supprimées: $deleted\n";
    
    // Supprimer tous les achats
    $deleted = $pdo->exec("DELETE FROM achat");
    echo "   ✓ Achats supprimés: $deleted\n";
    
    // Restaurer les quantités des dons
    $pdo->exec("UPDATE don SET quantite = quantite_initiale");
    echo "   ✓ Quantités des dons restaurées\n";
    
    // Restaurer les quantités satisfaites des besoins
    $pdo->exec("UPDATE besoin SET quantite_satisfaite = 0");
    echo "   ✓ Quantités satisfaites remises à 0\n";
    
    // Restaurer les dons argent
    $pdo->exec("UPDATE don_argent SET montant = montant_initial, montant_utilise = 0, statut = 'disponible'");
    echo "   ✓ Dons argent restaurés\n";
    
    $pdo->commit();
    echo "\n   ✅ RESET GLOBAL TERMINÉ!\n\n";

    // ================================================================
    // ÉTAPE 2: AFFICHER LES DONNÉES DE TEST
    // ================================================================
    echo "📊 ÉTAPE 2: DONNÉES DE TEST\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Villes
    echo "🏙️  VILLES:\n";
    $villes = $pdo->query("SELECT id, nom, region FROM ville ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($villes as $v) {
        echo "   ID {$v['id']}: {$v['nom']} ({$v['region']})\n";
    }
    
    // Dons
    echo "\n🎁 DONS EN NATURE:\n";
    echo "   ┌────────────┬──────────┬────────────────┐\n";
    echo "   │ Produit    │ Quantité │ Date           │\n";
    echo "   ├────────────┼──────────┼────────────────┤\n";
    $dons = $pdo->query("SELECT * FROM don ORDER BY date_saisie")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dons as $d) {
        printf("   │ %-10s │ %8d │ %14s │\n", $d['type_produit'], $d['quantite'], $d['date_saisie']);
    }
    echo "   └────────────┴──────────┴────────────────┘\n";
    
    // Besoins par produit
    echo "\n📦 BESOINS PAR PRODUIT:\n";
    $produits = ['Riz', 'Huile', 'Tôle', 'Ciment'];
    
    foreach ($produits as $produit) {
        $besoins = $pdo->query("
            SELECT b.id, v.nom as ville, b.quantite, b.date_saisie 
            FROM besoin b 
            JOIN ville v ON b.ville_id = v.id 
            WHERE b.produit = '$produit' 
            ORDER BY b.date_saisie
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $totalBesoin = array_sum(array_column($besoins, 'quantite'));
        $don = $pdo->query("SELECT quantite FROM don WHERE type_produit = '$produit'")->fetchColumn();
        
        echo "\n   ▶ $produit: Don = $don, Total Besoins = $totalBesoin";
        echo ($don >= $totalBesoin) ? " ✅ Suffisant" : " ⚠️ Manque " . ($totalBesoin - $don);
        echo "\n   ┌────────────────┬──────────┬────────────┐\n";
        echo "   │ Ville          │ Quantité │ Date       │\n";
        echo "   ├────────────────┼──────────┼────────────┤\n";
        foreach ($besoins as $b) {
            printf("   │ %-14s │ %8d │ %10s │\n", $b['ville'], $b['quantite'], $b['date_saisie']);
        }
        echo "   └────────────────┴──────────┴────────────┘\n";
    }

    // ================================================================
    // ÉTAPE 3: RÉSULTATS ATTENDUS POUR CHAQUE MODE
    // ================================================================
    echo "\n\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║         RÉSULTATS ATTENDUS - EXEMPLE RIZ                     ║\n";
    echo "║         Don = 100, Besoins = 30+50+40+20 = 140               ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    
    // MODE 1: CHRONOLOGIQUE
    echo "📅 MODE 1: CHRONOLOGIQUE (FIFO - par date)\n";
    echo str_repeat("-", 60) . "\n";
    echo "   Ordre de traitement: date_saisie ASC\n";
    echo "   1. Antananarivo (02/01): besoin=30, don=100 → attribué=30  (reste=70)\n";
    echo "   2. Toamasina    (02/02): besoin=50, don=70  → attribué=50  (reste=20)\n";
    echo "   3. Mahajanga    (02/03): besoin=40, don=20  → attribué=20  (reste=0) ⚠️\n";
    echo "   4. Fianarantsoa (02/04): besoin=20, don=0   → attribué=0   ❌\n";
    echo "\n   RÉSULTAT:\n";
    echo "   ┌────────────────┬────────┬──────────┬────────┐\n";
    echo "   │ Ville          │ Besoin │ Attribué │ Statut │\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ Antananarivo   │     30 │       30 │ ✅ 100%│\n";
    echo "   │ Toamasina      │     50 │       50 │ ✅ 100%│\n";
    echo "   │ Mahajanga      │     40 │       20 │ ⚠️  50%│\n";
    echo "   │ Fianarantsoa   │     20 │        0 │ ❌   0%│\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ TOTAL          │    140 │      100 │   71.4%│\n";
    echo "   └────────────────┴────────┴──────────┴────────┘\n";
    echo "   Villes complètes: 2/4\n\n";

    // MODE 2: CROISSANT
    echo "📊 MODE 2: CROISSANT (petits besoins d'abord)\n";
    echo str_repeat("-", 60) . "\n";
    echo "   Ordre de traitement: quantité ASC\n";
    echo "   1. Fianarantsoa: besoin=20, don=100 → attribué=20  (reste=80)\n";
    echo "   2. Antananarivo: besoin=30, don=80  → attribué=30  (reste=50)\n";
    echo "   3. Mahajanga:    besoin=40, don=50  → attribué=40  (reste=10)\n";
    echo "   4. Toamasina:    besoin=50, don=10  → attribué=10  (reste=0) ⚠️\n";
    echo "\n   RÉSULTAT:\n";
    echo "   ┌────────────────┬────────┬──────────┬────────┐\n";
    echo "   │ Ville          │ Besoin │ Attribué │ Statut │\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ Fianarantsoa   │     20 │       20 │ ✅ 100%│\n";
    echo "   │ Antananarivo   │     30 │       30 │ ✅ 100%│\n";
    echo "   │ Mahajanga      │     40 │       40 │ ✅ 100%│\n";
    echo "   │ Toamasina      │     50 │       10 │ ⚠️  20%│\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ TOTAL          │    140 │      100 │   71.4%│\n";
    echo "   └────────────────┴────────┴──────────┴────────┘\n";
    echo "   Villes complètes: 3/4 ⭐ (meilleur pour satisfaire plus de villes)\n\n";

    // MODE 3: PROPORTIONNEL
    echo "⚖️  MODE 3: PROPORTIONNEL (répartition équitable)\n";
    echo str_repeat("-", 60) . "\n";
    echo "   Formule: part = floor(besoin_ville / total_besoins × don_total)\n";
    echo "   Total besoins = 140, Don = 100\n\n";
    echo "   Calcul des parts (arrondi inférieur):\n";
    echo "   • Antananarivo: 30/140 × 100 = 21.43 → floor = 21\n";
    echo "   • Toamasina:    50/140 × 100 = 35.71 → floor = 35\n";
    echo "   • Mahajanga:    40/140 × 100 = 28.57 → floor = 28\n";
    echo "   • Fianarantsoa: 20/140 × 100 = 14.29 → floor = 14\n";
    echo "   Total distribué: 21+35+28+14 = 98, Reste: 2\n\n";
    echo "   Redistribution du reste (aux plus grands besoins restants):\n";
    echo "   • Toamasina: 50-35=15 restant → +1 = 36\n";
    echo "   • Mahajanga: 40-28=12 restant → +1 = 29\n";
    echo "\n   RÉSULTAT:\n";
    echo "   ┌────────────────┬────────┬──────────┬────────┐\n";
    echo "   │ Ville          │ Besoin │ Attribué │ Statut │\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ Antananarivo   │     30 │       21 │ ⚠️  70%│\n";
    echo "   │ Toamasina      │     50 │       36 │ ⚠️  72%│\n";
    echo "   │ Mahajanga      │     40 │       29 │ ⚠️  73%│\n";
    echo "   │ Fianarantsoa   │     20 │       14 │ ⚠️  70%│\n";
    echo "   ├────────────────┼────────┼──────────┼────────┤\n";
    echo "   │ TOTAL          │    140 │      100 │   71.4%│\n";
    echo "   └────────────────┴────────┴──────────┴────────┘\n";
    echo "   Villes complètes: 0/4 (mais distribution équitable ~70% chacune)\n\n";

    // ================================================================
    // COMPARAISON DES 3 MODES
    // ================================================================
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║              COMPARAISON DES 3 MODES                         ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    echo "   ┌─────────────────┬──────────────┬──────────────┬──────────────┐\n";
    echo "   │ Ville           │ Chronologique│   Croissant  │ Proportionnel│\n";
    echo "   ├─────────────────┼──────────────┼──────────────┼──────────────┤\n";
    echo "   │ Antananarivo    │    30 ✅     │    30 ✅     │    21 ⚠️     │\n";
    echo "   │ Toamasina       │    50 ✅     │    10 ⚠️     │    36 ⚠️     │\n";
    echo "   │ Mahajanga       │    20 ⚠️     │    40 ✅     │    29 ⚠️     │\n";
    echo "   │ Fianarantsoa    │     0 ❌     │    20 ✅     │    14 ⚠️     │\n";
    echo "   ├─────────────────┼──────────────┼──────────────┼──────────────┤\n";
    echo "   │ Villes 100%     │     2/4      │     3/4 ⭐   │     0/4      │\n";
    echo "   │ Équité          │    Faible    │    Moyenne   │   Haute ⭐   │\n";
    echo "   └─────────────────┴──────────────┴──────────────┴──────────────┘\n\n";

    // ================================================================
    // VÉRIFICATION DE L'ÉTAT ACTUEL
    // ================================================================
    echo "📋 ÉTAT ACTUEL APRÈS RESET:\n";
    echo str_repeat("-", 60) . "\n";
    
    $nbAttr = $pdo->query("SELECT COUNT(*) FROM attribution")->fetchColumn();
    $nbAchats = $pdo->query("SELECT COUNT(*) FROM achat")->fetchColumn();
    $totalDons = $pdo->query("SELECT SUM(quantite) FROM don")->fetchColumn();
    $totalBesoins = $pdo->query("SELECT SUM(quantite) FROM besoin")->fetchColumn();
    
    echo "   • Attributions: $nbAttr\n";
    echo "   • Achats: $nbAchats\n";
    echo "   • Total dons disponibles: $totalDons\n";
    echo "   • Total besoins: $totalBesoins\n";
    
    echo "\n✅ BASE DE DONNÉES PRÊTE POUR TESTER LA SIMULATION!\n";
    echo "   → Allez sur http://localhost:8000/simulation\n";
    echo "   → Sélectionnez un mode et cliquez sur 'Prévisualiser' ou 'Exécuter'\n\n";

} catch (PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

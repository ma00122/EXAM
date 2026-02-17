<?php
namespace app\controllers;

use flight\Engine;
use app\models\Don;
use app\models\Besoin;
use app\models\Attribution;
use app\models\Achat;
use app\models\Configuration;
use Flight;

/**
 * Controller SimulationController V3 - Simulation Multi-Mode + Reset Global
 * Projet BNGRC - Modules Mahery + Bolton + Sedra
 * 
 * VERSION 3.0 - Trois modes de simulation :
 * - Mode 1: Chronologique (FIFO par date_saisie)
 * - Mode 2: Croissant (priorité aux plus petits besoins)
 * - Mode 3: Proportionnel (selon le poids des besoins)
 * 
 * FONCTIONNALITÉS V3 :
 * - Simulation preview pour chaque mode
 * - Validation avec enregistrement
 * - Reset global pour tester plusieurs simulations
 * - Achat avec dons argent + frais configurable
 */
class SimulationController
{
    protected Engine $app;
    protected Don $donModel;
    protected Attribution $attributionModel;
    protected Achat $achatModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->donModel = new Don(Flight::db());
        $this->attributionModel = new Attribution(Flight::db());
        $this->achatModel = new Achat(Flight::db());
    }

    /* ===================== PAGE SIMULATION V3 ===================== */

    /**
     * Afficher la page de simulation V3
     * GET /simulation
     */
    public function index(): void
    {
        $db = Flight::db();
        
        // Récupérer les dons disponibles
        $dons = $this->donModel->getAllDons();
        $attributions = $this->attributionModel->getAllAttributions();
        
        // Récupérer les besoins pour la simulation
        $besoins = Besoin::getAllBesoinsWithDetails();

        // Grouper besoins par produit pour l'affichage
        $besoinsParProduit = [];
        foreach ($besoins as $besoin) {
            $produit = $besoin['produit'];
            if (!isset($besoinsParProduit[$produit])) {
                $besoinsParProduit[$produit] = [];
            }
            $besoinsParProduit[$produit][] = $besoin;
        }
        
        // Grouper dons par produit
        $donsParProduit = [];
        foreach ($dons as $don) {
            $produit = $don['type_produit'];
            if (!isset($donsParProduit[$produit])) {
                $donsParProduit[$produit] = [];
            }
            $donsParProduit[$produit][] = $don;
        }

        // Calculer les statistiques
        $stats = $this->calculateStats();
        $stats['nombre_besoins'] = count($besoins);
        $stats['total_besoins'] = array_sum(array_column($besoins, 'quantite'));

        // Dons argent disponibles
        $donsArgent = $db->fetchAll("
            SELECT * FROM don_argent 
            WHERE statut != 'epuise'
            ORDER BY date_saisie ASC
        ");
        
        // Frais configuré DDD
        $fraisPourcentage = $this->getFraisPourcentage();
        
        // Mode sélectionné (depuis URL ou défaut) SSS
        $selectedMode = $_GET['mode'] ?? 'chronologique';

        $this->app->render('simulation/index', [
            'pageTitle' => 'Simulation V3 - Multi-Mode',
            'dons' => $dons,
            'besoins' => $besoins,
            'besoinsParProduit' => $besoinsParProduit,
            'donsParProduit' => $donsParProduit,
            'attributions' => $attributions,
            'stats' => $stats,
            'donsArgent' => $donsArgent ?? [],
            'fraisPourcentage' => $fraisPourcentage,
            'selectedMode' => $selectedMode,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== SIMULATION V3 - TROIS MODES ===================== */

    /**
     * Preview de la simulation selon le mode choisi
     * POST /simulation/preview
     * Affiche une page HTML avec les résultats prévisionnels
     */
    public function previewMode(): void
    {
        $mode = Flight::request()->data->mode ?? 'chronologique';
        
        // Valider le mode
        if (!in_array($mode, ['chronologique', 'croissant', 'proportionnel'])) {
            $mode = 'chronologique';
        }
        
        $db = Flight::db();
        
        // Récupérer tous les produits avec dons disponibles
        $produits = $db->fetchAll("SELECT DISTINCT type_produit FROM don WHERE quantite > 0");
        
        $resultatsGlobaux = [];
        $statsGlobales = [
            'total_distribue' => 0,
            'total_besoins' => 0,
            'total_dons' => 0,
            'villes_satisfaites' => 0
        ];
        
        foreach ($produits as $p) {
            $produit = $p['type_produit'];
            
            $dons = $db->fetchAll("
                SELECT * FROM don WHERE type_produit = ? AND quantite > 0 ORDER BY date_saisie ASC
            ", [$produit]);
            
            $besoins = $db->fetchAll("
                SELECT b.*, v.nom as ville_nom, v.region,
                       (b.quantite - b.quantite_satisfaite) as quantite_restante
                FROM besoin b
                JOIN ville v ON b.ville_id = v.id
                WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
                ORDER BY b.date_saisie ASC
            ", [$produit]);
            
            if (!empty($dons) && !empty($besoins)) {
                $resultat = $this->calculerSimulation($dons, $besoins, $mode);
                $resultatsGlobaux[$produit] = $resultat;
                
                $statsGlobales['total_distribue'] += $resultat['statistiques']['total_distribue'];
                $statsGlobales['total_besoins'] += $resultat['statistiques']['total_besoins'];
                $statsGlobales['total_dons'] += $resultat['statistiques']['total_don'];
                $statsGlobales['villes_satisfaites'] += $resultat['statistiques']['villes_completes'];
            }
        }
        
        $statsGlobales['taux_satisfaction'] = $statsGlobales['total_besoins'] > 0 
            ? round(($statsGlobales['total_distribue'] / $statsGlobales['total_besoins']) * 100, 2) 
            : 0;
        
        $modeLabels = [
            'chronologique' => 'Chronologique (FIFO)',
            'croissant' => 'Croissant (petits d\'abord)',
            'proportionnel' => 'Proportionnel (équitable)'
        ];
        
        // Afficher la page de prévisualisation
        $this->app->render('simulation/preview', [
            'pageTitle' => 'Prévisualisation - Mode ' . $modeLabels[$mode],
            'mode' => $mode,
            'modeLabel' => $modeLabels[$mode],
            'resultats' => $resultatsGlobaux,
            'statsGlobales' => $statsGlobales
        ]);
    }
    
    /**
     * Preview pour un produit spécifique
     */
    private function previewProduit(string $produit, string $mode): array
    {
        $db = Flight::db();
        
        // Récupérer les dons pour ce produit
        $dons = $db->fetchAll("
            SELECT * FROM don 
            WHERE type_produit = ? AND quantite > 0
            ORDER BY date_saisie ASC
        ", [$produit]);
        
        // Récupérer les besoins pour ce produit
        $besoins = $db->fetchAll("
            SELECT b.*, v.nom as ville_nom, v.region,
                   (b.quantite - b.quantite_satisfaite) as quantite_restante
            FROM besoin b
            JOIN ville v ON b.ville_id = v.id
            WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
            ORDER BY b.date_saisie ASC
        ", [$produit]);
        
        if (empty($dons) || empty($besoins)) {
            return [
                'success' => false,
                'message' => 'Aucun don ou besoin disponible pour ce produit'
            ];
        }
        
        $resultat = $this->calculerSimulation($dons, $besoins, $mode);
        
        return [
            'success' => true,
            'mode' => $mode,
            'produit' => $produit,
            'attributions' => $resultat['attributions'],
            'statistiques' => $resultat['statistiques'],
            'details_calcul' => $resultat['details_calcul']
        ];
    }
    
    /**
     * Preview global pour tous les produits
     */
    private function previewGlobal(string $mode): array
    {
        $db = Flight::db();
        
        $produits = $db->fetchAll("SELECT DISTINCT type_produit FROM don WHERE quantite > 0");
        
        $resultatsGlobaux = [];
        $statsGlobales = [
            'total_distribue' => 0,
            'total_besoins' => 0,
            'total_dons' => 0,
            'villes_satisfaites' => 0
        ];
        
        foreach ($produits as $p) {
            $produit = $p['type_produit'];
            
            $dons = $db->fetchAll("
                SELECT * FROM don WHERE type_produit = ? AND quantite > 0 ORDER BY date_saisie ASC
            ", [$produit]);
            
            $besoins = $db->fetchAll("
                SELECT b.*, v.nom as ville_nom, v.region,
                       (b.quantite - b.quantite_satisfaite) as quantite_restante
                FROM besoin b
                JOIN ville v ON b.ville_id = v.id
                WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
                ORDER BY b.date_saisie ASC
            ", [$produit]);
            
            if (!empty($dons) && !empty($besoins)) {
                $resultat = $this->calculerSimulation($dons, $besoins, $mode);
                $resultatsGlobaux[$produit] = $resultat;
                
                $statsGlobales['total_distribue'] += $resultat['statistiques']['total_distribue'];
                $statsGlobales['total_besoins'] += $resultat['statistiques']['total_besoins'];
                $statsGlobales['total_dons'] += $resultat['statistiques']['total_don'];
                $statsGlobales['villes_satisfaites'] += $resultat['statistiques']['villes_completes'];
            }
        }
        
        $statsGlobales['taux_satisfaction'] = $statsGlobales['total_besoins'] > 0 
            ? round(($statsGlobales['total_distribue'] / $statsGlobales['total_besoins']) * 100, 2) 
            : 0;
        
        return [
            'success' => true,
            'mode' => $mode,
            'resultats' => $resultatsGlobaux,
            'statistiques_globales' => $statsGlobales
        ];
    }
    
    /**
     * Calcule la simulation selon le mode choisi
     */
    private function calculerSimulation(array $dons, array $besoins, string $mode): array
    {
        switch ($mode) {
            case 'croissant':
                return $this->simulationCroissant($dons, $besoins);
            case 'proportionnel':
                return $this->simulationProportionnel($dons, $besoins);
            case 'chronologique':
            default:
                return $this->simulationChronologique($dons, $besoins);
        }
    }
    
    /**
     * MODE 1: Chronologique (FIFO par date_saisie)
     * Distribue aux besoins dans l'ordre de leur date de saisie
     */
    private function simulationChronologique(array $dons, array $besoins): array
    {
        $attributions = [];
        $details = ["=== MODE CHRONOLOGIQUE (FIFO) ==="];
        
        // Trier besoins par date_saisie ASC
        usort($besoins, fn($a, $b) => strtotime($a['date_saisie']) - strtotime($b['date_saisie']));
        
        $details[] = "Ordre des besoins (par date): " . implode(' → ', 
            array_map(fn($b) => $b['ville_nom'] . "(" . $b['quantite_restante'] . ")", $besoins));
        
        $totalDonDisponible = array_sum(array_column($dons, 'quantite'));
        $donRestant = $totalDonDisponible;
        
        $details[] = "Don total disponible: $totalDonDisponible";
        
        foreach ($besoins as &$besoin) {
            if ($donRestant <= 0) break;
            
            $quantiteRestante = (int)$besoin['quantite_restante'];
            $quantiteAttribuee = min($quantiteRestante, $donRestant);
            
            if ($quantiteAttribuee > 0) {
                $attributions[] = [
                    'besoin_id' => $besoin['id'],
                    'ville_nom' => $besoin['ville_nom'],
                    'quantite_besoin' => $quantiteRestante,
                    'quantite_attribuee' => $quantiteAttribuee,
                    'complet' => ($quantiteAttribuee >= $quantiteRestante)
                ];
                
                $details[] = "→ {$besoin['ville_nom']}: attribué $quantiteAttribuee / $quantiteRestante " .
                            "(reste don: " . ($donRestant - $quantiteAttribuee) . ")";
                $donRestant -= $quantiteAttribuee;
            }
        }
        
        return $this->construireResultat($attributions, $besoins, $totalDonDisponible, $details);
    }
    
    /**
     * MODE 2: Croissant (priorité aux plus petits besoins)
     * Satisfait d'abord les villes ayant les plus petits besoins
     */
    private function simulationCroissant(array $dons, array $besoins): array
    {
        $attributions = [];
        $details = ["=== MODE CROISSANT (petits besoins d'abord) ==="];
        
        // Trier besoins par quantite_restante ASC
        usort($besoins, fn($a, $b) => (int)$a['quantite_restante'] - (int)$b['quantite_restante']);
        
        $details[] = "Ordre des besoins (par quantité croissante): " . implode(' → ', 
            array_map(fn($b) => $b['ville_nom'] . "(" . $b['quantite_restante'] . ")", $besoins));
        
        $totalDonDisponible = array_sum(array_column($dons, 'quantite'));
        $donRestant = $totalDonDisponible;
        
        $details[] = "Don total disponible: $totalDonDisponible";
        
        foreach ($besoins as &$besoin) {
            if ($donRestant <= 0) break;
            
            $quantiteRestante = (int)$besoin['quantite_restante'];
            $quantiteAttribuee = min($quantiteRestante, $donRestant);
            
            if ($quantiteAttribuee > 0) {
                $attributions[] = [
                    'besoin_id' => $besoin['id'],
                    'ville_nom' => $besoin['ville_nom'],
                    'quantite_besoin' => $quantiteRestante,
                    'quantite_attribuee' => $quantiteAttribuee,
                    'complet' => ($quantiteAttribuee >= $quantiteRestante)
                ];
                
                $details[] = "→ {$besoin['ville_nom']}: attribué $quantiteAttribuee / $quantiteRestante " .
                            "(reste don: " . ($donRestant - $quantiteAttribuee) . ")";
                $donRestant -= $quantiteAttribuee;
            }
        }
        
        return $this->construireResultat($attributions, $besoins, $totalDonDisponible, $details);
    }
    
    /**
     * MODE 3: Proportionnel (selon le poids des besoins)
     * Répartit selon la formule: part = (besoin_ville / total_besoins) × don
     * Arrondi vers le bas (floor)
     */
    private function simulationProportionnel(array $dons, array $besoins): array
    {
        $attributions = [];
        $details = ["=== MODE PROPORTIONNEL (répartition par partie décimale) ==="];
        
        $totalDonDisponible = array_sum(array_column($dons, 'quantite'));
        $totalBesoins = array_sum(array_column($besoins, 'quantite_restante'));
        
        $details[] = "Don total: $totalDonDisponible";
        $details[] = "Total besoins: $totalBesoins";
        $details[] = "";
        $details[] = "Calcul des proportions:";
        
        // Première passe: calcul proportionnel avec floor + garder la partie décimale
        $distribue = 0;
        foreach ($besoins as $key => &$besoin) {
            $quantiteRestante = (int)$besoin['quantite_restante'];
            $proportion = $totalBesoins > 0 ? $quantiteRestante / $totalBesoins : 0;
            $partExacte = $totalDonDisponible * $proportion;
            $partEntiere = (int)floor($partExacte);
            $partDecimale = $partExacte - $partEntiere;
            
            // Ne pas dépasser le besoin
            $partEntiere = min($partEntiere, $quantiteRestante);
            
            $besoin['part_calculee'] = $partEntiere;
            $besoin['part_decimale'] = $partDecimale;
            $besoin['key'] = $key;
            $distribue += $partEntiere;
            
            $details[] = "  {$besoin['ville_nom']}: $quantiteRestante/$totalBesoins = " . 
                        number_format($proportion, 4) . " × $totalDonDisponible = " . 
                        number_format($partExacte, 2) . " → floor = $partEntiere (décimale: " . 
                        number_format($partDecimale, 4) . ")";
        }
        unset($besoin);
        
        // Calculer le reste
        $reste = $totalDonDisponible - $distribue;
        $details[] = "";
        $details[] = "Total distribué (1ère passe): $distribue, Reste à redistribuer: $reste";
        
        // Redistribuer le reste par ordre de partie décimale DÉCROISSANTE (plus grande décimale d'abord)
        if ($reste > 0) {
            $details[] = "";
            $details[] = "Redistribution du reste ($reste unités) par partie décimale décroissante:";
            
            // Créer une copie pour trier par partie décimale décroissante
            $besoinsParDecimale = $besoins;
            usort($besoinsParDecimale, function($a, $b) {
                // Trier par partie décimale décroissante
                $diff = $b['part_decimale'] - $a['part_decimale'];
                if (abs($diff) < 0.0001) return 0;
                return $diff > 0 ? 1 : -1;
            });
            
            foreach ($besoinsParDecimale as $besoinTri) {
                if ($reste <= 0) break;
                
                // Trouver le besoin original dans le tableau
                foreach ($besoins as &$besoin) {
                    if ($besoin['id'] == $besoinTri['id']) {
                        $besoinRestant = (int)$besoin['quantite_restante'] - (int)$besoin['part_calculee'];
                        if ($besoinRestant > 0) {
                            $ajout = min(1, $reste, $besoinRestant);
                            $besoin['part_calculee'] += $ajout;
                            $reste -= $ajout;
                            $details[] = "  +$ajout à {$besoin['ville_nom']} (déc: " . 
                                        number_format($besoin['part_decimale'], 4) . ") → total: {$besoin['part_calculee']}";
                        }
                        break;
                    }
                }
                unset($besoin);
            }
        }
        
        $details[] = "";
        $details[] = "Reste final: $reste (doit être 0)";
        
        // Construire les attributions finales
        foreach ($besoins as $besoin) {
            if (isset($besoin['part_calculee']) && $besoin['part_calculee'] > 0) {
                $attributions[] = [
                    'besoin_id' => $besoin['id'],
                    'ville_nom' => $besoin['ville_nom'],
                    'quantite_besoin' => (int)$besoin['quantite_restante'],
                    'quantite_attribuee' => (int)$besoin['part_calculee'],
                    'complet' => ($besoin['part_calculee'] >= $besoin['quantite_restante'])
                ];
            }
        }
        
        return $this->construireResultat($attributions, $besoins, $totalDonDisponible, $details);
    }
    
    /**
     * Construit le résultat final avec statistiques
     */
    private function construireResultat(array $attributions, array $besoins, int $totalDon, array $details): array
    {
        $totalDistribue = array_sum(array_column($attributions, 'quantite_attribuee'));
        $totalBesoins = 0;
        foreach ($besoins as $b) {
            $totalBesoins += (int)($b['quantite_restante'] ?? $b['quantite'] ?? 0);
        }
        $villesCompletes = count(array_filter($attributions, fn($a) => $a['complet']));
        
        return [
            'attributions' => $attributions,
            'statistiques' => [
                'total_don' => $totalDon,
                'total_besoins' => $totalBesoins,
                'total_distribue' => $totalDistribue,
                'reste_don' => $totalDon - $totalDistribue,
                'villes_completes' => $villesCompletes,
                'villes_partielles' => count($attributions) - $villesCompletes,
                'taux_satisfaction' => $totalBesoins > 0 ? round(($totalDistribue / $totalBesoins) * 100, 2) : 0
            ],
            'details_calcul' => $details
        ];
    }

    /* ===================== VALIDATION V3 ===================== */

    /**
     * Valider et enregistrer la simulation selon le mode choisi
     * POST /simulation/valider-mode
     */
    public function validerMode(): void
    {
        $mode = Flight::request()->data->mode ?? 'chronologique';
        $produit = Flight::request()->data->produit ?? null;
        
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");
            
            if ($produit) {
                // Valider un seul produit
                $this->validerProduit($db, $produit, $mode);
            } else {
                // Valider tous les produits
                $this->validerTousProduits($db, $mode);
            }
            
            $db->runQuery("COMMIT");
            
            Flight::json([
                'success' => true,
                'message' => "Simulation validée avec succès (mode: $mode)"
            ]);
            
        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            Flight::json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Valider la simulation pour un produit
     */
    private function validerProduit($db, string $produit, string $mode): void
    {
        $dons = $db->fetchAll("
            SELECT * FROM don WHERE type_produit = ? AND quantite > 0 ORDER BY date_saisie ASC
        ", [$produit]);
        
        $besoins = $db->fetchAll("
            SELECT b.*, v.nom as ville_nom,
                   (b.quantite - b.quantite_satisfaite) as quantite_restante
            FROM besoin b
            JOIN ville v ON b.ville_id = v.id
            WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
            ORDER BY b.date_saisie ASC
        ", [$produit]);
        
        if (empty($dons) || empty($besoins)) {
            throw new \Exception("Aucun don ou besoin disponible pour $produit");
        }
        
        $resultat = $this->calculerSimulation($dons, $besoins, $mode);
        $this->enregistrerAttributions($db, $dons, $resultat['attributions'], $mode);
    }
    
    /**
     * Valider la simulation pour tous les produits
     */
    private function validerTousProduits($db, string $mode): void
    {
        $produits = $db->fetchAll("SELECT DISTINCT type_produit FROM don WHERE quantite > 0");
        
        foreach ($produits as $p) {
            $produit = $p['type_produit'];
            
            $dons = $db->fetchAll("
                SELECT * FROM don WHERE type_produit = ? AND quantite > 0 ORDER BY date_saisie ASC
            ", [$produit]);
            
            $besoins = $db->fetchAll("
                SELECT b.*, v.nom as ville_nom,
                       (b.quantite - b.quantite_satisfaite) as quantite_restante
                FROM besoin b
                JOIN ville v ON b.ville_id = v.id
                WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
                ORDER BY b.date_saisie ASC
            ", [$produit]);
            
            if (!empty($dons) && !empty($besoins)) {
                $resultat = $this->calculerSimulation($dons, $besoins, $mode);
                $this->enregistrerAttributions($db, $dons, $resultat['attributions'], $mode);
            }
        }
    }
    
    /**
     * Enregistrer les attributions en base
     */
    private function enregistrerAttributions($db, array $dons, array $attributions, string $mode): void
    {
        if (empty($dons) || empty($attributions)) {
            return;
        }
        
        $donIndex = 0;
        $donRestant = $dons[0]['quantite'] ?? 0;
        
        foreach ($attributions as $attr) {
            $quantiteAAttribuer = $attr['quantite_attribuee'];
            
            while ($quantiteAAttribuer > 0 && $donIndex < count($dons)) {
                // Si le don actuel est épuisé, passer au suivant
                if ($donRestant <= 0) {
                    $donIndex++;
                    if ($donIndex >= count($dons)) {
                        break; // Plus de dons disponibles
                    }
                    $donRestant = $dons[$donIndex]['quantite'] ?? 0;
                    continue;
                }
                
                $quantiteUtilisee = min($quantiteAAttribuer, $donRestant);
                
                if ($quantiteUtilisee > 0) {
                    // Créer l'attribution
                    $db->runQuery("
                        INSERT INTO attribution (don_id, besoin_id, quantite_attribuee, mode_simulation)
                        VALUES (?, ?, ?, ?)
                    ", [$dons[$donIndex]['id'], $attr['besoin_id'], $quantiteUtilisee, $mode]);
                    
                    // Mettre à jour le don
                    $db->runQuery("
                        UPDATE don SET quantite = quantite - ? WHERE id = ?
                    ", [$quantiteUtilisee, $dons[$donIndex]['id']]);
                    
                    // Mettre à jour le besoin
                    $db->runQuery("
                        UPDATE besoin SET quantite_satisfaite = quantite_satisfaite + ? WHERE id = ?
                    ", [$quantiteUtilisee, $attr['besoin_id']]);
                    
                    $donRestant -= $quantiteUtilisee;
                    $quantiteAAttribuer -= $quantiteUtilisee;
                }
            }
        }
    }

    /* ===================== RESET GLOBAL V3 ===================== */

    /**
     * Reset global - Restaurer l'état initial
     * POST /reset/global
     */
    public function resetGlobal(): void
    {
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");
            
            // 1. Supprimer toutes les attributions
            $db->runQuery("DELETE FROM attribution");
            
            // 2. Supprimer tous les achats
            $db->runQuery("DELETE FROM achat");
            
            // 3. Restaurer les quantités initiales des dons
            $db->runQuery("UPDATE don SET quantite = quantite_initiale");
            
            // 4. Restaurer les quantités initiales des besoins
            $db->runQuery("UPDATE besoin SET quantite = quantite_initiale, quantite_satisfaite = 0");
            
            // 5. Restaurer les dons argent
            $db->runQuery("UPDATE don_argent SET montant = montant_initial, montant_utilise = 0, statut = 'disponible'");
            
            $db->runQuery("COMMIT");
            
            // Si requête AJAX
            if (Flight::request()->ajax) {
                Flight::json([
                    'success' => true,
                    'message' => 'Reset global effectué avec succès'
                ]);
            } else {
                $_SESSION['success'] = 'Reset global effectué avec succès. Toutes les données ont été restaurées.';
                Flight::redirect('/simulation');
            }
            
        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            
            if (Flight::request()->ajax) {
                Flight::json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ]);
            } else {
                $_SESSION['error'] = 'Erreur lors du reset: ' . $e->getMessage();
                Flight::redirect('/simulation');
            }
        }
    }

    /* ===================== ANCIENNES MÉTHODES (compatibilité) ===================== */

    /**
     * Exécuter l'algorithme de simulation d'attribution des dons aux besoins
     * POST /simulation/run
     * 
     * V3: Utilise le mode envoyé par le formulaire
     */
    public function run(): void
    {
        $mode = Flight::request()->data->mode ?? 'chronologique';
        
        // Valider le mode
        if (!in_array($mode, ['chronologique', 'croissant', 'proportionnel'])) {
            $mode = 'chronologique';
        }
        
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");
            
            // Supprimer les attributions existantes
            $db->runQuery("DELETE FROM attribution");
            
            // Récupérer tous les produits avec dons disponibles
            $produits = $db->fetchAll("SELECT DISTINCT type_produit FROM don WHERE quantite > 0");
            
            $totalAttributions = 0;
            
            foreach ($produits as $p) {
                $produit = $p['type_produit'];
                
                // Récupérer les dons pour ce produit
                $dons = $db->fetchAll("
                    SELECT * FROM don 
                    WHERE type_produit = ? AND quantite > 0
                    ORDER BY date_saisie ASC
                ", [$produit]);
                
                // Récupérer les besoins pour ce produit
                $besoins = $db->fetchAll("
                    SELECT b.*, v.nom as ville_nom,
                           (b.quantite - b.quantite_satisfaite) as quantite_restante
                    FROM besoin b
                    JOIN ville v ON b.ville_id = v.id
                    WHERE b.produit = ? AND b.quantite > b.quantite_satisfaite
                    ORDER BY b.date_saisie ASC
                ", [$produit]);
                
                if (!empty($dons) && !empty($besoins)) {
                    // Calculer la simulation selon le mode
                    $resultat = $this->calculerSimulation($dons, $besoins, $mode);
                    
                    // Enregistrer les attributions
                    $this->enregistrerAttributions($db, $dons, $resultat['attributions'], $mode);
                    
                    $totalAttributions += count($resultat['attributions']);
                }
            }
            
            $db->runQuery("COMMIT");
            
            $modeLabels = [
                'chronologique' => 'Chronologique (FIFO)',
                'croissant' => 'Croissant (petits d\'abord)',
                'proportionnel' => 'Proportionnel (équitable)'
            ];
            
            $_SESSION['success'] = "Simulation terminée avec succès en mode {$modeLabels[$mode]}. {$totalAttributions} attribution(s) créée(s).";
            
        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            $_SESSION['error'] = "Erreur lors de la simulation: " . $e->getMessage();
        }
        
        $this->app->redirect('/simulation');
    }

    /* ===================== RÉINITIALISER ===================== */

    /**
     * Réinitialiser toutes les attributions et restaurer les quantités
     * POST /simulation/reset
     */
    public function reset(): void
    {
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");
            
            // Compter avant suppression
            $nbAttributions = $db->fetchRow("SELECT COUNT(*) as total FROM attribution")['total'] ?? 0;
            $nbAchats = $db->fetchRow("SELECT COUNT(*) as total FROM achat")['total'] ?? 0;
            
            // 1. Supprimer toutes les attributions
            $db->runQuery("DELETE FROM attribution");
            
            // 2. Supprimer tous les achats
            $db->runQuery("DELETE FROM achat");
            
            // 3. Restaurer les quantités initiales des dons
            $db->runQuery("UPDATE don SET quantite = quantite_initiale");
            
            // 4. Restaurer les quantités initiales des besoins
            $db->runQuery("UPDATE besoin SET quantite_satisfaite = 0");
            
            // 5. Restaurer les dons argent
            $db->runQuery("UPDATE don_argent SET montant_utilise = 0, statut = 'disponible'");
            
            $db->runQuery("COMMIT");
            
            $_SESSION['success'] = "Reset effectué: {$nbAttributions} attribution(s) et {$nbAchats} achat(s) supprimés. Quantités restaurées.";
            
        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            $_SESSION['error'] = "Erreur lors du reset: " . $e->getMessage();
        }

        $this->app->redirect('/simulation');
    }

    /* ===================== RÉSULTATS ===================== */

    /**
     * Afficher les résultats détaillés de la simulation
     * GET /simulation/results
     */
    public function results(): void
    {
        $attributions = $this->attributionModel->getAllAttributions();
        $stats = $this->calculateStats();

        // Grouper les attributions par don
        $attributionsByDon = [];
        foreach ($attributions as $attr) {
            $donId = $attr['don_id'];
            if (!isset($attributionsByDon[$donId])) {
                $attributionsByDon[$donId] = [
                    'don' => $this->donModel->getDonById($donId),
                    'attributions' => []
                ];
            }
            $attributionsByDon[$donId]['attributions'][] = $attr;
        }

        $this->app->render('simulation/results', [
            'pageTitle' => 'Résultats de la Simulation',
            'attributionsByDon' => $attributionsByDon,
            'stats' => $stats
        ]);
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Calculer les statistiques de la simulation
     * @return array Statistiques
     */
    private function calculateStats(): array
    {
        $dons = $this->donModel->getAllDons();

        $totalDons = 0;
        $totalAttribue = 0;

        foreach ($dons as $don) {
            $totalDons += $don['quantite'];
            $totalAttribue += $this->attributionModel->getTotalAttribueByDon($don['id']);
        }

        return [
            'total_dons' => $totalDons,
            'total_attribue' => $totalAttribue,
            'total_restant' => $totalDons - $totalAttribue,
            'pourcentage_attribue' => $totalDons > 0 
                ? round(($totalAttribue / $totalDons) * 100, 2) 
                : 0,
            'nombre_dons' => count($dons),
            'nombre_attributions' => $this->attributionModel->countAttributions()
        ];
    }

    /* ===================== API JSON ===================== */

    /**
     * API - Exécuter la simulation et retourner les résultats en JSON
     * POST /api/simulation/run
     * NOTE: Nécessite le module Besoins pour fonctionner
     */
    public function apiRun(): void
    {
        $this->app->json([
            'success' => false,
            'message' => 'La simulation nécessite le module Besoins pour fonctionner.'
        ], 400);
    }

    /**
     * API - Récupérer les statistiques en JSON
     * GET /api/simulation/stats
     */
    public function apiStats(): void
    {
        $stats = $this->calculateStats();

        $this->app->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /* ===================== FONCTIONS SEDRA ===================== */

    /**
     * Récupérer le pourcentage de frais depuis la table configuration
     * @return float Pourcentage de frais (ex: 10.0)
     */
    private function getFraisPourcentage(): float
    {
        $db = Flight::db();
        $stmt = $db->prepare("SELECT valeur FROM configuration WHERE cle = 'frais_achat_pourcentage'");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (float) $row['valeur'] : 10.0; // 10% par défaut
    }

    /**
     * BOUTON SIMULER - Preview sans modification en base
     * POST /simulation/simuler
     * 
     * Récupère dons nature et argent, récupère besoins par ville,
     * applique dispatch dons nature par produit, applique achat avec dons argent,
     * gère achat partiel si argent insuffisant, affiche preview résultat.
     * NE MODIFIE RIEN EN BASE.
     */
    public function simuler(): void
    {
        $db = Flight::db();
        $fraisPourcentage = $this->getFraisPourcentage();

        // 1. Récupérer dons (nature et argent)
        $donsNature = $this->donModel->getDonsByType('nature');
        $donsArgent = $this->donModel->getDonsByType('argent');

        // 2. Récupérer besoins par ville
        $besoins = Besoin::getAllBesoinsWithDetails();

        // Initialiser les résultats de la simulation
        $previewAttributions = [];
        $previewAchats = [];
        $errors = [];

        // Tableaux pour suivre les quantités restantes (simulation uniquement)
        $donRestants = [];
        foreach ($donsNature as $don) {
            $donRestants[$don['id']] = $don['quantite'];
        }

        $argentRestant = 0;
        foreach ($donsArgent as $don) {
            $argentRestant += (float) ($don['montant'] ?? $don['quantite'] ?? 0);
        }

        $besoinRestants = [];
        foreach ($besoins as $besoin) {
            $besoinRestants[$besoin['id']] = [
                'quantite' => $besoin['quantite'],
                'montant' => $besoin['quantite'] * ($besoin['prix_unitaire'] ?? 0),
                'prix_unitaire' => $besoin['prix_unitaire'] ?? 0,
                'ville_id' => $besoin['ville_id'] ?? 0,
                'produit' => $besoin['produit']
            ];
        }

        // 3. DISPATCH DONS NATURE PAR PRODUIT
        foreach ($donsNature as $don) {
            $donId = $don['id'];
            $produitDon = strtolower(trim($don['type_produit']));

            foreach ($besoins as $besoin) {
                if ($donRestants[$donId] <= 0) break;

                $produitBesoin = strtolower(trim($besoin['produit']));
                $besoinId = $besoin['id'];

                if ($produitBesoin === $produitDon && $besoinRestants[$besoinId]['quantite'] > 0) {
                    $quantiteAttribuee = min($donRestants[$donId], $besoinRestants[$besoinId]['quantite']);

                    if ($quantiteAttribuee > 0) {
                        $previewAttributions[] = [
                            'don_id' => $donId,
                            'don_produit' => $don['type_produit'],
                            'besoin_id' => $besoinId,
                            'besoin_produit' => $besoin['produit'],
                            'ville_nom' => $besoin['ville_nom'] ?? 'Inconnu',
                            'quantite_attribuee' => $quantiteAttribuee
                        ];

                        $donRestants[$donId] -= $quantiteAttribuee;
                        $besoinRestants[$besoinId]['quantite'] -= $quantiteAttribuee;
                    }
                }
            }

            // Si don non utilisé
            if ($donRestants[$donId] > 0) {
                $errors[] = "Aucun besoin correspondant au don '{$don['type_produit']}' (reste {$donRestants[$donId]} unités)";
            }
        }

        // 4. ACHAT AVEC DONS ARGENT
        foreach ($besoins as $besoin) {
            $besoinId = $besoin['id'];

            if ($besoinRestants[$besoinId]['quantite'] <= 0 || $argentRestant <= 0) continue;

            $prixUnitaire = $besoinRestants[$besoinId]['prix_unitaire'];
            if ($prixUnitaire <= 0) continue;

            $quantiteRestante = $besoinRestants[$besoinId]['quantite'];
            $montantNecessaire = $quantiteRestante * $prixUnitaire;
            $montantAvecFrais = $montantNecessaire * (1 + $fraisPourcentage / 100);

            // Achat complet ou partiel
            if ($argentRestant >= $montantAvecFrais) {
                // Achat complet
                $frais = $montantNecessaire * ($fraisPourcentage / 100);
                $previewAchats[] = [
                    'besoin_id' => $besoinId,
                    'besoin_produit' => $besoin['produit'],
                    'ville_id' => $besoin['ville_id'] ?? 0,
                    'ville_nom' => $besoin['ville_nom'] ?? 'Inconnu',
                    'quantite_achetee' => $quantiteRestante,
                    'montant_produit' => $montantNecessaire,
                    'frais' => $frais,
                    'montant_total' => $montantAvecFrais,
                    'type' => 'complet'
                ];

                $argentRestant -= $montantAvecFrais;
                $besoinRestants[$besoinId]['quantite'] = 0;
            } else {
                // Achat partiel
                $montantDisponible = $argentRestant / (1 + $fraisPourcentage / 100);
                $quantiteAchetee = floor($montantDisponible / $prixUnitaire);

                if ($quantiteAchetee > 0) {
                    $montantProduit = $quantiteAchetee * $prixUnitaire;
                    $frais = $montantProduit * ($fraisPourcentage / 100);
                    $montantTotal = $montantProduit + $frais;

                    $previewAchats[] = [
                        'besoin_id' => $besoinId,
                        'besoin_produit' => $besoin['produit'],
                        'ville_id' => $besoin['ville_id'] ?? 0,
                        'ville_nom' => $besoin['ville_nom'] ?? 'Inconnu',
                        'quantite_achetee' => $quantiteAchetee,
                        'montant_produit' => $montantProduit,
                        'frais' => $frais,
                        'montant_total' => $montantTotal,
                        'type' => 'partiel'
                    ];

                    $argentRestant -= $montantTotal;
                    $besoinRestants[$besoinId]['quantite'] -= $quantiteAchetee;
                    $errors[] = "Achat partiel pour '{$besoin['produit']}' : seulement {$quantiteAchetee} unités (argent insuffisant)";
                } else {
                    $errors[] = "Pas assez d'argent pour acheter '{$besoin['produit']}' (besoin {$montantAvecFrais} Ar, disponible {$argentRestant} Ar)";
                }
            }
        }

        // Stocker les résultats de la simulation en session pour validation
        $_SESSION['simulation_preview'] = [
            'attributions' => $previewAttributions,
            'achats' => $previewAchats,
            'errors' => $errors,
            'argent_restant' => $argentRestant,
            'frais_pourcentage' => $fraisPourcentage,
            'timestamp' => time()
        ];

        // Calculer les statistiques de preview
        $totalAttribue = array_sum(array_column($previewAttributions, 'quantite_attribuee'));
        $totalAchete = array_sum(array_column($previewAchats, 'quantite_achetee'));
        $totalFrais = array_sum(array_column($previewAchats, 'frais'));
        $totalMontantAchats = array_sum(array_column($previewAchats, 'montant_total'));

        $this->app->render('simulation/preview', [
            'pageTitle' => 'Simulation - Preview',
            'previewAttributions' => $previewAttributions,
            'previewAchats' => $previewAchats,
            'errors' => $errors,
            'totalAttribue' => $totalAttribue,
            'totalAchete' => $totalAchete,
            'totalFrais' => $totalFrais,
            'totalMontantAchats' => $totalMontantAchats,
            'argentRestant' => $argentRestant,
            'fraisPourcentage' => $fraisPourcentage
        ]);
    }

    /**
     * BOUTON VALIDER - Insérer attributions et achats en base
     * POST /simulation/valider
     * 
     * Insère attributions (table attribution), insère achats (table achat),
     * met à jour quantités dons et besoins.
     */
    public function valider(): void
    {
        // Vérifier que la simulation a été faite
        if (!isset($_SESSION['simulation_preview'])) {
            $_SESSION['error'] = "Veuillez d'abord exécuter une simulation.";
            $this->app->redirect('/simulation');
            return;
        }

        $preview = $_SESSION['simulation_preview'];
        $db = Flight::db();

        // Vérifier que la simulation n'est pas trop ancienne (5 minutes max)
        if (time() - $preview['timestamp'] > 300) {
            $_SESSION['error'] = "La simulation a expiré. Veuillez relancer la simulation.";
            unset($_SESSION['simulation_preview']);
            $this->app->redirect('/simulation');
            return;
        }

        try {
            $db->beginTransaction();

            // 1. Insérer les attributions
            foreach ($preview['attributions'] as $attr) {
                $this->attributionModel->createAttribution(
                    $attr['don_id'],
                    $attr['besoin_id'],
                    $attr['quantite_attribuee']
                );

                // Mettre à jour quantité don
                $stmt = $db->prepare("UPDATE don SET quantite = quantite - ? WHERE id = ?");
                $stmt->execute([$attr['quantite_attribuee'], $attr['don_id']]);

                // Mettre à jour quantité besoin
                $stmt = $db->prepare("UPDATE besoin SET quantite = quantite - ? WHERE id = ?");
                $stmt->execute([$attr['quantite_attribuee'], $attr['besoin_id']]);
            }

            // 2. Insérer les achats
            foreach ($preview['achats'] as $achat) {
                $this->achatModel->createAchat(
                    $achat['ville_id'],
                    $achat['besoin_id'],
                    0, // don_id = 0 pour les achats avec argent groupé
                    $achat['montant_produit'],
                    $achat['frais'],
                    $achat['montant_total']
                );

                // Mettre à jour quantité besoin
                $stmt = $db->prepare("UPDATE besoin SET quantite = quantite - ? WHERE id = ?");
                $stmt->execute([$achat['quantite_achetee'], $achat['besoin_id']]);
            }

            // 3. Déduire l'argent utilisé des dons argent
            $totalArgentUtilise = array_sum(array_column($preview['achats'], 'montant_total'));
            if ($totalArgentUtilise > 0) {
                // Répartir sur les dons argent disponibles
                $donsArgent = $this->donModel->getDonsByType('argent');
                $resteADeduire = $totalArgentUtilise;

                foreach ($donsArgent as $don) {
                    if ($resteADeduire <= 0) break;

                    $montantDon = (float) ($don['montant'] ?? $don['quantite'] ?? 0);
                    $deduction = min($montantDon, $resteADeduire);

                    if ($deduction > 0) {
                        $stmt = $db->prepare("UPDATE don SET quantite = quantite - ? WHERE id = ?");
                        $stmt->execute([$deduction, $don['id']]);
                        $resteADeduire -= $deduction;
                    }
                }
            }

            $db->commit();

            $nbAttributions = count($preview['attributions']);
            $nbAchats = count($preview['achats']);
            $_SESSION['success'] = "Validation réussie : {$nbAttributions} attribution(s) et {$nbAchats} achat(s) enregistré(s).";

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Erreur lors de la validation : " . $e->getMessage();
        }

        unset($_SESSION['simulation_preview']);
        $this->app->redirect('/simulation');
    }

    /**
     * GET /recap/data - Endpoint JSON pour Ajax
     * Retourne les totaux de besoins (total, satisfait, restant)
     */
    public function recapData(): void
    {
        $db = Flight::db();

        // Total des besoins
        $stmtTotal = $db->query("SELECT COALESCE(SUM(quantite), 0) as total FROM besoin");
        $total = (int) $stmtTotal->fetch(\PDO::FETCH_ASSOC)['total'];

        // Total des besoins satisfaits (via attributions)
        $stmtSatisfait = $db->query("SELECT COALESCE(SUM(quantite_attribuee), 0) as satisfait FROM attribution");
        $satisfaitAttributions = (int) $stmtSatisfait->fetch(\PDO::FETCH_ASSOC)['satisfait'];

        // Total des achats (quantités achetées)
        // On calcule à partir du montant_produit / prix_unitaire si disponible
        $stmtAchats = $db->query("
            SELECT COALESCE(SUM(a.montant_produit / NULLIF(b.prix_unitaire, 0)), 0) as achete 
            FROM achat a 
            LEFT JOIN besoin b ON a.besoin_id = b.id
        ");
        $row = $stmtAchats->fetch(\PDO::FETCH_ASSOC);
        $achete = (int) ($row['achete'] ?? 0);

        $satisfait = $satisfaitAttributions + $achete;
        $restant = max(0, $total - $satisfait);

        // Montants financiers
        $stmtMontantTotal = $db->query("SELECT COALESCE(SUM(quantite * prix_unitaire), 0) as montant FROM besoin WHERE prix_unitaire > 0");
        $montantTotal = (float) $stmtMontantTotal->fetch(\PDO::FETCH_ASSOC)['montant'];

        $stmtMontantSatisfait = $db->query("SELECT COALESCE(SUM(montant_total), 0) as montant FROM achat");
        $montantSatisfait = (float) $stmtMontantSatisfait->fetch(\PDO::FETCH_ASSOC)['montant'];

        $montantRestant = max(0, $montantTotal - $montantSatisfait);

        $this->app->json([
            'success' => true,
            'total' => $total,
            'satisfait' => $satisfait,
            'restant' => $restant,
            'montant_total' => $montantTotal,
            'montant_satisfait' => $montantSatisfait,
            'montant_restant' => $montantRestant
        ]);
    }

    /**
     * Page récapitulatif avec bouton Actualiser Ajax
     * GET /recap
     */
    public function recap(): void
    {
        $this->app->render('simulation/recap', [
            'pageTitle' => 'Récapitulatif des Besoins'
        ]);
    }

    /**
     * V3: Statistiques de simulation
     * GET /simulation/stats
     */
    public function stats(): void
    {
        $db = Flight::db();
        
        $nbAttributions = $db->fetchRow("SELECT COUNT(*) as total FROM attribution")['total'] ?? 0;
        
        $stats = [
            'dons' => $db->fetchAll("
                SELECT type_produit, 
                       COALESCE(SUM(quantite_initiale), SUM(quantite)) as initial, 
                       SUM(quantite) as restant
                FROM don GROUP BY type_produit
            "),
            'besoins' => $db->fetchAll("
                SELECT produit, 
                       COALESCE(SUM(quantite_initiale), SUM(quantite)) as initial, 
                       SUM(quantite_satisfaite) as satisfait,
                       SUM(quantite - quantite_satisfaite) as restant
                FROM besoin GROUP BY produit
            "),
            'attributions' => $nbAttributions,
            'nombre_attributions' => $nbAttributions,
            'achats' => [
                'total' => $db->fetchRow("SELECT COUNT(*) as total FROM achat")['total'] ?? 0,
                'montant' => $db->fetchRow("SELECT COALESCE(SUM(montant_total), 0) as montant FROM achat WHERE statut = 'valide'")['montant'] ?? 0
            ],
            'dons_argent' => [
                'total' => $db->fetchRow("SELECT COALESCE(SUM(montant), 0) as total FROM don_argent")['total'] ?? 0,
                'utilise' => $db->fetchRow("SELECT COALESCE(SUM(montant_utilise), 0) as utilise FROM don_argent")['utilise'] ?? 0
            ]
        ];

        Flight::json(['success' => true, 'stats' => $stats]);
    }
}

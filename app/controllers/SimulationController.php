<?php
namespace app\controllers;

use flight\Engine;
use app\models\Don;
use app\models\Besoin;
use app\models\Attribution;
use app\models\Achat;
use Flight;

/**
 * Controller SimulationController - Simulation d'attribution des dons
 * Projet BNGRC - Modules Mahery + Sedra
 * 
 * ALGORITHME DE SIMULATION :
 * Ce module attribue les dons aux besoins des villes.
 * La logique de simulation utilise le module Besoins pour fonctionner.
 * 
 * FONCTIONS SEDRA :
 * - simuler() : Preview sans modification en base
 * - valider() : Insertion des attributions et achats
 * - recapData() : Endpoint JSON pour Ajax
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

    /* ===================== PAGE SIMULATION ===================== */

    /**
     * Afficher la page de simulation
     * GET /simulation
     */
    public function index(): void
    {
        $dons = $this->donModel->getAllDons();
        $attributions = $this->attributionModel->getAllAttributions();
        
        // Récupérer les besoins pour la simulation
        $besoins = Besoin::getAllBesoinsWithDetails();

        // Calculer les statistiques
        $stats = $this->calculateStats();
        $stats['nombre_besoins'] = count($besoins);
        $stats['total_besoins'] = array_sum(array_column($besoins, 'quantite'));

        $this->app->render('simulation/index', [
            'pageTitle' => 'Simulation d\'attribution',
            'dons' => $dons,
            'besoins' => $besoins,
            'attributions' => $attributions,
            'stats' => $stats,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== EXÉCUTER SIMULATION ===================== */

    /**
     * Exécuter l'algorithme de simulation d'attribution des dons aux besoins
     * POST /simulation/run
     * 
     * ALGORITHME :
     * 1. Récupérer dons ORDER BY date_saisie ASC
     * 2. Récupérer besoins ORDER BY date_saisie ASC  
     * 3. Pour chaque don :
     *    - Chercher besoins même produit
     *    - Calculer besoin restant
     *    - attribuer = min(don_restant, besoin_restant)
     *    - Enregistrer attribution
     *    - Continuer jusqu'à don épuisé
     */
    public function run(): void
    {
        // Réinitialiser les attributions existantes
        $this->attributionModel->deleteAll();

        $db = Flight::db();
        
        // 1. Récupérer tous les dons triés par date
        $dons = $this->donModel->getDonsOrderByDate();
        
        // 2. Récupérer tous les besoins triés par date (created_at si date_saisie n'existe pas)
        $stmt = $db->query("SELECT * FROM besoin ORDER BY created_at ASC, id ASC");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Tableau pour suivre les quantités restantes
        $donRestants = [];
        foreach ($dons as $don) {
            $donRestants[$don['id']] = $don['quantite'];
        }
        
        $besoinRestants = [];
        foreach ($besoins as $besoin) {
            $besoinRestants[$besoin['id']] = $besoin['quantite'];
        }
        
        $attributionsCreees = 0;
        
        // 3. Pour chaque don
        foreach ($dons as $don) {
            $donId = $don['id'];
            $produitDon = strtolower(trim($don['type_produit']));
            
            // Tant qu'il reste du don à attribuer
            while ($donRestants[$donId] > 0) {
                $meilleurBesoin = null;
                
                // Chercher le premier besoin correspondant (même produit, avec quantité restante)
                foreach ($besoins as $besoin) {
                    $produitBesoin = strtolower(trim($besoin['produit']));
                    
                    // Correspondance par produit
                    if ($produitBesoin === $produitDon && $besoinRestants[$besoin['id']] > 0) {
                        $meilleurBesoin = $besoin;
                        break;
                    }
                }
                
                // Si aucun besoin trouvé, passer au don suivant
                if (!$meilleurBesoin) {
                    break;
                }
                
                // Calculer la quantité à attribuer
                $besoinId = $meilleurBesoin['id'];
                $quantiteAttribuee = min($donRestants[$donId], $besoinRestants[$besoinId]);
                
                // Créer l'attribution
                if ($quantiteAttribuee > 0) {
                    $this->attributionModel->createAttribution($donId, $besoinId, $quantiteAttribuee);
                    
                    // Mettre à jour les quantités restantes
                    $donRestants[$donId] -= $quantiteAttribuee;
                    $besoinRestants[$besoinId] -= $quantiteAttribuee;
                    
                    $attributionsCreees++;
                }
            }
        }
        
        $_SESSION['success'] = "Simulation terminée avec succès. {$attributionsCreees} attribution(s) créée(s).";
        $this->app->redirect('/simulation');
    }

    /* ===================== RÉINITIALISER ===================== */

    /**
     * Réinitialiser toutes les attributions
     * POST /simulation/reset
     */
    public function reset(): void
    {
        $count = $this->attributionModel->countAttributions();
        $result = $this->attributionModel->deleteAll();

        if ($result) {
            $_SESSION['success'] = "Simulation réinitialisée. {$count} attribution(s) supprimée(s).";
        } else {
            $_SESSION['error'] = "Erreur lors de la réinitialisation.";
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
}

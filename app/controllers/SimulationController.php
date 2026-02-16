<?php
namespace app\controllers;

use flight\Engine;
use app\models\Don;
use app\models\Besoin;
use app\models\Attribution;
use Flight;

/**
 * Controller SimulationController - Simulation d'attribution des dons
 * Projet BNGRC - Module Mahery
 * 
 * ALGORITHME DE SIMULATION :
 * 1. Récupérer dons ORDER BY date_saisie ASC
 * 2. Récupérer besoins ORDER BY date_saisie ASC
 * 3. Pour chaque don :
 *    - Chercher besoins même produit
 *    - Calculer besoin restant
 *    - attribuer = min(don_restant, besoin_restant)
 *    - Enregistrer attribution
 *    - Diminuer quantité don disponible
 *    - Continuer jusqu'à don = 0
 */
class SimulationController
{
    protected Engine $app;
    protected Don $donModel;
    protected Besoin $besoinModel;
    protected Attribution $attributionModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->donModel = new Don(Flight::db());
        $this->besoinModel = new Besoin(Flight::db());
        $this->attributionModel = new Attribution(Flight::db());
    }

    /* ===================== PAGE SIMULATION ===================== */

    /**
     * Afficher la page de simulation
     * GET /simulation
     */
    public function index(): void
    {
        $dons = $this->donModel->getAllDons();
        $besoins = $this->besoinModel->getAllBesoins();
        $attributions = $this->attributionModel->getAllAttributions();

        // Calculer les statistiques
        $stats = $this->calculateStats();

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
     * Exécuter l'algorithme de simulation
     * POST /simulation/run
     */
    public function run(): void
    {
        // 1. Réinitialiser les attributions existantes
        $this->attributionModel->deleteAll();

        // 2. Récupérer les dons triés par date
        $dons = $this->donModel->getDonsOrderByDate();

        // 3. Récupérer les besoins triés par date
        $besoins = $this->besoinModel->getBesoinsOrderByDate();

        // Tableau pour suivre les quantités restantes
        $donsRestants = [];
        foreach ($dons as $don) {
            $donsRestants[$don['id']] = $don['quantite'];
        }

        $besoinsRestants = [];
        foreach ($besoins as $besoin) {
            $besoinsRestants[$besoin['id']] = $besoin['quantite'];
        }

        // Compteurs pour les statistiques
        $totalAttributions = 0;
        $quantiteTotaleAttribuee = 0;

        // 4. Algorithme d'attribution
        foreach ($dons as $don) {
            $donId = $don['id'];
            $produitDon = $don['type_produit'];
            $quantiteDonRestante = $donsRestants[$donId];

            // Si le don est épuisé, passer au suivant
            if ($quantiteDonRestante <= 0) {
                continue;
            }

            // Chercher les besoins correspondant au même produit
            foreach ($besoins as $besoin) {
                $besoinId = $besoin['id'];
                $produitBesoin = $besoin['produit'];
                $quantiteBesoinRestante = $besoinsRestants[$besoinId];

                // Vérifier si c'est le même produit
                if (strtolower($produitDon) !== strtolower($produitBesoin)) {
                    continue;
                }

                // Si le besoin est satisfait, passer au suivant
                if ($quantiteBesoinRestante <= 0) {
                    continue;
                }

                // Si le don est épuisé, sortir de la boucle besoins
                if ($quantiteDonRestante <= 0) {
                    break;
                }

                // Calculer la quantité à attribuer
                $quantiteAttribuee = min($quantiteDonRestante, $quantiteBesoinRestante);

                // Créer l'attribution
                $result = $this->attributionModel->createAttribution(
                    $donId,
                    $besoinId,
                    $quantiteAttribuee
                );

                if ($result) {
                    // Mettre à jour les quantités restantes
                    $donsRestants[$donId] -= $quantiteAttribuee;
                    $besoinsRestants[$besoinId] -= $quantiteAttribuee;
                    $quantiteDonRestante -= $quantiteAttribuee;

                    $totalAttributions++;
                    $quantiteTotaleAttribuee += $quantiteAttribuee;
                }
            }
        }

        // Message de succès avec statistiques
        if ($totalAttributions > 0) {
            $_SESSION['success'] = "Simulation terminée avec succès ! <br>
                                   <strong>{$totalAttributions}</strong> attribution(s) créée(s).<br>
                                   <strong>{$quantiteTotaleAttribuee}</strong> unités attribuées au total.";
        } else {
            $_SESSION['success'] = "Simulation terminée. Aucune attribution n'a pu être effectuée (pas de correspondance produit/besoin).";
        }

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
        $besoins = $this->besoinModel->getAllBesoins();

        $totalDons = 0;
        $totalBesoins = 0;
        $totalAttribue = 0;

        foreach ($dons as $don) {
            $totalDons += $don['quantite'];
        }

        foreach ($besoins as $besoin) {
            $totalBesoins += $besoin['quantite'];
            $totalAttribue += $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
        }

        $pourcentageCouverture = $totalBesoins > 0 
            ? round(($totalAttribue / $totalBesoins) * 100, 2) 
            : 0;

        return [
            'total_dons' => $totalDons,
            'total_besoins' => $totalBesoins,
            'total_attribue' => $totalAttribue,
            'total_restant' => $totalBesoins - $totalAttribue,
            'pourcentage_couverture' => $pourcentageCouverture,
            'nombre_dons' => count($dons),
            'nombre_besoins' => count($besoins),
            'nombre_attributions' => $this->attributionModel->countAttributions()
        ];
    }

    /* ===================== API JSON ===================== */

    /**
     * API - Exécuter la simulation et retourner les résultats en JSON
     * POST /api/simulation/run
     */
    public function apiRun(): void
    {
        // Réinitialiser
        $this->attributionModel->deleteAll();

        // Exécuter l'algorithme (même logique que run())
        $dons = $this->donModel->getDonsOrderByDate();
        $besoins = $this->besoinModel->getBesoinsOrderByDate();

        $donsRestants = [];
        foreach ($dons as $don) {
            $donsRestants[$don['id']] = $don['quantite'];
        }

        $besoinsRestants = [];
        foreach ($besoins as $besoin) {
            $besoinsRestants[$besoin['id']] = $besoin['quantite'];
        }

        $attributionsCreees = [];

        foreach ($dons as $don) {
            $donId = $don['id'];
            $produitDon = $don['type_produit'];
            $quantiteDonRestante = $donsRestants[$donId];

            if ($quantiteDonRestante <= 0) continue;

            foreach ($besoins as $besoin) {
                $besoinId = $besoin['id'];
                $produitBesoin = $besoin['produit'];
                $quantiteBesoinRestante = $besoinsRestants[$besoinId];

                if (strtolower($produitDon) !== strtolower($produitBesoin)) continue;
                if ($quantiteBesoinRestante <= 0) continue;
                if ($quantiteDonRestante <= 0) break;

                $quantiteAttribuee = min($quantiteDonRestante, $quantiteBesoinRestante);

                $result = $this->attributionModel->createAttribution($donId, $besoinId, $quantiteAttribuee);

                if ($result) {
                    $donsRestants[$donId] -= $quantiteAttribuee;
                    $besoinsRestants[$besoinId] -= $quantiteAttribuee;
                    $quantiteDonRestante -= $quantiteAttribuee;

                    $attributionsCreees[] = [
                        'don_id' => $donId,
                        'besoin_id' => $besoinId,
                        'quantite' => $quantiteAttribuee,
                        'produit' => $produitDon
                    ];
                }
            }
        }

        $stats = $this->calculateStats();

        $this->app->json([
            'success' => true,
            'message' => 'Simulation terminée',
            'stats' => $stats,
            'attributions' => $attributionsCreees
        ]);
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
}

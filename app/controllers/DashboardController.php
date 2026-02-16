<?php
namespace app\controllers;

use flight\Engine;
use app\models\Ville;
use app\models\Besoin;
use app\models\Don;
use app\models\Attribution;
use Flight;

/**
 * Controller DashboardController - Tableau de bord BNGRC
 * Projet BNGRC - Module Mahery
 * 
 * Affiche :
 * - Liste des villes
 * - Liste des besoins
 * - Quantité demandée
 * - Quantité reçue (SUM attribution)
 * - Quantité restante
 * - Valeur totale
 * - Pourcentage couverture
 */
class DashboardController
{
    protected Engine $app;
    protected Ville $villeModel;
    protected Besoin $besoinModel;
    protected Don $donModel;
    protected Attribution $attributionModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->villeModel = new Ville(Flight::db());
        $this->besoinModel = new Besoin(Flight::db());
        $this->donModel = new Don(Flight::db());
        $this->attributionModel = new Attribution(Flight::db());
    }

    /* ===================== DASHBOARD PRINCIPAL ===================== */

    /**
     * Afficher le tableau de bord principal
     * GET /bngrc/dashboard
     */
    public function index(): void
    {
        // Récupérer toutes les données
        $villes = $this->villeModel->getAllVilles();
        $besoins = $this->besoinModel->getAllBesoins();
        $dons = $this->donModel->getAllDons();

        // Calculer les statistiques globales
        $statsGlobales = $this->calculateGlobalStats();

        // Calculer les statistiques par ville
        $statsParVille = $this->calculateStatsByVille();

        // Calculer les statistiques par produit
        $statsParProduit = $this->calculateStatsByProduit();

        // Enrichir les besoins avec les infos d'attribution
        $besoinsEnrichis = $this->enrichBesoins($besoins);

        $this->app->render('dashboard/index', [
            'pageTitle' => 'Dashboard BNGRC - Suivi des Besoins et Dons',
            'villes' => $villes,
            'besoins' => $besoinsEnrichis,
            'dons' => $dons,
            'statsGlobales' => $statsGlobales,
            'statsParVille' => $statsParVille,
            'statsParProduit' => $statsParProduit
        ]);
    }

    /* ===================== STATISTIQUES GLOBALES ===================== */

    /**
     * Calculer les statistiques globales
     * @return array Statistiques
     */
    private function calculateGlobalStats(): array
    {
        $besoins = $this->besoinModel->getAllBesoins();
        $dons = $this->donModel->getAllDons();

        $totalQuantiteDemandee = 0;
        $totalValeurDemandee = 0;
        $totalQuantiteRecue = 0;

        foreach ($besoins as $besoin) {
            $totalQuantiteDemandee += $besoin['quantite'];
            $totalValeurDemandee += ($besoin['quantite'] * $besoin['prix_unitaire']);
            $totalQuantiteRecue += $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
        }

        $totalQuantiteRestante = $totalQuantiteDemandee - $totalQuantiteRecue;
        $pourcentageCouverture = $totalQuantiteDemandee > 0 
            ? round(($totalQuantiteRecue / $totalQuantiteDemandee) * 100, 2) 
            : 0;

        // Calculer la valeur reçue (estimation basée sur les prix unitaires des besoins)
        $valeurRecue = 0;
        foreach ($besoins as $besoin) {
            $quantiteRecue = $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
            $valeurRecue += ($quantiteRecue * $besoin['prix_unitaire']);
        }

        $valeurRestante = $totalValeurDemandee - $valeurRecue;

        // Statistiques des dons
        $totalDons = 0;
        $totalDonsAttribues = 0;
        foreach ($dons as $don) {
            $totalDons += $don['quantite'];
            $totalDonsAttribues += $this->attributionModel->getTotalAttribueByDon($don['id']);
        }

        return [
            'nombre_villes' => count($this->villeModel->getAllVilles()),
            'nombre_besoins' => count($besoins),
            'nombre_dons' => count($dons),
            'nombre_attributions' => $this->attributionModel->countAttributions(),
            'quantite_demandee' => $totalQuantiteDemandee,
            'quantite_recue' => $totalQuantiteRecue,
            'quantite_restante' => $totalQuantiteRestante,
            'valeur_totale' => $totalValeurDemandee,
            'valeur_recue' => $valeurRecue,
            'valeur_restante' => $valeurRestante,
            'pourcentage_couverture' => $pourcentageCouverture,
            'total_dons' => $totalDons,
            'dons_attribues' => $totalDonsAttribues,
            'dons_restants' => $totalDons - $totalDonsAttribues
        ];
    }

    /* ===================== STATISTIQUES PAR VILLE ===================== */

    /**
     * Calculer les statistiques par ville
     * @return array Statistiques par ville
     */
    private function calculateStatsByVille(): array
    {
        $villes = $this->villeModel->getAllVilles();
        $stats = [];

        foreach ($villes as $ville) {
            $besoins = $this->besoinModel->getBesoinsByVille($ville['id']);
            
            $quantiteDemandee = 0;
            $quantiteRecue = 0;
            $valeurTotale = 0;

            foreach ($besoins as $besoin) {
                $quantiteDemandee += $besoin['quantite'];
                $valeurTotale += ($besoin['quantite'] * $besoin['prix_unitaire']);
                $quantiteRecue += $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
            }

            $quantiteRestante = $quantiteDemandee - $quantiteRecue;
            $pourcentage = $quantiteDemandee > 0 
                ? round(($quantiteRecue / $quantiteDemandee) * 100, 2) 
                : 0;

            // Calculer la valeur reçue
            $valeurRecue = 0;
            foreach ($besoins as $besoin) {
                $recue = $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
                $valeurRecue += ($recue * $besoin['prix_unitaire']);
            }

            $stats[] = [
                'ville_id' => $ville['id'],
                'ville_nom' => $ville['nom'],
                'ville_region' => $ville['region'],
                'nombre_besoins' => count($besoins),
                'quantite_demandee' => $quantiteDemandee,
                'quantite_recue' => $quantiteRecue,
                'quantite_restante' => $quantiteRestante,
                'valeur_totale' => $valeurTotale,
                'valeur_recue' => $valeurRecue,
                'valeur_restante' => $valeurTotale - $valeurRecue,
                'pourcentage_couverture' => $pourcentage
            ];
        }

        // Trier par pourcentage croissant (les plus dans le besoin en premier)
        usort($stats, function($a, $b) {
            return $a['pourcentage_couverture'] <=> $b['pourcentage_couverture'];
        });

        return $stats;
    }

    /* ===================== STATISTIQUES PAR PRODUIT ===================== */

    /**
     * Calculer les statistiques par produit
     * @return array Statistiques par produit
     */
    private function calculateStatsByProduit(): array
    {
        $produits = $this->besoinModel->getAllProduits();
        $stats = [];

        foreach ($produits as $produit) {
            $besoins = $this->besoinModel->getBesoinsByProduit($produit);
            
            $quantiteDemandee = 0;
            $quantiteRecue = 0;
            $valeurTotale = 0;

            foreach ($besoins as $besoin) {
                $quantiteDemandee += $besoin['quantite'];
                $valeurTotale += ($besoin['quantite'] * $besoin['prix_unitaire']);
                $quantiteRecue += $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
            }

            $quantiteRestante = $quantiteDemandee - $quantiteRecue;
            $pourcentage = $quantiteDemandee > 0 
                ? round(($quantiteRecue / $quantiteDemandee) * 100, 2) 
                : 0;

            $stats[] = [
                'produit' => $produit,
                'nombre_besoins' => count($besoins),
                'quantite_demandee' => $quantiteDemandee,
                'quantite_recue' => $quantiteRecue,
                'quantite_restante' => $quantiteRestante,
                'valeur_totale' => $valeurTotale,
                'pourcentage_couverture' => $pourcentage
            ];
        }

        return $stats;
    }

    /* ===================== ENRICHISSEMENT BESOINS ===================== */

    /**
     * Enrichir les besoins avec les informations d'attribution
     * @param array $besoins Liste des besoins
     * @return array Besoins enrichis
     */
    private function enrichBesoins(array $besoins): array
    {
        foreach ($besoins as &$besoin) {
            $quantiteRecue = $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
            $besoin['quantite_recue'] = $quantiteRecue;
            $besoin['quantite_restante'] = $besoin['quantite'] - $quantiteRecue;
            $besoin['valeur_recue'] = $quantiteRecue * $besoin['prix_unitaire'];
            $besoin['valeur_restante'] = $besoin['quantite_restante'] * $besoin['prix_unitaire'];
            $besoin['pourcentage_couverture'] = $besoin['quantite'] > 0 
                ? round(($quantiteRecue / $besoin['quantite']) * 100, 2) 
                : 0;
        }

        return $besoins;
    }

    /* ===================== DÉTAIL PAR VILLE ===================== */

    /**
     * Afficher le détail d'une ville
     * GET /bngrc/dashboard/ville/@id
     */
    public function villeDetail(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);

        if (!$ville) {
            $_SESSION['error'] = "Ville introuvable.";
            $this->app->redirect('/bngrc/dashboard');
            return;
        }

        $besoins = $this->besoinModel->getBesoinsByVille($id);
        $besoinsEnrichis = $this->enrichBesoins($besoins);

        // Calculer les stats de la ville
        $stats = $this->calculateVilleStats($id);

        $this->app->render('dashboard/ville_detail', [
            'pageTitle' => "Dashboard - {$ville['nom']}",
            'ville' => $ville,
            'besoins' => $besoinsEnrichis,
            'stats' => $stats
        ]);
    }

    /**
     * Calculer les statistiques d'une ville
     * @param int $villeId ID de la ville
     * @return array Statistiques
     */
    private function calculateVilleStats(int $villeId): array
    {
        $besoins = $this->besoinModel->getBesoinsByVille($villeId);
        
        $quantiteDemandee = 0;
        $quantiteRecue = 0;
        $valeurTotale = 0;

        foreach ($besoins as $besoin) {
            $quantiteDemandee += $besoin['quantite'];
            $valeurTotale += ($besoin['quantite'] * $besoin['prix_unitaire']);
            $quantiteRecue += $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
        }

        $valeurRecue = 0;
        foreach ($besoins as $besoin) {
            $recue = $this->attributionModel->getTotalAttribueByBesoin($besoin['id']);
            $valeurRecue += ($recue * $besoin['prix_unitaire']);
        }

        return [
            'nombre_besoins' => count($besoins),
            'quantite_demandee' => $quantiteDemandee,
            'quantite_recue' => $quantiteRecue,
            'quantite_restante' => $quantiteDemandee - $quantiteRecue,
            'valeur_totale' => $valeurTotale,
            'valeur_recue' => $valeurRecue,
            'valeur_restante' => $valeurTotale - $valeurRecue,
            'pourcentage_couverture' => $quantiteDemandee > 0 
                ? round(($quantiteRecue / $quantiteDemandee) * 100, 2) 
                : 0
        ];
    }

    /* ===================== API JSON ===================== */

    /**
     * API - Statistiques globales en JSON
     * GET /api/bngrc/dashboard
     */
    public function apiIndex(): void
    {
        $this->app->json([
            'success' => true,
            'data' => [
                'global' => $this->calculateGlobalStats(),
                'par_ville' => $this->calculateStatsByVille(),
                'par_produit' => $this->calculateStatsByProduit()
            ]
        ]);
    }

    /**
     * API - Statistiques d'une ville en JSON
     * GET /api/bngrc/dashboard/ville/@id
     */
    public function apiVilleDetail(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);

        if (!$ville) {
            $this->app->json([
                'success' => false,
                'message' => 'Ville introuvable'
            ], 404);
            return;
        }

        $besoins = $this->besoinModel->getBesoinsByVille($id);
        $besoinsEnrichis = $this->enrichBesoins($besoins);
        $stats = $this->calculateVilleStats($id);

        $this->app->json([
            'success' => true,
            'data' => [
                'ville' => $ville,
                'besoins' => $besoinsEnrichis,
                'stats' => $stats
            ]
        ]);
    }
}

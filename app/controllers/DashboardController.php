<?php
namespace app\controllers;

use flight\Engine;
use app\models\Ville;
use app\models\Don;
use app\models\Attribution;
use Flight;

/**
 * Controller DashboardController - Tableau de bord BNGRC
 * Projet BNGRC - Module Mahery
 * 
 * Affiche :
 * - Liste des villes
 * - Liste des dons
 * - Quantité donnée
 * - Statistiques globales
 */
class DashboardController
{
    protected Engine $app;
    protected Ville $villeModel;
    protected Don $donModel;
    protected Attribution $attributionModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->villeModel = new Ville(Flight::db());
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
        $dons = $this->donModel->getAllDons();

        // Calculer les statistiques globales
        $statsGlobales = $this->calculateGlobalStats();

        // Calculer les statistiques par ville
        $statsParVille = $this->calculateStatsByVille();

        // Calculer les statistiques par produit (dons)
        $statsParProduit = $this->calculateStatsByProduit();

        // Enrichir les dons avec les infos d'attribution
        $donsEnrichis = $this->enrichDons($dons);

        $this->app->render('dashboard/index', [
            'pageTitle' => 'Dashboard BNGRC - Suivi des Dons',
            'villes' => $villes,
            'dons' => $donsEnrichis,
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
        $dons = $this->donModel->getAllDons();

        $totalDons = 0;
        $totalDonsAttribues = 0;

        foreach ($dons as $don) {
            $totalDons += $don['quantite'];
            $totalDonsAttribues += $this->attributionModel->getTotalAttribueByDon($don['id']);
        }

        return [
            'nombre_villes' => count($this->villeModel->getAllVilles()),
            'nombre_dons' => count($dons),
            'nombre_attributions' => $this->attributionModel->countAttributions(),
            'total_dons' => $totalDons,
            'dons_attribues' => $totalDonsAttribues,
            'dons_restants' => $totalDons - $totalDonsAttribues,
            'pourcentage_attribue' => $totalDons > 0 
                ? round(($totalDonsAttribues / $totalDons) * 100, 2) 
                : 0
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
            $stats[] = [
                'ville_id' => $ville['id'],
                'ville_nom' => $ville['nom'],
                'ville_region' => $ville['region'],
                'population' => $ville['population'] ?? 0
            ];
        }

        return $stats;
    }

    /* ===================== STATISTIQUES PAR PRODUIT ===================== */

    /**
     * Calculer les statistiques par produit (basé sur les dons)
     * @return array Statistiques par produit
     */
    private function calculateStatsByProduit(): array
    {
        $produits = $this->donModel->getAllTypesProduits();
        $stats = [];

        foreach ($produits as $produit) {
            $dons = $this->donModel->getDonsByTypeProduit($produit);
            
            $quantiteTotale = 0;
            $quantiteAttribuee = 0;

            foreach ($dons as $don) {
                $quantiteTotale += $don['quantite'];
                $quantiteAttribuee += $this->attributionModel->getTotalAttribueByDon($don['id']);
            }

            $stats[] = [
                'produit' => $produit,
                'nombre_dons' => count($dons),
                'quantite_totale' => $quantiteTotale,
                'quantite_attribuee' => $quantiteAttribuee,
                'quantite_restante' => $quantiteTotale - $quantiteAttribuee,
                'pourcentage_attribue' => $quantiteTotale > 0 
                    ? round(($quantiteAttribuee / $quantiteTotale) * 100, 2) 
                    : 0
            ];
        }

        return $stats;
    }

    /* ===================== ENRICHISSEMENT DONS ===================== */

    /**
     * Enrichir les dons avec les informations d'attribution
     * @param array $dons Liste des dons
     * @return array Dons enrichis
     */
    private function enrichDons(array $dons): array
    {
        foreach ($dons as &$don) {
            $quantiteAttribuee = $this->attributionModel->getTotalAttribueByDon($don['id']);
            $don['quantite_attribuee'] = $quantiteAttribuee;
            $don['quantite_restante'] = $don['quantite'] - $quantiteAttribuee;
            $don['pourcentage_attribue'] = $don['quantite'] > 0 
                ? round(($quantiteAttribuee / $don['quantite']) * 100, 2) 
                : 0;
        }

        return $dons;
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

        $this->app->render('dashboard/ville_detail', [
            'pageTitle' => "Dashboard - {$ville['nom']}",
            'ville' => $ville
        ]);
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
     * API - Détail d'une ville en JSON
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

        $this->app->json([
            'success' => true,
            'data' => [
                'ville' => $ville
            ]
        ]);
    }
}

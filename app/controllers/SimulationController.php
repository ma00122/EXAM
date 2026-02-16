<?php
namespace app\controllers;

use flight\Engine;
use app\models\Don;
use app\models\Attribution;
use Flight;

/**
 * Controller SimulationController - Simulation d'attribution des dons
 * Projet BNGRC - Module Mahery
 * 
 * ALGORITHME DE SIMULATION :
 * Ce module affiche les statistiques des dons et attributions.
 * La logique de simulation nécessite le module Besoins pour fonctionner complètement.
 */
class SimulationController
{
    protected Engine $app;
    protected Don $donModel;
    protected Attribution $attributionModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->donModel = new Don(Flight::db());
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
        $attributions = $this->attributionModel->getAllAttributions();

        // Calculer les statistiques
        $stats = $this->calculateStats();

        $this->app->render('simulation/index', [
            'pageTitle' => 'Simulation d\'attribution',
            'dons' => $dons,
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
     * NOTE: Nécessite le module Besoins (Sedra) pour fonctionner
     */
    public function run(): void
    {
        $_SESSION['error'] = "La simulation nécessite le module Besoins pour fonctionner. Veuillez intégrer ce module.";
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
}

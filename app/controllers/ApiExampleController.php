<?php
namespace app\controllers;

use flight\Engine;
use app\models\DatabaseModel;
use Flight;

class ApiExampleController
{
    protected Engine $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /* ===================== API VÉHICULES ===================== */

    public function getVehicules()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getVehicules();
    }

    public function getVehiculeById($id)
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getVehiculeById($id);
    }

    /* ===================== API CHAUFFEURS ===================== */

    public function getChauffeurs()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getChauffeurs();
    }

    public function getChauffeurById($id)
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getChauffeurById($id);
    }

    /* ===================== API HISTO JOUR ===================== */

    public function getTourJours()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTourJours();
    }

    public function getTourJourById($id)
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTourJourById($id);
    }

    /* ===================== API TRAJETS FIXES ===================== */

    public function getTrajetsFixes()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTrajetsFixes();
    }

    public function getTrajetFixeById($id)
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTrajetFixeById($id);
    }

    /* ===================== API TRAJETS TOURS ===================== */

    public function getTrajetsTours()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTrajetsTours();
    }

    public function getTrajetTourById($id)
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTrajetTourById($id);
    }

    public function getTrajetToursWithDetails()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getTrajetToursWithDetails();
    }

    public function getHistoJourWithDetails()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        return $databaseModel->getHistoJourWithDetails();
    }

    /* ===================== PAGES ===================== */

    public function showVehiculesPage()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        $vehicules = $databaseModel->getVehicules();

        $this->app->render('vehicules', [
            'vehicules' => $vehicules
        ]);
    }

    public function showChauffeursPage()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        $chauffeurs = $databaseModel->getChauffeurs();

        $this->app->render('chauffeurs', [
            'chauffeurs' => $chauffeurs
        ]);
    }

    public function showTrajetsFixesPage()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        $trajets = $databaseModel->getTrajetsFixes();

        $this->app->render('trajets_fixes', [
            'trajets' => $trajets
        ]);
    }

    public function showTrajetsToursPage()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        $trajets = $databaseModel->getTrajetToursWithDetails();

        $this->app->render('trajets_tours', [
            'trajets' => $trajets
        ]);
    }

    public function showHistoJourPage()
    {
        $databaseModel = new DatabaseModel(Flight::db());
        $historique = $databaseModel->getHistoJourWithDetails();

        $this->app->render('histo_jour', [
            'historique' => $historique
        ]);
    }

    public function showHomePage()
    {
        $databaseModel = new DatabaseModel(Flight::db());

        $this->app->render('index', [
            'vehicules'     => $databaseModel->getVehicules(),
            'chauffeurs'    => $databaseModel->getChauffeurs(),
            'trajets_fixes' => $databaseModel->getTrajetsFixes()
        ]);
    }
}
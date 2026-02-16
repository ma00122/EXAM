<?php
namespace app\controllers;

use flight\Engine;
use Flight;

/**
 * Controller ObjetController - Gestion des objets (placeholder)
 */
class ObjetController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Page d'accueil
     */
    public function accueil(): void
    {
        // Rediriger vers les besoins pour le moment
        Flight::redirect('/besoins');
    }

    public function liste(): void
    {
        Flight::redirect('/besoins');
    }

    public function detail(int $id): void
    {
        Flight::redirect('/besoins');
    }

    public function historique(int $id): void
    {
        Flight::redirect('/besoins');
    }

    public function mesObjets(): void
    {
        Flight::redirect('/besoins');
    }

    public function create(): void
    {
        Flight::redirect('/besoins');
    }

    public function edit(int $id): void
    {
        Flight::redirect('/besoins');
    }

    public function delete(int $id): void
    {
        Flight::redirect('/besoins');
    }
}

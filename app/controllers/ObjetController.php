<?php
namespace app\controllers;

use flight\Engine;
use Flight;

/**
 * Minimal ObjetController placeholder to satisfy routes and render views.
 */
class ObjetController
{
    protected Engine $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function accueil(): void
    {
        // Provide minimal variables expected by the view to avoid runtime errors
        $this->app->render('objets/accueil', [
            'pageTitle' => 'Accueil',
            'categories' => [],
            'objets' => [],
            'e' => fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'),
        ]);
    }

    public function liste(): void
    {
        // Provide minimal data to the view
        $objets = [];
        $categories = [];
        $search = $this->app->request()->query['q'] ?? '';

        $this->app->render('objets/liste', [
            'pageTitle' => 'Objets',
            'objets' => $objets,
            'categories' => $categories,
            'search' => $search,
            'e' => fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8')
        ]);
    }

    public function detail(int $id): void
    {
        $this->app->render('objets/detail', [
            'pageTitle' => 'Détail de l\'objet',
            'id' => $id,
        ]);
    }

    public function historique(int $id): void
    {
        $this->app->render('objets/historique', [
            'pageTitle' => 'Historique',
            'id' => $id,
        ]);
    }

    public function mesObjets(): void
    {
        $this->app->render('objets/mes_objets', [
            'pageTitle' => 'Mes objets',
        ]);
    }

    public function create(): void
    {
        $this->app->render('objets/formulaire', [
            'pageTitle' => 'Nouveau objet',
        ]);
    }

    public function edit(int $id): void
    {
        $this->app->render('objets/formulaire', [
            'pageTitle' => 'Modifier objet',
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        // Minimal redirect back to listing
        $this->app->redirect('/objets');
    }

    public function deletePhoto(int $id): void
    {
        $this->app->redirect('/mes-objets');
    }

    public function parPrix(int $id, int $pourcentage): void
    {
        $this->app->render('objets/par_prix', [
            'pageTitle' => 'Par prix',
            'id' => $id,
            'pourcentage' => $pourcentage,
        ]);
    }
}

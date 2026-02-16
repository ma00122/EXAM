<?php
namespace app\controllers;

use flight\Engine;
use app\models\Ville;
use Flight;

/**
 * Controller VilleController - Gestion des villes
 * Projet BNGRC - Module Bolton
 */
class VilleController
{
    protected Engine $app;
    protected Ville $villeModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->villeModel = new Ville(Flight::db());
    }

    /* ===================== LISTE DES VILLES ===================== */

    /**
     * Afficher la liste des villes
     * GET /villes
     */
    public function index(): void
    {
        $search = $this->app->request()->query['search'] ?? '';
        
        if (!empty($search)) {
            $villes = $this->villeModel->searchVilles($search);
        } else {
            $villes = $this->villeModel->getAllVilles();
        }

        $regions = $this->villeModel->getAllRegions();

        $this->app->render('villes/index', [
            'pageTitle' => 'Gestion des Villes',
            'villes' => $villes,
            'regions' => $regions,
            'search' => $search,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        // Nettoyer les messages flash
        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== FORMULAIRE CRÉATION ===================== */

    /**
     * Afficher le formulaire de création
     * GET /villes/create
     */
    public function create(): void
    {
        $regions = $this->villeModel->getAllRegions();

        $this->app->render('villes/create', [
            'pageTitle' => 'Ajouter une Ville',
            'regions' => $regions,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== ENREGISTREMENT ===================== */

    /**
     * Enregistrer une nouvelle ville
     * POST /villes/store
     */
    public function store(): void
    {
        $nom = trim($this->app->request()->data['nom'] ?? '');
        $region = trim($this->app->request()->data['region'] ?? '');

        // Validation
        $errors = $this->validateVille($nom, $region);

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['nom' => $nom, 'region' => $region];
            $this->app->redirect('/villes/create');
            return;
        }

        // Insertion
        $result = $this->villeModel->insertVille($nom, $region);

        if ($result) {
            $_SESSION['success'] = "La ville <strong>{$nom}</strong> a été ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de la ville.";
        }

        $this->app->redirect('/villes');
    }

    /* ===================== FORMULAIRE MODIFICATION ===================== */

    /**
     * Afficher le formulaire de modification
     * GET /villes/edit/@id
     */
    public function edit(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);

        if (!$ville) {
            $_SESSION['error'] = "Ville introuvable.";
            $this->app->redirect('/villes');
            return;
        }

        $regions = $this->villeModel->getAllRegions();

        $this->app->render('villes/edit', [
            'pageTitle' => 'Modifier la Ville',
            'ville' => $ville,
            'regions' => $regions,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== MISE À JOUR ===================== */

    /**
     * Mettre à jour une ville
     * POST /villes/update/@id
     */
    public function update(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);

        if (!$ville) {
            $_SESSION['error'] = "Ville introuvable.";
            $this->app->redirect('/villes');
            return;
        }

        $nom = trim($this->app->request()->data['nom'] ?? '');
        $region = trim($this->app->request()->data['region'] ?? '');

        // Validation
        $errors = $this->validateVille($nom, $region);

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['nom' => $nom, 'region' => $region];
            $this->app->redirect("/villes/edit/{$id}");
            return;
        }

        // Mise à jour
        $result = $this->villeModel->updateVille($id, $nom, $region);

        if ($result) {
            $_SESSION['success'] = "La ville <strong>{$nom}</strong> a été modifiée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de la ville.";
        }

        $this->app->redirect('/villes');
    }

    /* ===================== SUPPRESSION ===================== */

    /**
     * Supprimer une ville
     * GET /villes/delete/@id
     */
    public function delete(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);

        if (!$ville) {
            $_SESSION['error'] = "Ville introuvable.";
            $this->app->redirect('/villes');
            return;
        }

        // Vérifier si la ville a des besoins associés
        if ($this->villeModel->hasBesoins($id)) {
            $_SESSION['error'] = "Impossible de supprimer cette ville car elle a des besoins associés.";
            $this->app->redirect('/villes');
            return;
        }

        $result = $this->villeModel->deleteVille($id);

        if ($result) {
            $_SESSION['success'] = "La ville <strong>{$ville['nom']}</strong> a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la ville.";
        }

        $this->app->redirect('/villes');
    }

    /* ===================== VALIDATION ===================== */

    /**
     * Valider les données d'une ville
     * @param string $nom Nom de la ville
     * @param string $region Région de la ville
     * @return array Liste des erreurs
     */
    private function validateVille(string $nom, string $region): array
    {
        $errors = [];

        if (empty($nom)) {
            $errors[] = "Le nom de la ville est obligatoire.";
        } elseif (strlen($nom) < 2) {
            $errors[] = "Le nom de la ville doit contenir au moins 2 caractères.";
        } elseif (strlen($nom) > 100) {
            $errors[] = "Le nom de la ville ne peut pas dépasser 100 caractères.";
        }

        if (empty($region)) {
            $errors[] = "La région est obligatoire.";
        } elseif (strlen($region) < 2) {
            $errors[] = "La région doit contenir au moins 2 caractères.";
        } elseif (strlen($region) > 100) {
            $errors[] = "La région ne peut pas dépasser 100 caractères.";
        }

        return $errors;
    }

    /* ===================== API JSON (BONUS) ===================== */

    /**
     * Retourner toutes les villes en JSON
     * GET /api/villes
     */
    public function apiIndex(): void
    {
        $villes = $this->villeModel->getAllVilles();
        $this->app->json($villes);
    }

    /**
     * Retourner une ville en JSON
     * GET /api/villes/@id
     */
    public function apiShow(int $id): void
    {
        $ville = $this->villeModel->getVilleById($id);
        
        if (!$ville) {
            $this->app->json(['error' => 'Ville introuvable'], 404);
            return;
        }
        
        $this->app->json($ville);
    }
}

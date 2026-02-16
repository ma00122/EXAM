<?php
namespace app\controllers;

use flight\Engine;
use app\models\Besoin;
use Flight;

/**
 * Controller BesoinController - Gestion des besoins
 */
class BesoinController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * GET /besoins - Liste de tous les besoins
     */
    public function index(): void
    {
        // Récupérer tous les besoins avec détails
        $besoins = Besoin::getAllBesoinsWithDetails();

        // Message flash
        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        Flight::render('besoins/index', [
            'pageTitle' => 'Liste des Besoins',
            'besoins' => $besoins,
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * GET /besoins/create - Formulaire de création
     */
    public function create(): void
    {
        $villes = Besoin::getAllVilles();
        $types = Besoin::getAllTypes();

        Flight::render('besoins/create', [
            'pageTitle' => 'Ajouter un Besoin',
            'villes' => $villes,
            'types' => $types
        ]);
    }

    /**
     * POST /besoins/store - Enregistrer un nouveau besoin
     */
    public function store(): void
    {
        $data = [
            'ville_id' => Flight::request()->data->ville_id,
            'type_id' => Flight::request()->data->type_id,
            'produit' => Flight::request()->data->produit,
            'quantite' => Flight::request()->data->quantite,
            'prix_unitaire' => Flight::request()->data->prix_unitaire
        ];

        // Validation
        $errors = $this->validateBesoinData($data);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            Flight::redirect('/besoins/create');
            return;
        }

        // Insertion
        $result = Besoin::insertBesoin($data);

        if ($result) {
            $_SESSION['flash_success'] = 'Besoin ajouté avec succès !';
            Flight::redirect('/besoins');
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'ajout du besoin.';
            Flight::redirect('/besoins/create');
        }
    }

    /**
     * GET /besoins/edit/@id - Formulaire de modification
     */
    public function edit(int $id): void
    {
        $besoin = Besoin::getBesoinById($id);

        if (!$besoin) {
            $_SESSION['flash_error'] = 'Besoin introuvable.';
            Flight::redirect('/besoins');
            return;
        }

        $villes = Besoin::getAllVilles();
        $types = Besoin::getAllTypes();

        Flight::render('besoins/edit', [
            'pageTitle' => 'Modifier le Besoin',
            'besoin' => $besoin,
            'villes' => $villes,
            'types' => $types
        ]);
    }

    /**
     * POST /besoins/update/@id - Mettre à jour un besoin
     * Note: Seule la quantité est modifiable (prix_unitaire non modifiable après insertion)
     */
    public function update(int $id): void
    {
        $besoin = Besoin::getBesoinById($id);

        if (!$besoin) {
            $_SESSION['flash_error'] = 'Besoin introuvable.';
            Flight::redirect('/besoins');
            return;
        }

        $quantite = (int) Flight::request()->data->quantite;

        // Validation quantite > 0
        if ($quantite <= 0) {
            $_SESSION['flash_error'] = 'La quantité doit être supérieure à 0.';
            Flight::redirect('/besoins/edit/' . $id);
            return;
        }

        // Mise à jour de la quantité uniquement
        $result = Besoin::updateQuantite($id, $quantite);

        if ($result) {
            $_SESSION['flash_success'] = 'Besoin mis à jour avec succès !';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        Flight::redirect('/besoins');
    }

    /**
     * GET /besoins/delete/@id - Supprimer un besoin
     */
    public function delete(int $id): void
    {
        $result = Besoin::deleteBesoin($id);

        if ($result) {
            $_SESSION['flash_success'] = 'Besoin supprimé avec succès !';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        Flight::redirect('/besoins');
    }

    /**
     * Validation des données du besoin
     */
    private function validateBesoinData(array $data): array
    {
        $errors = [];

        if (empty($data['ville_id']) || (int) $data['ville_id'] <= 0) {
            $errors[] = 'Veuillez sélectionner une ville.';
        }

        if (empty($data['type_id']) || (int) $data['type_id'] <= 0) {
            $errors[] = 'Veuillez sélectionner un type de besoin.';
        }

        if (empty(trim($data['produit'] ?? ''))) {
            $errors[] = 'Le produit est requis.';
        }

        if (empty($data['quantite']) || (int) $data['quantite'] <= 0) {
            $errors[] = 'La quantité doit être supérieure à 0.';
        }

        if (empty($data['prix_unitaire']) || (float) $data['prix_unitaire'] <= 0) {
            $errors[] = 'Le prix unitaire doit être supérieur à 0.';
        }

        return $errors;
    }
}

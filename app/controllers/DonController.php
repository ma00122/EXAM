<?php
namespace app\controllers;

use flight\Engine;
use app\models\Don;
use app\models\Attribution;
use Flight;

/**
 * Controller DonController - Gestion des dons
 * Projet BNGRC - Module Mahery
 */
class DonController
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

    /* ===================== LISTE DES DONS ===================== */

    /**
     * Afficher la liste des dons
     * GET /dons
     */
    public function index(): void
    {
        $filterProduit = $this->app->request()->query['produit'] ?? '';
        
        if (!empty($filterProduit)) {
            $dons = $this->donModel->getDonsByTypeProduit($filterProduit);
        } else {
            $dons = $this->donModel->getAllDons();
        }

        // Ajouter les infos d'attribution pour chaque don
        foreach ($dons as &$don) {
            $don['quantite_attribuee'] = $this->attributionModel->getTotalAttribueByDon($don['id']);
            $don['quantite_restante'] = $don['quantite'] - $don['quantite_attribuee'];
        }

        $typesProduits = $this->donModel->getAllTypesProduits();

        $this->app->render('dons/index', [
            'pageTitle' => 'Gestion des Dons',
            'dons' => $dons,
            'typesProduits' => $typesProduits,
            'filterProduit' => $filterProduit,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        // Nettoyer les messages flash
        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== FORMULAIRE CRÉATION ===================== */

    /**
     * Afficher le formulaire de création
     * GET /dons/create
     */
    public function create(): void
    {
        $typesProduits = $this->donModel->getAllTypesProduits();

        $this->app->render('dons/create', [
            'pageTitle' => 'Ajouter un Don',
            'typesProduits' => $typesProduits,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== ENREGISTREMENT ===================== */

    /**
     * Enregistrer un nouveau don
     * POST /dons/store
     */
    public function store(): void
    {
        $typeProduit = trim($this->app->request()->data['type_produit'] ?? '');
        $quantite = (int) ($this->app->request()->data['quantite'] ?? 0);
        $dateSaisie = trim($this->app->request()->data['date_saisie'] ?? '');

        // Si pas de date fournie, utiliser aujourd'hui
        if (empty($dateSaisie)) {
            $dateSaisie = date('Y-m-d');
        }

        // Validation
        $errors = $this->validateDon($typeProduit, $quantite);

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'type_produit' => $typeProduit,
                'quantite' => $quantite,
                'date_saisie' => $dateSaisie
            ];
            $this->app->redirect('/dons/create');
            return;
        }

        // Insertion
        $result = $this->donModel->insertDon($typeProduit, $quantite, $dateSaisie);

        if ($result) {
            $_SESSION['success'] = "Le don de <strong>{$quantite} {$typeProduit}</strong> a été ajouté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du don.";
        }

        $this->app->redirect('/dons');
    }

    /* ===================== FORMULAIRE MODIFICATION ===================== */

    /**
     * Afficher le formulaire de modification
     * GET /dons/edit/@id
     */
    public function edit(int $id): void
    {
        $don = $this->donModel->getDonById($id);

        if (!$don) {
            $_SESSION['error'] = "Don introuvable.";
            $this->app->redirect('/dons');
            return;
        }

        $typesProduits = $this->donModel->getAllTypesProduits();

        $this->app->render('dons/edit', [
            'pageTitle' => 'Modifier le Don',
            'don' => $don,
            'typesProduits' => $typesProduits,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== MISE À JOUR ===================== */

    /**
     * Mettre à jour un don
     * POST /dons/update/@id
     */
    public function update(int $id): void
    {
        $don = $this->donModel->getDonById($id);

        if (!$don) {
            $_SESSION['error'] = "Don introuvable.";
            $this->app->redirect('/dons');
            return;
        }

        $typeProduit = trim($this->app->request()->data['type_produit'] ?? '');
        $quantite = (int) ($this->app->request()->data['quantite'] ?? 0);
        $dateSaisie = trim($this->app->request()->data['date_saisie'] ?? '');

        // Validation
        $errors = $this->validateDon($typeProduit, $quantite);

        // Vérifier que la nouvelle quantité n'est pas inférieure à la quantité déjà attribuée
        $quantiteAttribuee = $this->attributionModel->getTotalAttribueByDon($id);
        if ($quantite < $quantiteAttribuee) {
            $errors[] = "La quantité ne peut pas être inférieure à la quantité déjà attribuée ({$quantiteAttribuee}).";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'type_produit' => $typeProduit,
                'quantite' => $quantite,
                'date_saisie' => $dateSaisie
            ];
            $this->app->redirect("/dons/edit/{$id}");
            return;
        }

        // Mise à jour
        $result = $this->donModel->updateDon($id, $typeProduit, $quantite, $dateSaisie);

        if ($result) {
            $_SESSION['success'] = "Le don a été modifié avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du don.";
        }

        $this->app->redirect('/dons');
    }

    /* ===================== SUPPRESSION ===================== */

    /**
     * Supprimer un don
     * GET /dons/delete/@id
     */
    public function delete(int $id): void
    {
        $don = $this->donModel->getDonById($id);

        if (!$don) {
            $_SESSION['error'] = "Don introuvable.";
            $this->app->redirect('/dons');
            return;
        }

        // Vérifier si le don a des attributions
        if ($this->donModel->hasAttributions($id)) {
            $_SESSION['error'] = "Impossible de supprimer ce don car il a des attributions. Veuillez d'abord réinitialiser la simulation.";
            $this->app->redirect('/dons');
            return;
        }

        $result = $this->donModel->deleteDon($id);

        if ($result) {
            $_SESSION['success'] = "Le don <strong>{$don['type_produit']}</strong> a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du don.";
        }

        $this->app->redirect('/dons');
    }

    /* ===================== DÉTAIL ===================== */

    /**
     * Afficher le détail d'un don avec ses attributions
     * GET /dons/@id
     */
    public function show(int $id): void
    {
        $don = $this->donModel->getDonById($id);

        if (!$don) {
            $_SESSION['error'] = "Don introuvable.";
            $this->app->redirect('/dons');
            return;
        }

        $attributions = $this->attributionModel->getAttributionsByDon($id);
        $quantiteAttribuee = $this->attributionModel->getTotalAttribueByDon($id);

        $this->app->render('dons/show', [
            'pageTitle' => "Détail du don - {$don['type_produit']}",
            'don' => $don,
            'attributions' => $attributions,
            'quantiteAttribuee' => $quantiteAttribuee,
            'quantiteRestante' => $don['quantite'] - $quantiteAttribuee
        ]);
    }

    /* ===================== VALIDATION ===================== */

    /**
     * Valider les données d'un don
     * @param string $typeProduit Type de produit
     * @param int $quantite Quantité
     * @return array Tableau d'erreurs
     */
    private function validateDon(string $typeProduit, int $quantite): array
    {
        $errors = [];

        if (empty($typeProduit)) {
            $errors[] = "Le type de produit est obligatoire.";
        } elseif (strlen($typeProduit) > 100) {
            $errors[] = "Le type de produit ne doit pas dépasser 100 caractères.";
        }

        if ($quantite <= 0) {
            $errors[] = "La quantité doit être supérieure à 0.";
        }

        return $errors;
    }

    /* ===================== API JSON ===================== */

    /**
     * API - Liste des dons en JSON
     * GET /api/dons
     */
    public function apiIndex(): void
    {
        $dons = $this->donModel->getAllDons();
        
        foreach ($dons as &$don) {
            $don['quantite_attribuee'] = $this->attributionModel->getTotalAttribueByDon($don['id']);
            $don['quantite_restante'] = $don['quantite'] - $don['quantite_attribuee'];
        }

        $this->app->json([
            'success' => true,
            'count' => count($dons),
            'data' => $dons
        ]);
    }

    /**
     * API - Détail d'un don en JSON
     * GET /api/dons/@id
     */
    public function apiShow(int $id): void
    {
        $don = $this->donModel->getDonById($id);

        if (!$don) {
            $this->app->json([
                'success' => false,
                'message' => 'Don introuvable'
            ], 404);
            return;
        }

        $don['quantite_attribuee'] = $this->attributionModel->getTotalAttribueByDon($id);
        $don['quantite_restante'] = $don['quantite'] - $don['quantite_attribuee'];
        $don['attributions'] = $this->attributionModel->getAttributionsByDon($id);

        $this->app->json([
            'success' => true,
            'data' => $don
        ]);
    }
}

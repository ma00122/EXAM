<?php
namespace app\controllers;

use flight\Engine;
use app\models\DonArgent;
use Flight;

/**
 * Controller DonArgentController - Gestion des dons en argent
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class DonArgentController
{
    protected Engine $app;
    protected DonArgent $donArgentModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->donArgentModel = new DonArgent(Flight::db());
    }

    /* ===================== LISTE DES DONS ARGENT ===================== */

    /**
     * Afficher la liste des dons argent
     * GET /dons-argent
     */
    public function index(): void
    {
        $donsArgent = $this->donArgentModel->getAll();
        $stats = $this->donArgentModel->getStats();

        $this->app->render('dons-argent/index', [
            'pageTitle' => 'Gestion des Dons Argent',
            'donsArgent' => $donsArgent,
            'stats' => $stats,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== FORMULAIRE CRÉATION ===================== */

    /**
     * Afficher le formulaire de création
     * GET /dons-argent/create
     */
    public function create(): void
    {
        $this->app->render('dons-argent/create', [
            'pageTitle' => 'Ajouter un Don Argent',
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== ENREGISTREMENT ===================== */

    /**
     * Enregistrer un nouveau don argent
     * POST /dons-argent/store
     */
    public function store(): void
    {
        $montant = (float) ($this->app->request()->data['montant'] ?? 0);
        $donateur = trim($this->app->request()->data['donateur'] ?? 'Anonyme');
        $dateSaisie = trim($this->app->request()->data['date_saisie'] ?? date('Y-m-d'));
        $notes = trim($this->app->request()->data['notes'] ?? '');

        // Validation
        $errors = [];
        if ($montant <= 0) {
            $errors[] = "Le montant doit être positif.";
        }
        if (empty($donateur)) {
            $donateur = 'Anonyme';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $this->app->request()->data;
            $this->app->redirect('/dons-argent/create');
            return;
        }

        $result = $this->donArgentModel->create($montant, $donateur, $dateSaisie, $notes ?: null);

        if ($result) {
            $_SESSION['success'] = "Don argent de <strong>" . number_format($montant, 0, ',', ' ') . " Ar</strong> ajouté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du don argent.";
        }

        $this->app->redirect('/dons-argent');
    }

    /* ===================== FORMULAIRE ÉDITION ===================== */

    /**
     * Afficher le formulaire d'édition
     * GET /dons-argent/edit/@id
     */
    public function edit(int $id): void
    {
        $donArgent = $this->donArgentModel->getById($id);

        if (!$donArgent) {
            $_SESSION['error'] = "Don argent non trouvé.";
            $this->app->redirect('/dons-argent');
            return;
        }

        $this->app->render('dons-argent/edit', [
            'pageTitle' => 'Modifier Don Argent #' . $id,
            'donArgent' => $donArgent,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['error']);
    }

    /* ===================== MISE À JOUR ===================== */

    /**
     * Mettre à jour un don argent
     * POST /dons-argent/update/@id
     */
    public function update(int $id): void
    {
        $donArgent = $this->donArgentModel->getById($id);

        if (!$donArgent) {
            $_SESSION['error'] = "Don argent non trouvé.";
            $this->app->redirect('/dons-argent');
            return;
        }

        $montant = (float) ($this->app->request()->data['montant'] ?? 0);
        $donateur = trim($this->app->request()->data['donateur'] ?? 'Anonyme');
        $dateSaisie = trim($this->app->request()->data['date_saisie'] ?? date('Y-m-d'));
        $notes = trim($this->app->request()->data['notes'] ?? '');

        // Validation
        if ($montant <= 0) {
            $_SESSION['error'] = "Le montant doit être positif.";
            $this->app->redirect('/dons-argent/edit/' . $id);
            return;
        }

        $data = [
            'montant' => $montant,
            'donateur' => $donateur ?: 'Anonyme',
            'date_saisie' => $dateSaisie,
            'notes' => $notes ?: null
        ];

        $result = $this->donArgentModel->update($id, $data);

        if ($result) {
            $_SESSION['success'] = "Don argent #$id mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du don argent.";
        }

        $this->app->redirect('/dons-argent');
    }

    /* ===================== SUPPRESSION ===================== */

    /**
     * Supprimer un don argent
     * GET /dons-argent/delete/@id
     */
    public function delete(int $id): void
    {
        $donArgent = $this->donArgentModel->getById($id);

        if (!$donArgent) {
            $_SESSION['error'] = "Don argent non trouvé.";
            $this->app->redirect('/dons-argent');
            return;
        }

        // Vérifier si le don a été utilisé
        if ($donArgent['montant_utilise'] > 0) {
            $_SESSION['error'] = "Ce don argent a déjà été utilisé et ne peut pas être supprimé.";
            $this->app->redirect('/dons-argent');
            return;
        }

        $result = $this->donArgentModel->delete($id);

        if ($result) {
            $_SESSION['success'] = "Don argent #$id supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du don argent.";
        }

        $this->app->redirect('/dons-argent');
    }
}

<?php
namespace app\controllers;

use flight\Engine;
use app\models\Attribution;
use app\models\Don;
use app\models\Ville;
use Flight;

/**
 * Controller AttributionController - Gestion des attributions CRUD
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class AttributionController
{
    protected Engine $app;
    protected Attribution $attributionModel;
    protected Don $donModel;
    protected Ville $villeModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->attributionModel = new Attribution(Flight::db());
        $this->donModel = new Don(Flight::db());
        $this->villeModel = new Ville(Flight::db());
    }

    /* ===================== LISTE DES ATTRIBUTIONS ===================== */

    /**
     * Afficher la liste des attributions
     * GET /attributions
     */
    public function index(): void
    {
        $filterVilleId = $this->app->request()->query['ville_id'] ?? null;
        
        if (!empty($filterVilleId)) {
            $attributions = $this->attributionModel->getAttributionsByVille((int) $filterVilleId);
        } else {
            $attributions = $this->attributionModel->getAllAttributions();
        }

        $villes = $this->villeModel->getAllVilles();

        // Statistiques
        $stats = [
            'total_attributions' => count($attributions),
            'quantite_totale' => array_sum(array_column($attributions, 'quantite_attribuee'))
        ];

        $this->app->render('attributions/index', [
            'pageTitle' => 'Gestion des Attributions',
            'attributions' => $attributions,
            'villes' => $villes,
            'stats' => $stats,
            'filterVilleId' => $filterVilleId,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== FORMULAIRE CRÉATION ===================== */

    /**
     * Afficher le formulaire de création
     * GET /attributions/create
     */
    public function create(): void
    {
        $dons = $this->donModel->getAllDons();
        
        // Récupérer les besoins
        $db = Flight::db();
        $stmt = $db->query("SELECT b.*, v.nom as ville_nom FROM besoin b JOIN ville v ON b.ville_id = v.id ORDER BY v.nom, b.produit");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculer les quantités restantes pour chaque don
        foreach ($dons as &$don) {
            $don['quantite_restante'] = $this->donModel->getQuantiteRestante($don['id']);
        }

        $this->app->render('attributions/create', [
            'pageTitle' => 'Créer une Attribution',
            'dons' => $dons,
            'besoins' => $besoins,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== ENREGISTREMENT ===================== */

    /**
     * Enregistrer une nouvelle attribution
     * POST /attributions/store
     */
    public function store(): void
    {
        $donId = (int) ($this->app->request()->data['don_id'] ?? 0);
        $besoinId = (int) ($this->app->request()->data['besoin_id'] ?? 0);
        $quantiteAttribuee = (int) ($this->app->request()->data['quantite_attribuee'] ?? 0);

        // Validation
        $errors = [];
        if ($donId <= 0) {
            $errors[] = "Veuillez sélectionner un don.";
        }
        if ($besoinId <= 0) {
            $errors[] = "Veuillez sélectionner un besoin.";
        }
        if ($quantiteAttribuee <= 0) {
            $errors[] = "La quantité attribuée doit être positive.";
        }

        // Vérifier la quantité disponible du don
        if ($donId > 0) {
            $quantiteRestante = $this->donModel->getQuantiteRestante($donId);
            if ($quantiteAttribuee > $quantiteRestante) {
                $errors[] = "Quantité insuffisante. Disponible: {$quantiteRestante}";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $this->app->request()->data;
            $this->app->redirect('/attributions/create');
            return;
        }

        $result = $this->attributionModel->createAttribution($donId, $besoinId, $quantiteAttribuee);

        if ($result) {
            $_SESSION['success'] = "Attribution créée avec succès. Quantité: {$quantiteAttribuee}";
        } else {
            $_SESSION['error'] = "Erreur lors de la création de l'attribution.";
        }

        $this->app->redirect('/attributions');
    }

    /* ===================== FORMULAIRE ÉDITION ===================== */

    /**
     * Afficher le formulaire d'édition
     * GET /attributions/edit/@id
     */
    public function edit(int $id): void
    {
        $attribution = $this->attributionModel->getAttributionById($id);

        if (!$attribution) {
            $_SESSION['error'] = "Attribution non trouvée.";
            $this->app->redirect('/attributions');
            return;
        }

        $dons = $this->donModel->getAllDons();
        
        $db = Flight::db();
        $stmt = $db->query("SELECT b.*, v.nom as ville_nom FROM besoin b JOIN ville v ON b.ville_id = v.id ORDER BY v.nom, b.produit");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->app->render('attributions/edit', [
            'pageTitle' => 'Modifier Attribution #' . $id,
            'attribution' => $attribution,
            'dons' => $dons,
            'besoins' => $besoins,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['error']);
    }

    /* ===================== MISE À JOUR ===================== */

    /**
     * Mettre à jour une attribution
     * POST /attributions/update/@id
     */
    public function update(int $id): void
    {
        $attribution = $this->attributionModel->getAttributionById($id);

        if (!$attribution) {
            $_SESSION['error'] = "Attribution non trouvée.";
            $this->app->redirect('/attributions');
            return;
        }

        $quantiteAttribuee = (int) ($this->app->request()->data['quantite_attribuee'] ?? 0);

        // Validation
        if ($quantiteAttribuee <= 0) {
            $_SESSION['error'] = "La quantité attribuée doit être positive.";
            $this->app->redirect('/attributions/edit/' . $id);
            return;
        }

        $result = $this->attributionModel->updateQuantite($id, $quantiteAttribuee);

        if ($result) {
            $_SESSION['success'] = "Attribution #$id mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'attribution.";
        }

        $this->app->redirect('/attributions');
    }

    /* ===================== SUPPRESSION ===================== */

    /**
     * Supprimer une attribution
     * GET /attributions/delete/@id
     */
    public function delete(int $id): void
    {
        $result = $this->attributionModel->deleteAttribution($id);

        if ($result) {
            $_SESSION['success'] = "Attribution #$id supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'attribution.";
        }

        $this->app->redirect('/attributions');
    }
}

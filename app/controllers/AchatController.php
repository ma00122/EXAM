<?php
namespace app\controllers;

use flight\Engine;
use app\models\Achat;
use app\models\Configuration;
use app\models\Ville;
use app\models\DonArgent;
use Flight;

/**
 * Controller AchatController - Gestion des achats
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class AchatController
{
    protected Engine $app;
    protected Achat $achatModel;
    protected Configuration $configModel;
    protected Ville $villeModel;
    protected DonArgent $donArgentModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->achatModel = new Achat(Flight::db());
        $this->configModel = new Configuration(Flight::db());
        $this->villeModel = new Ville(Flight::db());
        $this->donArgentModel = new DonArgent(Flight::db());
    }

    /* ===================== LISTE DES ACHATS ===================== */

    /**
     * Afficher la liste des achats (filtrable par ville)
     * GET /achats
     */
    public function index(): void
    {
        $villeId = $this->app->request()->query['ville_id'] ?? null;
        $statut = $this->app->request()->query['statut'] ?? null;
        
        // Récupérer les achats selon les filtres
        if (!empty($villeId)) {
            $achats = $this->achatModel->getByVille((int) $villeId);
        } elseif (!empty($statut)) {
            $achats = $this->achatModel->getByStatut($statut);
        } else {
            $achats = $this->achatModel->getAll();
        }

        // Récupérer les villes pour le filtre
        $villes = $this->villeModel->getAllVilles();

        // Statistiques
        $stats = [
            'total_achats' => count($achats),
            'montant_total' => array_sum(array_column($achats, 'montant_total')),
            'total_frais' => array_sum(array_column($achats, 'frais')),
            'frais_pourcentage' => $this->configModel->getFraisAchatPourcentage()
        ];

        $this->app->render('achats/index', [
            'pageTitle' => 'Liste des Achats',
            'achats' => $achats,
            'villes' => $villes,
            'stats' => $stats,
            'filterVilleId' => $villeId,
            'filterStatut' => $statut,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== FORMULAIRE CRÉATION ===================== */

    /**
     * Afficher le formulaire de création d'achat
     * GET /achats/create
     */
    public function create(): void
    {
        $villes = $this->villeModel->getAllVilles();
        
        // Récupérer les besoins
        $db = Flight::db();
        $stmt = $db->query("SELECT b.*, v.nom as ville_nom FROM besoin b JOIN ville v ON b.ville_id = v.id ORDER BY v.nom, b.produit");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $donsArgent = $this->donArgentModel->getAvailable();
        $fraisPourcentage = $this->configModel->getFraisAchatPourcentage();

        $this->app->render('achats/create', [
            'pageTitle' => 'Créer un Achat',
            'villes' => $villes,
            'besoins' => $besoins,
            'donsArgent' => $donsArgent,
            'fraisPourcentage' => $fraisPourcentage,
            'error' => $_SESSION['error'] ?? null,
            'old' => $_SESSION['old'] ?? []
        ]);

        unset($_SESSION['error'], $_SESSION['old']);
    }

    /* ===================== ENREGISTREMENT ===================== */

    /**
     * Enregistrer un nouvel achat
     * POST /achats/store
     */
    public function store(): void
    {
        $villeId = (int) ($this->app->request()->data['ville_id'] ?? 0);
        $besoinId = (int) ($this->app->request()->data['besoin_id'] ?? 0);
        $montantProduit = (float) ($this->app->request()->data['montant_produit'] ?? 0);
        $quantiteAchetee = (int) ($this->app->request()->data['quantite_achetee'] ?? 1);
        $dateAchat = trim($this->app->request()->data['date_achat'] ?? date('Y-m-d'));
        $notes = trim($this->app->request()->data['notes'] ?? '');

        // Validation
        $errors = [];
        if ($villeId <= 0) {
            $errors[] = "Veuillez sélectionner une ville.";
        }
        if ($besoinId <= 0) {
            $errors[] = "Veuillez sélectionner un besoin.";
        }
        if ($montantProduit <= 0) {
            $errors[] = "Le montant du produit doit être positif.";
        }
        if ($quantiteAchetee <= 0) {
            $errors[] = "La quantité doit être positive.";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $this->app->request()->data;
            $this->app->redirect('/achats/create');
            return;
        }

        // Calculer les frais
        $calcul = $this->configModel->calculerFraisAchat($montantProduit);

        $data = [
            'ville_id' => $villeId,
            'besoin_id' => $besoinId,
            'montant_produit' => $montantProduit,
            'frais' => $calcul['frais'],
            'montant_total' => $calcul['montant_total'],
            'quantite_achetee' => $quantiteAchetee,
            'date_achat' => $dateAchat,
            'statut' => 'en_attente',
            'notes' => $notes ?: null
        ];

        $result = $this->achatModel->create($data);

        if ($result) {
            $_SESSION['success'] = "Achat créé avec succès. Montant total: " . number_format($calcul['montant_total'], 0, ',', ' ') . " Ar (dont " . number_format($calcul['frais'], 0, ',', ' ') . " Ar de frais).";
        } else {
            $_SESSION['error'] = "Erreur lors de la création de l'achat.";
        }

        $this->app->redirect('/achats');
    }

    /* ===================== DÉTAIL ===================== */

    /**
     * Afficher les détails d'un achat
     * GET /achats/show/@id
     */
    public function show(int $id): void
    {
        $achat = $this->achatModel->getById($id);

        if (!$achat) {
            $_SESSION['error'] = "Achat non trouvé.";
            $this->app->redirect('/achats');
            return;
        }

        $this->app->render('achats/show', [
            'pageTitle' => 'Détail Achat #' . $id,
            'achat' => $achat
        ]);
    }

    /* ===================== FORMULAIRE ÉDITION ===================== */

    /**
     * Afficher le formulaire d'édition
     * GET /achats/edit/@id
     */
    public function edit(int $id): void
    {
        $achat = $this->achatModel->getById($id);

        if (!$achat) {
            $_SESSION['error'] = "Achat non trouvé.";
            $this->app->redirect('/achats');
            return;
        }

        $villes = $this->villeModel->getAllVilles();
        
        $db = Flight::db();
        $stmt = $db->query("SELECT b.*, v.nom as ville_nom FROM besoin b JOIN ville v ON b.ville_id = v.id ORDER BY v.nom, b.produit");
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $fraisPourcentage = $this->configModel->getFraisAchatPourcentage();

        $this->app->render('achats/edit', [
            'pageTitle' => 'Modifier Achat #' . $id,
            'achat' => $achat,
            'villes' => $villes,
            'besoins' => $besoins,
            'fraisPourcentage' => $fraisPourcentage,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['error']);
    }

    /* ===================== MISE À JOUR ===================== */

    /**
     * Mettre à jour un achat
     * POST /achats/update/@id
     */
    public function update(int $id): void
    {
        $achat = $this->achatModel->getById($id);

        if (!$achat) {
            $_SESSION['error'] = "Achat non trouvé.";
            $this->app->redirect('/achats');
            return;
        }

        $villeId = (int) ($this->app->request()->data['ville_id'] ?? 0);
        $besoinId = (int) ($this->app->request()->data['besoin_id'] ?? 0);
        $montantProduit = (float) ($this->app->request()->data['montant_produit'] ?? 0);
        $quantiteAchetee = (int) ($this->app->request()->data['quantite_achetee'] ?? 1);
        $dateAchat = trim($this->app->request()->data['date_achat'] ?? date('Y-m-d'));
        $statut = trim($this->app->request()->data['statut'] ?? 'en_attente');
        $notes = trim($this->app->request()->data['notes'] ?? '');

        // Calculer les frais
        $calcul = $this->configModel->calculerFraisAchat($montantProduit);

        $data = [
            'ville_id' => $villeId,
            'besoin_id' => $besoinId,
            'montant_produit' => $montantProduit,
            'frais' => $calcul['frais'],
            'montant_total' => $calcul['montant_total'],
            'quantite_achetee' => $quantiteAchetee,
            'date_achat' => $dateAchat,
            'statut' => $statut,
            'notes' => $notes ?: null
        ];

        $result = $this->achatModel->update($id, $data);

        if ($result) {
            $_SESSION['success'] = "Achat #$id mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'achat.";
        }

        $this->app->redirect('/achats');
    }

    /* ===================== VALIDATION ===================== */

    /**
     * Valider un achat
     * POST /achats/valider/@id
     */
    public function valider(int $id): void
    {
        $result = $this->achatModel->valider($id);

        if ($result) {
            $_SESSION['success'] = "Achat #$id validé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la validation de l'achat.";
        }

        $this->app->redirect('/achats');
    }

    /**
     * Valider un achat (alias)
     * GET /achats/validate/@id
     */
    public function validate(int $id): void
    {
        $this->valider($id);
    }

    /* ===================== ANNULATION ===================== */

    /**
     * Annuler un achat
     * GET /achats/cancel/@id
     */
    public function cancel(int $id): void
    {
        $result = $this->achatModel->cancel($id);

        if ($result) {
            $_SESSION['success'] = "Achat #$id annulé.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation de l'achat.";
        }

        $this->app->redirect('/achats');
    }

    /* ===================== SUPPRESSION ===================== */

    /**
     * Supprimer un achat
     * GET /achats/delete/@id
     */
    public function delete(int $id): void
    {
        $result = $this->achatModel->delete($id);

        if ($result) {
            $_SESSION['success'] = "Achat #$id supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'achat.";
        }

        $this->app->redirect('/achats');
    }

    /* ===================== STATISTIQUES PAR VILLE ===================== */

    /**
     * Statistiques des achats par ville
     * GET /achats/stats
     */
    public function stats(): void
    {
        $statsByVille = $this->achatModel->getStatsByVille();
        $countByStatut = $this->achatModel->getCountByStatut();

        $this->app->render('achats/stats', [
            'pageTitle' => 'Statistiques des Achats',
            'statsByVille' => $statsByVille,
            'countByStatut' => $countByStatut,
            'fraisPourcentage' => $this->configModel->getFraisAchatPourcentage()
        ]);
    }
}

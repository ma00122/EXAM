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
        // Récupérer la liste des villes pour le filtre
        $villes = Besoin::getAllVilles();

        // Récupérer l'ID de ville passé en paramètre GET (filtre)
        $selectedVille = (int) (Flight::request()->query->ville_id ?? 0);

        // Récupérer les besoins selon filtre ou tous
        if ($selectedVille > 0) {
            $besoins = Besoin::getBesoinsByVille($selectedVille);
        } else {
            $besoins = Besoin::getAllBesoinsWithDetails();
        }

        // Message flash
        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        Flight::render('besoins/index', [
            'pageTitle' => 'Liste des Besoins',
            'besoins' => $besoins,
            'villes' => $villes,
            'selected_ville' => $selectedVille,
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
     * GET /besoins/restants - Page des besoins restants (non satisfaits)
     * Bolton - Module Calculs Financiers
     */
    public function restants(): void
    {
        // Récupérer la liste des villes pour le filtre
        $villes = Besoin::getAllVilles();

        // Récupérer l'ID de ville passé en paramètre GET (filtre)
        $selectedVille = (int) (Flight::request()->query->ville_id ?? 0);

        // Récupérer les besoins restants selon filtre ou tous
        if ($selectedVille > 0) {
            $besoinsRestants = Besoin::getBesoinsRestantsParVille($selectedVille);
        } else {
            $besoinsRestants = Besoin::getBesoinsRestants();
        }

        // Récupérer les statistiques globales
        $stats = Besoin::getStatistiquesGlobales();

        // Récupérer le pourcentage de frais d'achat
        $fraisPourcentage = Besoin::getFraisAchatPourcentage();

        // Message flash
        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        Flight::render('besoins/restants', [
            'pageTitle' => 'Besoins Restants',
            'besoins_restants' => $besoinsRestants,
            'villes' => $villes,
            'selected_ville' => $selectedVille,
            'stats' => $stats,
            'frais_pourcentage' => $fraisPourcentage,
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * GET /recap/data - API JSON pour Ajax
     * Retourne les montants totaux pour actualisation dynamique
     * Bolton - Module Calculs Financiers
     */
    public function recapData(): void
    {
        $stats = Besoin::getStatistiquesGlobales();
        
        Flight::json([
            'total' => (float) ($stats['montant_total_global'] ?? 0),
            'satisfait' => (float) ($stats['montant_satisfait_global'] ?? 0),
            'restant' => (float) ($stats['montant_restant_global'] ?? 0),
            'quantite_demandee' => (int) ($stats['quantite_totale_demandee'] ?? 0),
            'quantite_recue' => (int) ($stats['quantite_totale_recue'] ?? 0),
            'quantite_restante' => (int) ($stats['quantite_totale_restante'] ?? 0),
            'nombre_besoins' => (int) ($stats['nombre_besoins'] ?? 0)
        ]);
    }

    /**
     * GET /recap/data/ville/@id - API JSON pour Ajax par ville
     * Bolton - Module Calculs Financiers
     */
    public function recapDataVille(int $villeId): void
    {
        $stats = Besoin::getStatistiquesParVille($villeId);
        
        Flight::json([
            'total' => (float) ($stats['montant_total'] ?? 0),
            'satisfait' => (float) ($stats['montant_satisfait'] ?? 0),
            'restant' => (float) ($stats['montant_restant'] ?? 0),
            'quantite_demandee' => (int) ($stats['quantite_demandee'] ?? 0),
            'quantite_recue' => (int) ($stats['quantite_recue'] ?? 0),
            'quantite_restante' => (int) ($stats['quantite_restante'] ?? 0),
            'nombre_besoins' => (int) ($stats['nombre_besoins'] ?? 0)
        ]);
    }

    /**
     * GET /besoins/calculer-achat/@id - Calculer l'achat possible pour un besoin
     * Bolton - Module Calculs Financiers avec Frais
     */
    public function calculerAchat(int $besoinId): void
    {
        $argentDisponible = (float) (Flight::request()->query->montant ?? 0);
        
        if ($argentDisponible <= 0) {
            Flight::json([
                'success' => false,
                'message' => 'Veuillez spécifier un montant d\'argent disponible (paramètre montant)'
            ], 400);
            return;
        }
        
        $resultat = Besoin::calculerAchatPossible($besoinId, $argentDisponible);
        Flight::json($resultat);
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

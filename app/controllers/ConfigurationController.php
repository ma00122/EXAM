<?php
namespace app\controllers;

use flight\Engine;
use app\models\Configuration;
use Flight;

/**
 * Controller ConfigurationController - Gestion des paramètres système
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class ConfigurationController
{
    protected Engine $app;
    protected Configuration $configModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->configModel = new Configuration(Flight::db());
    }

    /* ===================== LISTE DES CONFIGURATIONS ===================== */

    /**
     * Afficher la page de configuration
     * GET /configuration
     */
    public function index(): void
    {
        $configurations = $this->configModel->getAll();
        $fraisPourcentage = $this->configModel->getFraisAchatPourcentage();

        $this->app->render('configuration/index', [
            'pageTitle' => 'Configuration Système',
            'configurations' => $configurations,
            'fraisPourcentage' => $fraisPourcentage,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== MISE À JOUR FRAIS ===================== */

    /**
     * Mettre à jour le pourcentage de frais d'achat
     * POST /configuration/frais
     */
    public function updateFrais(): void
    {
        $pourcentage = (float) ($this->app->request()->data['frais_pourcentage'] ?? 0);

        // Validation
        if ($pourcentage < 0 || $pourcentage > 100) {
            $_SESSION['error'] = "Le pourcentage de frais doit être entre 0 et 100.";
            $this->app->redirect('/configuration');
            return;
        }

        $result = $this->configModel->setFraisAchatPourcentage($pourcentage);

        if ($result) {
            $_SESSION['success'] = "Le pourcentage de frais d'achat a été mis à jour à <strong>{$pourcentage}%</strong>.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du pourcentage de frais.";
        }

        $this->app->redirect('/configuration');
    }

    /* ===================== MISE À JOUR CONFIGURATION ===================== */

    /**
     * Mettre à jour une configuration
     * POST /configuration/update
     */
    public function update(): void
    {
        $cle = trim($this->app->request()->data['cle'] ?? '');
        $valeur = trim($this->app->request()->data['valeur'] ?? '');

        // Validation
        if (empty($cle) || empty($valeur)) {
            $_SESSION['error'] = "La clé et la valeur sont obligatoires.";
            $this->app->redirect('/configuration');
            return;
        }

        $result = $this->configModel->set($cle, $valeur);

        if ($result) {
            $_SESSION['success'] = "Configuration <strong>{$cle}</strong> mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la configuration.";
        }

        $this->app->redirect('/configuration');
    }

    /* ===================== CRÉATION CONFIGURATION ===================== */

    /**
     * Créer une nouvelle configuration
     * POST /configuration/create
     */
    public function create(): void
    {
        $cle = trim($this->app->request()->data['cle'] ?? '');
        $valeur = trim($this->app->request()->data['valeur'] ?? '');
        $description = trim($this->app->request()->data['description'] ?? '');

        // Validation
        if (empty($cle) || empty($valeur)) {
            $_SESSION['error'] = "La clé et la valeur sont obligatoires.";
            $this->app->redirect('/configuration');
            return;
        }

        // Vérifier si la clé existe déjà
        if ($this->configModel->get($cle) !== null) {
            $_SESSION['error'] = "La clé <strong>{$cle}</strong> existe déjà.";
            $this->app->redirect('/configuration');
            return;
        }

        $result = $this->configModel->create($cle, $valeur, $description ?: null);

        if ($result) {
            $_SESSION['success'] = "Configuration <strong>{$cle}</strong> créée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la configuration.";
        }

        $this->app->redirect('/configuration');
    }

    /* ===================== SUPPRESSION CONFIGURATION ===================== */

    /**
     * Supprimer une configuration
     * GET /configuration/delete/@cle
     */
    public function delete(string $cle): void
    {
        // Clés protégées qu'on ne peut pas supprimer
        $protectedKeys = ['frais_achat_pourcentage', 'montant_minimum_achat'];
        
        if (in_array($cle, $protectedKeys)) {
            $_SESSION['error'] = "La configuration <strong>{$cle}</strong> est protégée et ne peut pas être supprimée.";
            $this->app->redirect('/configuration');
            return;
        }

        $result = $this->configModel->delete($cle);

        if ($result) {
            $_SESSION['success'] = "Configuration <strong>{$cle}</strong> supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la configuration.";
        }

        $this->app->redirect('/configuration');
    }

    /* ===================== CALCUL PREVIEW FRAIS ===================== */

    /**
     * API pour calculer les frais d'achat (preview)
     * GET /configuration/calcul-frais?montant=xxx
     */
    public function calculFrais(): void
    {
        $montant = (float) ($this->app->request()->query['montant'] ?? 0);

        if ($montant <= 0) {
            $this->app->json([
                'success' => false,
                'error' => 'Montant invalide'
            ]);
            return;
        }

        $calcul = $this->configModel->calculerFraisAchat($montant);

        $this->app->json([
            'success' => true,
            'data' => $calcul
        ]);
    }
}

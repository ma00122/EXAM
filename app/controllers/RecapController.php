<?php
namespace app\controllers;

use flight\Engine;
use app\models\Achat;
use app\models\Attribution;
use app\models\Configuration;
use app\models\DonArgent;
use app\models\Don;
use app\models\Ville;
use Flight;

/**
 * Controller RecapController - Récapitulatif global et API Ajax
 * Projet BNGRC - Module Mahery (ETU004546)
 * 
 * Fournit la route GET /recap/data pour Ajax (utilisée par Sedra)
 */
class RecapController
{
    protected Engine $app;
    protected Achat $achatModel;
    protected Attribution $attributionModel;
    protected Configuration $configModel;
    protected DonArgent $donArgentModel;
    protected Don $donModel;
    protected Ville $villeModel;

    public function __construct(Engine $app)
    {
        $this->app = $app;
        $this->achatModel = new Achat(Flight::db());
        $this->attributionModel = new Attribution(Flight::db());
        $this->configModel = new Configuration(Flight::db());
        $this->donArgentModel = new DonArgent(Flight::db());
        $this->donModel = new Don(Flight::db());
        $this->villeModel = new Ville(Flight::db());
    }

    /* ===================== PAGE RÉCAPITULATIVE ===================== */

    /**
     * Afficher la page récapitulative
     * GET /recap
     */
    public function index(): void
    {
        $recap = $this->getRecapData();
        $besoinsRestants = $this->getBesoinsRestants();
        $villes = $this->villeModel->getAllVilles();

        $this->app->render('recap/index', [
            'pageTitle' => 'Récapitulatif Global BNGRC',
            'recap' => $recap,
            'besoinsRestants' => $besoinsRestants,
            'villes' => $villes,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['success'], $_SESSION['error']);
    }

    /* ===================== API AJAX /recap/data ===================== */

    /**
     * API pour récupérer les données de récap en JSON
     * GET /recap/data
     * 
     * Retourne:
     * {
     *   "total": xxx,
     *   "satisfait": xxx,
     *   "restant": xxx,
     *   "details": {...}
     * }
     */
    public function data(): void
    {
        $recap = $this->getRecapData();
        
        $this->app->json([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'total' => $recap['montant_total_besoins'],
            'satisfait' => $recap['montant_satisfait'],
            'restant' => $recap['montant_restant'],
            'details' => [
                'quantites' => [
                    'total_demandee' => $recap['quantite_totale_demandee'],
                    'total_recue' => $recap['quantite_totale_recue'],
                    'restante' => $recap['quantite_restante']
                ],
                'montants' => [
                    'total_besoins' => $recap['montant_total_besoins'],
                    'satisfait_nature' => $recap['montant_satisfait_nature'],
                    'satisfait_achats' => $recap['montant_satisfait_achats'],
                    'total_satisfait' => $recap['montant_satisfait'],
                    'restant' => $recap['montant_restant']
                ],
                'dons' => [
                    'total_nature' => $recap['total_dons_nature'],
                    'total_argent' => $recap['total_dons_argent'],
                    'argent_disponible' => $recap['argent_disponible']
                ],
                'taux_satisfaction' => $recap['taux_satisfaction'],
                'frais_pourcentage' => $recap['frais_pourcentage']
            ]
        ]);
    }

    /* ===================== API BESOINS RESTANTS ===================== */

    /**
     * API pour récupérer les besoins restants
     * GET /recap/besoins-restants
     */
    public function besoinsRestants(): void
    {
        $besoins = $this->getBesoinsRestants();
        
        $this->app->json([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'count' => count($besoins),
            'besoins' => $besoins
        ]);
    }

    /* ===================== RÉCAP PAR VILLE ===================== */

    /**
     * API pour récupérer le récap par ville
     * GET /recap/ville/@id
     */
    public function parVille(int $villeId): void
    {
        $ville = $this->villeModel->getVilleById($villeId);
        
        if (!$ville) {
            $this->app->json([
                'success' => false,
                'error' => 'Ville non trouvée'
            ]);
            return;
        }

        $recap = $this->getRecapVille($villeId);
        
        $this->app->json([
            'success' => true,
            'ville' => $ville,
            'recap' => $recap
        ]);
    }

    /* ===================== MÉTHODES PRIVÉES ===================== */

    /**
     * Calculer les données récapitulatives globales
     * @return array Données récap
     */
    private function getRecapData(): array
    {
        $db = Flight::db();

        // Montant total des besoins
        $stmt = $db->query("SELECT COALESCE(SUM(quantite * prix_unitaire), 0) as montant_total FROM besoin");
        $montantTotalBesoins = (float) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Quantité totale demandée
        $stmt = $db->query("SELECT COALESCE(SUM(quantite), 0) as total FROM besoin");
        $quantiteTotaleDemandee = (int) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Montant satisfait par dons nature (attributions)
        $stmt = $db->query("
            SELECT COALESCE(SUM(a.quantite_attribuee * b.prix_unitaire), 0) as montant
            FROM attribution a
            JOIN besoin b ON a.besoin_id = b.id
        ");
        $montantSatisfaitNature = (float) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Quantité totale reçue (attributions)
        $stmt = $db->query("SELECT COALESCE(SUM(quantite_attribuee), 0) FROM attribution");
        $quantiteTotaleRecue = (int) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Montant satisfait par achats validés
        $montantSatisfaitAchats = $this->achatModel->getTotalValidated();

        // Total satisfait
        $montantSatisfait = $montantSatisfaitNature + $montantSatisfaitAchats;

        // Montant restant
        $montantRestant = max(0, $montantTotalBesoins - $montantSatisfait);

        // Quantité restante
        $quantiteRestante = max(0, $quantiteTotaleDemandee - $quantiteTotaleRecue);

        // Total dons nature
        $totalDonsNature = $this->donModel->getTotalQuantite();

        // Total dons argent
        $totalDonsArgent = $this->donArgentModel->getTotal();
        $argentDisponible = $this->donArgentModel->getTotalDisponible();

        // Taux de satisfaction
        $tauxSatisfaction = $montantTotalBesoins > 0 
            ? round(($montantSatisfait / $montantTotalBesoins) * 100, 2) 
            : 0;

        // Frais d'achat
        $fraisPourcentage = $this->configModel->getFraisAchatPourcentage();

        return [
            'montant_total_besoins' => $montantTotalBesoins,
            'quantite_totale_demandee' => $quantiteTotaleDemandee,
            'montant_satisfait_nature' => $montantSatisfaitNature,
            'montant_satisfait_achats' => $montantSatisfaitAchats,
            'montant_satisfait' => $montantSatisfait,
            'montant_restant' => $montantRestant,
            'quantite_totale_recue' => $quantiteTotaleRecue,
            'quantite_restante' => $quantiteRestante,
            'total_dons_nature' => $totalDonsNature,
            'total_dons_argent' => $totalDonsArgent,
            'argent_disponible' => $argentDisponible,
            'taux_satisfaction' => $tauxSatisfaction,
            'frais_pourcentage' => $fraisPourcentage
        ];
    }

    /**
     * Récupérer les besoins restants (non entièrement satisfaits)
     * @return array Liste des besoins restants
     */
    private function getBesoinsRestants(): array
    {
        $db = Flight::db();

        $sql = "
            SELECT 
                b.id,
                b.produit,
                b.quantite as quantite_demandee,
                b.prix_unitaire,
                (b.quantite * b.prix_unitaire) as montant_total,
                v.nom as ville_nom,
                v.region,
                COALESCE(SUM(a.quantite_attribuee), 0) as quantite_recue,
                (b.quantite - COALESCE(SUM(a.quantite_attribuee), 0)) as quantite_restante,
                ((b.quantite - COALESCE(SUM(a.quantite_attribuee), 0)) * b.prix_unitaire) as montant_restant
            FROM besoin b
            LEFT JOIN ville v ON b.ville_id = v.id
            LEFT JOIN attribution a ON b.id = a.besoin_id
            GROUP BY b.id, b.produit, b.quantite, b.prix_unitaire, v.nom, v.region
            HAVING quantite_restante > 0
            ORDER BY v.nom, b.produit
        ";

        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le récap pour une ville spécifique
     * @param int $villeId ID de la ville
     * @return array Données récap de la ville
     */
    private function getRecapVille(int $villeId): array
    {
        $db = Flight::db();

        // Montant total des besoins de la ville
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM besoin WHERE ville_id = ?");
        $stmt->execute([$villeId]);
        $montantTotalBesoins = (float) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Montant satisfait par attributions
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(a.quantite_attribuee * b.prix_unitaire), 0)
            FROM attribution a
            JOIN besoin b ON a.besoin_id = b.id
            WHERE b.ville_id = ?
        ");
        $stmt->execute([$villeId]);
        $montantSatisfaitNature = (float) $stmt->fetch(\PDO::FETCH_COLUMN);

        // Montant satisfait par achats
        $stmt = $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achat WHERE ville_id = ? AND statut = 'valide'");
        $stmt->execute([$villeId]);
        $montantSatisfaitAchats = (float) $stmt->fetch(\PDO::FETCH_COLUMN);

        $montantSatisfait = $montantSatisfaitNature + $montantSatisfaitAchats;
        $montantRestant = max(0, $montantTotalBesoins - $montantSatisfait);

        return [
            'montant_total_besoins' => $montantTotalBesoins,
            'montant_satisfait_nature' => $montantSatisfaitNature,
            'montant_satisfait_achats' => $montantSatisfaitAchats,
            'montant_satisfait' => $montantSatisfait,
            'montant_restant' => $montantRestant,
            'taux_satisfaction' => $montantTotalBesoins > 0 
                ? round(($montantSatisfait / $montantTotalBesoins) * 100, 2) 
                : 0
        ];
    }
}

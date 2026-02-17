<?php
namespace app\controllers;

use flight\Engine;
use Flight;

/**
 * ResetController V3 - Reset Global
 * Projet BNGRC - Module Bolton (ETU004172)
 * 
 * Fonctionnalités :
 * - Supprimer toutes les attributions
 * - Supprimer tous les achats
 * - Restaurer quantités initiales des dons
 * - Restaurer quantités initiales des besoins
 * - Remettre dons argent à l'état initial
 */
class ResetController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Reset global - Remet tout à l'état initial
     * POST /reset/global
     */
    public function resetGlobal(): void
    {
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");

            // 1. Supprimer toutes les attributions
            $nbAttributions = $db->fetchRow("SELECT COUNT(*) as total FROM attribution")['total'] ?? 0;
            $db->runQuery("DELETE FROM attribution");

            // 2. Supprimer tous les achats
            $nbAchats = $db->fetchRow("SELECT COUNT(*) as total FROM achat")['total'] ?? 0;
            $db->runQuery("DELETE FROM achat");

            // 3. Restaurer quantités initiales des dons nature
            $db->runQuery("UPDATE don SET quantite = quantite_initiale");

            // 4. Restaurer quantités initiales des besoins
            $db->runQuery("UPDATE besoin SET quantite_satisfaite = 0");

            // 5. Remettre dons argent à l'état initial
            $db->runQuery("
                UPDATE don_argent 
                SET montant_utilise = 0, 
                    statut = 'disponible'
            ");

            $db->runQuery("COMMIT");

            // Retour JSON ou redirection selon le contexte
            if ($this->isAjaxRequest()) {
                Flight::json([
                    'success' => true,
                    'message' => 'Reset global effectué avec succès',
                    'details' => [
                        'attributions_supprimees' => $nbAttributions,
                        'achats_supprimes' => $nbAchats,
                        'dons_restaures' => true,
                        'besoins_restaures' => true,
                        'dons_argent_restaures' => true
                    ]
                ]);
            } else {
                $_SESSION['success'] = "Reset global effectué: {$nbAttributions} attribution(s) et {$nbAchats} achat(s) supprimés";
                Flight::redirect('/simulation');
            }

        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            
            if ($this->isAjaxRequest()) {
                Flight::json([
                    'success' => false,
                    'error' => 'Erreur lors du reset: ' . $e->getMessage()
                ], 500);
            } else {
                $_SESSION['error'] = 'Erreur lors du reset: ' . $e->getMessage();
                Flight::redirect('/simulation');
            }
        }
    }

    /**
     * Reset partiel - Seulement les attributions
     * POST /reset/attributions
     */
    public function resetAttributions(): void
    {
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");

            // Récupérer les attributions pour restaurer les quantités
            $attributions = $db->fetchAll("SELECT don_id, besoin_id, quantite_attribuee FROM attribution");
            
            foreach ($attributions as $attr) {
                // Restaurer quantité don
                $db->runQuery("
                    UPDATE don 
                    SET quantite = quantite + ?
                    WHERE id = ?
                ", [$attr['quantite_attribuee'], $attr['don_id']]);

                // Restaurer quantité besoin
                $db->runQuery("
                    UPDATE besoin 
                    SET quantite_satisfaite = quantite_satisfaite - ?
                    WHERE id = ?
                ", [$attr['quantite_attribuee'], $attr['besoin_id']]);
            }

            // Supprimer les attributions
            $nb = count($attributions);
            $db->runQuery("DELETE FROM attribution");

            $db->runQuery("COMMIT");

            Flight::json([
                'success' => true,
                'message' => "{$nb} attribution(s) supprimée(s)",
                'attributions_supprimees' => $nb
            ]);

        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            Flight::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset partiel - Seulement les achats
     * POST /reset/achats
     */
    public function resetAchats(): void
    {
        $db = Flight::db();
        
        try {
            $db->runQuery("START TRANSACTION");

            // Récupérer les achats validés pour restaurer les montants
            $achats = $db->fetchAll("
                SELECT besoin_id, don_id, quantite_achetee, montant_total 
                FROM achat 
                WHERE statut = 'valide'
            ");
            
            foreach ($achats as $achat) {
                // Restaurer quantité besoin
                if ($achat['besoin_id']) {
                    $db->runQuery("
                        UPDATE besoin 
                        SET quantite_satisfaite = quantite_satisfaite - ?
                        WHERE id = ?
                    ", [$achat['quantite_achetee'], $achat['besoin_id']]);
                }

                // Restaurer montant don argent si applicable
                if ($achat['don_id']) {
                    $db->runQuery("
                        UPDATE don_argent 
                        SET montant_utilise = montant_utilise - ?,
                            statut = CASE 
                                WHEN montant_utilise - ? <= 0 THEN 'disponible'
                                ELSE 'partiellement_utilise'
                            END
                        WHERE id = ?
                    ", [$achat['montant_total'], $achat['montant_total'], $achat['don_id']]);
                }
            }

            // Supprimer les achats
            $nb = $db->fetchRow("SELECT COUNT(*) as total FROM achat")['total'] ?? 0;
            $db->runQuery("DELETE FROM achat");

            $db->runQuery("COMMIT");

            Flight::json([
                'success' => true,
                'message' => "{$nb} achat(s) supprimé(s)",
                'achats_supprimes' => $nb
            ]);

        } catch (\Exception $e) {
            $db->runQuery("ROLLBACK");
            Flight::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier l'état actuel du système
     * GET /reset/status
     */
    public function getStatus(): void
    {
        $db = Flight::db();
        
        $status = [
            'attributions' => $db->fetchRow("SELECT COUNT(*) as total FROM attribution")['total'] ?? 0,
            'achats' => $db->fetchRow("SELECT COUNT(*) as total FROM achat")['total'] ?? 0,
            'achats_valides' => $db->fetchRow("SELECT COUNT(*) as total FROM achat WHERE statut = 'valide'")['total'] ?? 0,
            'dons_utilises' => $db->fetchRow("
                SELECT COUNT(*) as total FROM don WHERE quantite < quantite_initiale
            ")['total'] ?? 0,
            'besoins_satisfaits' => $db->fetchRow("
                SELECT COUNT(*) as total FROM besoin WHERE quantite_satisfaite > 0
            ")['total'] ?? 0,
            'dons_argent_utilises' => $db->fetchRow("
                SELECT COUNT(*) as total FROM don_argent WHERE montant_utilise > 0
            ")['total'] ?? 0,
            'peut_reset' => true
        ];

        $status['systeme_modifie'] = (
            $status['attributions'] > 0 || 
            $status['achats'] > 0 || 
            $status['dons_utilises'] > 0 || 
            $status['besoins_satisfaits'] > 0
        );

        Flight::json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Vérifie si c'est une requête AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

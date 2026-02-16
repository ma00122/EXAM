<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Achat - Gestion des achats avec dons argent
 * Projet BNGRC - Module Bolton (Calculs Financiers)
 */
class Achat
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Insérer un nouvel achat
     * @param int $villeId ID de la ville
     * @param int $besoinId ID du besoin
     * @param float $montantProduit Montant du produit acheté
     * @param float $frais Frais appliqués
     * @param float $montantTotal Montant total (produit + frais)
     * @param int $quantiteAchetee Quantité achetée
     * @param int|null $donId ID du don utilisé (optionnel)
     * @return int|false ID de l'achat créé ou false en cas d'échec
     */
    public function insertAchat(
        int $villeId, 
        int $besoinId, 
        float $montantProduit, 
        float $frais, 
        float $montantTotal, 
        int $quantiteAchetee,
        ?int $donId = null
    ): int|false {
        $sql = "INSERT INTO achat (ville_id, besoin_id, don_id, montant_produit, frais, montant_total, quantite_achetee, date_achat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $villeId, 
            $besoinId, 
            $donId, 
            $montantProduit, 
            $frais, 
            $montantTotal, 
            $quantiteAchetee
        ]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Créer un achat à partir des résultats du calcul
     * @param array $calculAchat Résultat de Besoin::calculerAchatPossible()
     * @param int|null $donId ID du don utilisé
     * @return int|false ID de l'achat créé
     */
    public function creerAchatDepuisCalcul(array $calculAchat, ?int $donId = null): int|false
    {
        if (!isset($calculAchat['success']) || !$calculAchat['success']) {
            return false;
        }
        
        // Récupérer le besoin pour obtenir ville_id
        $besoin = Besoin::getBesoinById($calculAchat['besoin_id']);
        if (!$besoin) {
            return false;
        }
        
        return $this->insertAchat(
            (int) $besoin['ville_id'],
            (int) $calculAchat['besoin_id'],
            (float) $calculAchat['montant_produit'],
            (float) $calculAchat['frais'],
            (float) $calculAchat['montant_total'],
            (int) $calculAchat['quantite_achetee'],
            $donId
        );
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer tous les achats avec détails
     * @return array Liste des achats
     */
    public function getAllAchats(): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, 
                       b.produit as besoin_produit,
                       b.prix_unitaire,
                       d.type_produit as don_type
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN don d ON a.don_id = d.id
                ORDER BY a.date_achat DESC, a.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats par ville
     * @param int $villeId ID de la ville
     * @return array Liste des achats pour cette ville
     */
    public function getAchatsByVille(int $villeId): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, 
                       b.produit as besoin_produit,
                       b.prix_unitaire,
                       d.type_produit as don_type
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN don d ON a.don_id = d.id
                WHERE a.ville_id = ?
                ORDER BY a.date_achat DESC, a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un achat par son ID
     * @param int $id ID de l'achat
     * @return array|false Données de l'achat ou false si non trouvé
     */
    public function getAchatById(int $id): array|false
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, 
                       b.produit as besoin_produit,
                       b.prix_unitaire,
                       d.type_produit as don_type
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN don d ON a.don_id = d.id
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats par don
     * @param int $donId ID du don
     * @return array Liste des achats pour ce don
     */
    public function getAchatsByDon(int $donId): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, 
                       b.produit as besoin_produit
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.don_id = ?
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$donId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le total des achats
     * @return array Statistiques des achats
     */
    public function getStatistiquesAchats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as nombre_achats,
                    COALESCE(SUM(montant_produit), 0) as total_produits,
                    COALESCE(SUM(frais), 0) as total_frais,
                    COALESCE(SUM(montant_total), 0) as montant_total_global,
                    COALESCE(SUM(quantite_achetee), 0) as quantite_totale
                FROM achat";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupérer le total des achats par ville
     * @param int $villeId ID de la ville
     * @return array Statistiques des achats pour cette ville
     */
    public function getStatistiquesAchatsParVille(int $villeId): array
    {
        $sql = "SELECT 
                    COUNT(*) as nombre_achats,
                    COALESCE(SUM(montant_produit), 0) as total_produits,
                    COALESCE(SUM(frais), 0) as total_frais,
                    COALESCE(SUM(montant_total), 0) as montant_total,
                    COALESCE(SUM(quantite_achetee), 0) as quantite_totale
                FROM achat WHERE ville_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer un achat
     * @param int $id ID de l'achat
     * @return bool Succès de la suppression
     */
    public function deleteAchat(int $id): bool
    {
        $sql = "DELETE FROM achat WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer les achats d'un don
     * @param int $donId ID du don
     * @return bool Succès de la suppression
     */
    public function deleteAchatsByDon(int $donId): bool
    {
        $sql = "DELETE FROM achat WHERE don_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$donId]);
    }
}

<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Achat - Gestion des achats effectués avec les dons en argent
 * Projet BNGRC - Module Sedra
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
     * Créer un nouvel achat
     * @param int $villeId ID de la ville
     * @param int $besoinId ID du besoin
     * @param int $donId ID du don argent utilisé
     * @param float $montantProduit Montant du produit acheté
     * @param float $frais Frais d'achat appliqués
     * @param float $montantTotal Montant total (produit + frais)
     * @return int|false ID de l'achat créé ou false
     */
    public function createAchat(int $villeId, int $besoinId, int $donId, float $montantProduit, float $frais, float $montantTotal): int|false
    {
        $sql = "INSERT INTO achat (ville_id, besoin_id, don_id, montant_produit, frais, montant_total, date_achat) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$villeId, $besoinId, $donId, $montantProduit, $frais, $montantTotal]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
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
                       b.quantite as besoin_quantite,
                       d.type_produit as don_type
                FROM achat a
                LEFT JOIN ville v ON a.ville_id = v.id
                LEFT JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN don d ON a.don_id = d.id
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats par ville
     * @param int $villeId ID de la ville
     * @return array Liste des achats
     */
    public function getAchatsByVille(int $villeId): array
    {
        $sql = "SELECT a.*, b.produit as besoin_produit 
                FROM achat a
                LEFT JOIN besoin b ON a.besoin_id = b.id
                WHERE a.ville_id = ?
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un achat par ID
     * @param int $id ID de l'achat
     * @return array|false Achat ou false
     */
    public function getAchatById(int $id): array|false
    {
        $sql = "SELECT * FROM achat WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calculer le total des achats
     * @return float Montant total
     */
    public function getTotalAchats(): float
    {
        $sql = "SELECT COALESCE(SUM(montant_total), 0) as total FROM achat";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total'];
    }

    /**
     * Calculer le total des frais
     * @return float Total frais
     */
    public function getTotalFrais(): float
    {
        $sql = "SELECT COALESCE(SUM(frais), 0) as total FROM achat";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total'];
    }

    /**
     * Compter le nombre d'achats
     * @return int Nombre d'achats
     */
    public function countAchats(): int
    {
        $sql = "SELECT COUNT(*) as count FROM achat";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['count'];
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer tous les achats
     * @return bool Succès
     */
    public function deleteAll(): bool
    {
        $sql = "DELETE FROM achat";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Supprimer un achat par ID
     * @param int $id ID de l'achat
     * @return bool Succès
     */
    public function deleteAchat(int $id): bool
    {
        $sql = "DELETE FROM achat WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}

<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Attribution - Gestion des attributions de dons aux besoins
 * Projet BNGRC - Module Mahery
 */
class Attribution
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Créer une nouvelle attribution
     * @param int $donId ID du don
     * @param int $besoinId ID du besoin
     * @param int $quantiteAttribuee Quantité attribuée
     * @return int|false ID de l'attribution créée ou false en cas d'échec
     */
    public function createAttribution(int $donId, int $besoinId, int $quantiteAttribuee): int|false
    {
        $sql = "INSERT INTO attribution (don_id, besoin_id, quantite_attribuee) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$donId, $besoinId, $quantiteAttribuee]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer toutes les attributions avec détails dons et besoins
     * @return array Liste des attributions
     */
    public function getAllAttributions(): array
    {
        $sql = "SELECT a.*, 
                       d.type_produit, d.quantite as don_quantite, d.date_saisie as don_date,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.ville_id,
                       v.nom as ville_nom
                FROM attribution a
                JOIN don d ON a.don_id = d.id
                JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN ville v ON b.ville_id = v.id
                ORDER BY a.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les attributions par besoin
     * @param int $besoinId ID du besoin
     * @return array Liste des attributions pour ce besoin
     */
    public function getAttributionsByBesoin(int $besoinId): array
    {
        $sql = "SELECT a.*, d.type_produit, d.quantite as don_quantite, d.date_saisie as don_date
                FROM attribution a
                JOIN don d ON a.don_id = d.id
                WHERE a.besoin_id = ?
                ORDER BY a.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$besoinId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les attributions par don
     * @param int $donId ID du don
     * @return array Liste des attributions pour ce don
     */
    public function getAttributionsByDon(int $donId): array
    {
        $sql = "SELECT a.*, b.produit, b.quantite as besoin_quantite, b.ville_id
                FROM attribution a
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.don_id = ?
                ORDER BY a.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$donId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une attribution par son ID
     * @param int $id ID de l'attribution
     * @return array|false Données de l'attribution ou false si non trouvée
     */
    public function getAttributionById(int $id): array|false
    {
        $sql = "SELECT a.*, d.type_produit, d.quantite as don_quantite
                FROM attribution a
                JOIN don d ON a.don_id = d.id
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les attributions par ville
     * @param int $villeId ID de la ville
     * @return array Liste des attributions pour cette ville
     */
    public function getAttributionsByVille(int $villeId): array
    {
        $sql = "SELECT a.*, 
                       d.type_produit, d.quantite as don_quantite, d.date_saisie as don_date,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.ville_id,
                       v.nom as ville_nom
                FROM attribution a
                JOIN don d ON a.don_id = d.id
                JOIN besoin b ON a.besoin_id = b.id
                LEFT JOIN ville v ON b.ville_id = v.id
                WHERE b.ville_id = ?
                ORDER BY a.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour la quantité d'une attribution
     * @param int $id ID de l'attribution
     * @param int $quantiteAttribuee Nouvelle quantité
     * @return bool Succès ou échec
     */
    public function updateQuantite(int $id, int $quantiteAttribuee): bool
    {
        $sql = "UPDATE attribution SET quantite_attribuee = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantiteAttribuee, $id]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer une attribution
     * @param int $id ID de l'attribution
     * @return bool Succès ou échec
     */
    public function deleteAttribution(int $id): bool
    {
        $sql = "DELETE FROM attribution WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer toutes les attributions d'un don
     * @param int $donId ID du don
     * @return bool Succès ou échec
     */
    public function deleteByDon(int $donId): bool
    {
        $sql = "DELETE FROM attribution WHERE don_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$donId]);
    }

    /**
     * Supprimer toutes les attributions d'un besoin
     * @param int $besoinId ID du besoin
     * @return bool Succès ou échec
     */
    public function deleteByBesoin(int $besoinId): bool
    {
        $sql = "DELETE FROM attribution WHERE besoin_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$besoinId]);
    }

    /**
     * Supprimer toutes les attributions (reset)
     * @return bool Succès ou échec
     */
    public function deleteAll(): bool
    {
        $sql = "DELETE FROM attribution";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Calculer la somme des attributions pour un besoin
     * @param int $besoinId ID du besoin
     * @return int Quantité totale attribuée
     */
    public function getTotalAttribueByBesoin(int $besoinId): int
    {
        $sql = "SELECT COALESCE(SUM(quantite_attribuee), 0) FROM attribution WHERE besoin_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$besoinId]);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Calculer la somme des attributions pour un don
     * @param int $donId ID du don
     * @return int Quantité totale attribuée
     */
    public function getTotalAttribueByDon(int $donId): int
    {
        $sql = "SELECT COALESCE(SUM(quantite_attribuee), 0) FROM attribution WHERE don_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$donId]);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Compter le nombre total d'attributions
     * @return int Nombre d'attributions
     */
    public function countAttributions(): int
    {
        $sql = "SELECT COUNT(*) FROM attribution";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer les statistiques d'attribution par produit
     * @return array Statistiques par produit
     */
    public function getStatsByProduit(): array
    {
        $sql = "SELECT d.type_produit, 
                       SUM(a.quantite_attribuee) as total_attribue,
                       COUNT(DISTINCT a.besoin_id) as nombre_besoins_couverts
                FROM attribution a
                JOIN don d ON a.don_id = d.id
                GROUP BY d.type_produit
                ORDER BY d.type_produit ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

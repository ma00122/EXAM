<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Don - Gestion des dons
 * Projet BNGRC - Module Mahery
 */
class Don
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Insérer un nouveau don
     * @param string $typeProduit Type/nom du produit
     * @param int $quantite Quantité donnée
     * @param string|null $dateSaisie Date de saisie (défaut: aujourd'hui)
     * @return int|false ID du don inséré ou false en cas d'échec
     */
    public function insertDon(string $typeProduit, int $quantite, ?string $dateSaisie = null): int|false
    {
        $dateSaisie = $dateSaisie ?? date('Y-m-d');
        
        $sql = "INSERT INTO don (type_produit, quantite, date_saisie) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$typeProduit, $quantite, $dateSaisie]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer tous les dons
     * @return array Liste des dons
     */
    public function getAllDons(): array
    {
        $sql = "SELECT * FROM don ORDER BY date_saisie ASC, id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les dons triés par date (pour simulation)
     * @return array Liste des dons ordonnés par date_saisie ASC
     */
    public function getDonsOrderByDate(): array
    {
        $sql = "SELECT * FROM don ORDER BY date_saisie ASC, id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un don par son ID
     * @param int $id ID du don
     * @return array|false Données du don ou false si non trouvé
     */
    public function getDonById(int $id): array|false
    {
        $sql = "SELECT * FROM don WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les dons par type de produit
     * @param string $typeProduit Type de produit
     * @return array Liste des dons pour ce produit
     */
    public function getDonsByTypeProduit(string $typeProduit): array
    {
        $sql = "SELECT * FROM don WHERE type_produit = ? ORDER BY date_saisie ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$typeProduit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les types de produits distincts
     * @return array Liste des types de produits
     */
    public function getAllTypesProduits(): array
    {
        $sql = "SELECT DISTINCT type_produit FROM don ORDER BY type_produit ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour la quantité d'un don
     * @param int $id ID du don
     * @param int $quantite Nouvelle quantité
     * @return bool Succès ou échec
     */
    public function updateQuantite(int $id, int $quantite): bool
    {
        $sql = "UPDATE don SET quantite = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantite, $id]);
    }

    /**
     * Mettre à jour un don complet
     * @param int $id ID du don
     * @param string $typeProduit Type de produit
     * @param int $quantite Quantité
     * @param string $dateSaisie Date de saisie
     * @return bool Succès ou échec
     */
    public function updateDon(int $id, string $typeProduit, int $quantite, string $dateSaisie): bool
    {
        $sql = "UPDATE don SET type_produit = ?, quantite = ?, date_saisie = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$typeProduit, $quantite, $dateSaisie, $id]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer un don
     * @param int $id ID du don
     * @return bool Succès ou échec
     */
    public function deleteDon(int $id): bool
    {
        $sql = "DELETE FROM don WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Récupérer la quantité totale de dons par produit
     * @return array Statistiques par produit
     */
    public function getTotalDonsByProduit(): array
    {
        $sql = "SELECT type_produit, SUM(quantite) as total_quantite, COUNT(*) as nombre_dons 
                FROM don 
                GROUP BY type_produit 
                ORDER BY type_produit ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la quantité totale de tous les dons
     * @return int Quantité totale
     */
    public function getTotalQuantite(): int
    {
        $sql = "SELECT COALESCE(SUM(quantite), 0) as total FROM don";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Compter le nombre total de dons
     * @return int Nombre de dons
     */
    public function countDons(): int
    {
        $sql = "SELECT COUNT(*) FROM don";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Vérifier si un don a des attributions
     * @param int $id ID du don
     * @return bool True si le don a des attributions
     */
    public function hasAttributions(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM attribution WHERE don_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN) > 0;
    }

    /**
     * Récupérer la quantité restante d'un don (non attribuée)
     * @param int $id ID du don
     * @return int Quantité restante
     */
    public function getQuantiteRestante(int $id): int
    {
        $don = $this->getDonById($id);
        if (!$don) {
            return 0;
        }

        $sql = "SELECT COALESCE(SUM(quantite_attribuee), 0) FROM attribution WHERE don_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $attribuee = (int) $stmt->fetch(PDO::FETCH_COLUMN);

        return max(0, $don['quantite'] - $attribuee);
    }
}

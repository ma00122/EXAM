<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Achat - Gestion des achats effectués avec les dons argent
 * Projet BNGRC - Module Mahery (ETU004546)
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
     * @param array $data Données de l'achat
     * @return int|false ID de l'achat créé ou false en cas d'échec
     */
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO achat (ville_id, besoin_id, don_argent_id, montant_produit, frais, montant_total, quantite_achetee, date_achat, statut, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['ville_id'],
            $data['besoin_id'],
            $data['don_argent_id'] ?? null,
            $data['montant_produit'],
            $data['frais'],
            $data['montant_total'],
            $data['quantite_achetee'] ?? 1,
            $data['date_achat'] ?? date('Y-m-d'),
            $data['statut'] ?? 'en_attente',
            $data['notes'] ?? null
        ]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer tous les achats
     * @return array Liste des achats
     */
    public function getAll(): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, v.region,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.prix_unitaire
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                ORDER BY a.date_achat DESC, a.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un achat par son ID
     * @param int $id ID de l'achat
     * @return array|false Données de l'achat ou false si non trouvé
     */
    public function getById(int $id): array|false
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, v.region,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.prix_unitaire
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats par ville
     * @param int $villeId ID de la ville
     * @return array Liste des achats pour cette ville
     */
    public function getByVille(int $villeId): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, v.region,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.prix_unitaire
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.ville_id = ?
                ORDER BY a.date_achat DESC, a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats par statut
     * @param string $statut Statut (en_attente, valide, annule)
     * @return array Liste des achats
     */
    public function getByStatut(string $statut): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, v.region,
                       b.produit as besoin_produit, b.quantite as besoin_quantite, b.prix_unitaire
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.statut = ?
                ORDER BY a.date_achat DESC, a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les achats validés
     * @return array Liste des achats validés
     */
    public function getValidated(): array
    {
        return $this->getByStatut('valide');
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour un achat
     * @param int $id ID de l'achat
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE achat SET 
                    ville_id = ?, 
                    besoin_id = ?, 
                    montant_produit = ?, 
                    frais = ?, 
                    montant_total = ?, 
                    quantite_achetee = ?,
                    date_achat = ?, 
                    statut = ?, 
                    notes = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['ville_id'],
            $data['besoin_id'],
            $data['montant_produit'],
            $data['frais'],
            $data['montant_total'],
            $data['quantite_achetee'] ?? 1,
            $data['date_achat'],
            $data['statut'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Valider un achat
     * @param int $id ID de l'achat
     * @return bool Succès ou échec
     */
    public function validate(int $id): bool
    {
        $sql = "UPDATE achat SET statut = 'valide' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Annuler un achat
     * @param int $id ID de l'achat
     * @return bool Succès ou échec
     */
    public function cancel(int $id): bool
    {
        $sql = "UPDATE achat SET statut = 'annule' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer un achat
     * @param int $id ID de l'achat
     * @return bool Succès ou échec
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM achat WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer tous les achats en attente
     * @return bool Succès ou échec
     */
    public function deleteAllPending(): bool
    {
        $sql = "DELETE FROM achat WHERE statut = 'en_attente'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Récupérer le total des achats validés
     * @return float Montant total
     */
    public function getTotalValidated(): float
    {
        $sql = "SELECT COALESCE(SUM(montant_total), 0) FROM achat WHERE statut = 'valide'";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer le total des frais sur achats validés
     * @return float Total des frais
     */
    public function getTotalFrais(): float
    {
        $sql = "SELECT COALESCE(SUM(frais), 0) FROM achat WHERE statut = 'valide'";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer le nombre d'achats par statut
     * @return array Statistiques par statut
     */
    public function getCountByStatut(): array
    {
        $sql = "SELECT statut, COUNT(*) as nombre, COALESCE(SUM(montant_total), 0) as total
                FROM achat 
                GROUP BY statut";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les statistiques par ville
     * @return array Statistiques par ville
     */
    public function getStatsByVille(): array
    {
        $sql = "SELECT 
                    v.id as ville_id,
                    v.nom as ville_nom,
                    v.region,
                    COUNT(a.id) as nombre_achats,
                    COALESCE(SUM(a.montant_produit), 0) as total_montant_produit,
                    COALESCE(SUM(a.frais), 0) as total_frais,
                    COALESCE(SUM(a.montant_total), 0) as total_general
                FROM ville v
                LEFT JOIN achat a ON v.id = a.ville_id AND a.statut = 'valide'
                GROUP BY v.id, v.nom, v.region
                ORDER BY v.nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compter le nombre total d'achats
     * @return int Nombre d'achats
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM achat";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }
}

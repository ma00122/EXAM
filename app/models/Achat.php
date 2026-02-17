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
                       v.region as region,
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
                       v.region as region,
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
                       v.region as region,
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
                       v.region as region,
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

    /* ===================== ALIAS MÉTHODES ===================== */

    /**
     * Alias pour getAllAchats
     * @return array Liste des achats
     */
    public function getAll(): array
    {
        return $this->getAllAchats();
    }

    /**
     * Alias pour getAchatById
     * @param int $id ID de l'achat
     * @return array|false Données de l'achat
     */
    public function getById(int $id): array|false
    {
        return $this->getAchatById($id);
    }

    /**
     * Alias pour getAchatsByVille
     * @param int $villeId ID de la ville
     * @return array Liste des achats
     */
    public function getByVille(int $villeId): array
    {
        return $this->getAchatsByVille($villeId);
    }

    /**
     * Récupérer les achats par statut
     * @param string $statut Statut de l'achat
     * @return array Liste des achats
     */
    public function getByStatut(string $statut): array
    {
        $sql = "SELECT a.*, 
                       v.nom as ville_nom, 
                       v.region as region,
                       b.produit as besoin_produit
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                JOIN besoin b ON a.besoin_id = b.id
                WHERE a.statut = ?
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Alias pour insertAchat
     * @param array $data Données de l'achat
     * @return int|false ID de l'achat créé
     */
    public function create(array $data): int|false
    {
        return $this->insertAchat(
            (int) ($data['ville_id'] ?? 0),
            (int) ($data['besoin_id'] ?? 0),
            (float) ($data['montant_produit'] ?? 0),
            (float) ($data['frais'] ?? 0),
            (float) ($data['montant_total'] ?? 0),
            (int) ($data['quantite_achetee'] ?? 1),
            isset($data['don_id']) ? (int) $data['don_id'] : null
        );
    }

    /**
     * Créer un achat simplifié (pour simulation)
     */
    public function createAchat(int $villeId, int $besoinId, int $donId, float $montantProduit, float $frais, float $montantTotal): int|false
    {
        $sql = "INSERT INTO achat (ville_id, besoin_id, don_id, montant_produit, frais, montant_total, quantite_achetee, date_achat, statut) 
                VALUES (?, ?, ?, ?, ?, ?, 1, CURRENT_DATE, 'valide')";
        $stmt = $this->db->prepare($sql);
        $donIdParam = $donId > 0 ? $donId : null;
        $result = $stmt->execute([$villeId, $besoinId, $donIdParam, $montantProduit, $frais, $montantTotal]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Alias pour deleteAchat
     */
    public function delete(int $id): bool
    {
        return $this->deleteAchat($id);
    }

    /**
     * Récupérer le total des achats validés
     */
    public function getTotalValidated(): float
    {
        $sql = "SELECT COALESCE(SUM(montant_total), 0) FROM achat WHERE statut = 'valide'";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Mettre à jour un achat
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
            $data['quantite_achetee'],
            $data['date_achat'],
            $data['statut'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Valider un achat
     */
    public function valider(int $id): bool
    {
        $sql = "UPDATE achat SET statut = 'valide' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Annuler un achat
     */
    public function cancel(int $id): bool
    {
        $sql = "UPDATE achat SET statut = 'annule' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Récupérer les statistiques par ville
     */
    public function getStatsByVille(): array
    {
        $sql = "SELECT v.nom as ville_nom, 
                       v.region as region,
                       COUNT(*) as nombre_achats,
                       COALESCE(SUM(a.montant_produit), 0) as total_montant_produit,
                       COALESCE(SUM(a.frais), 0) as total_frais,
                       COALESCE(SUM(a.montant_total), 0) as total_general
                FROM achat a
                JOIN ville v ON a.ville_id = v.id
                GROUP BY v.id, v.nom, v.region
                ORDER BY total_general DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le nombre d'achats par statut avec montants
     */
    public function getCountByStatut(): array
    {
        $sql = "SELECT statut, 
                       COUNT(*) as nombre, 
                       COALESCE(SUM(montant_total), 0) as total 
                FROM achat 
                GROUP BY statut
                ORDER BY 
                    CASE statut 
                        WHEN 'valide' THEN 1 
                        WHEN 'en_attente' THEN 2 
                        WHEN 'annule' THEN 3 
                        ELSE 4 
                    END";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

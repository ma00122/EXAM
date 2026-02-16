<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Besoin - Gestion des besoins
 * Projet BNGRC - Module Sedra (utilisé aussi par Mahery)
 */
class Besoin
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Insérer un nouveau besoin
     * @param int $villeId ID de la ville
     * @param string $type Type de besoin (nature/materiaux/argent)
     * @param string $produit Nom du produit
     * @param int $quantite Quantité demandée
     * @param float $prixUnitaire Prix unitaire
     * @param string|null $dateSaisie Date de saisie (défaut: aujourd'hui)
     * @return int|false ID du besoin inséré ou false en cas d'échec
     */
    public function insertBesoin(int $villeId, string $type, string $produit, int $quantite, float $prixUnitaire, ?string $dateSaisie = null): int|false
    {
        $dateSaisie = $dateSaisie ?? date('Y-m-d');
        
        $sql = "INSERT INTO besoin (ville_id, type, produit, quantite, prix_unitaire, date_saisie) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$villeId, $type, $produit, $quantite, $prixUnitaire, $dateSaisie]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer tous les besoins
     * @return array Liste des besoins avec info ville
     */
    public function getAllBesoins(): array
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                ORDER BY b.date_saisie ASC, b.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins ordonnés par date (pour simulation)
     * @return array Liste des besoins ordonnés par date_saisie ASC
     */
    public function getBesoinsOrderByDate(): array
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                ORDER BY b.date_saisie ASC, b.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins par ville
     * @param int $villeId ID de la ville
     * @return array Liste des besoins pour cette ville
     */
    public function getBesoinsByVille(int $villeId): array
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                WHERE b.ville_id = ?
                ORDER BY b.date_saisie ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins par type
     * @param string $type Type de besoin
     * @return array Liste des besoins pour ce type
     */
    public function getBesoinsByType(string $type): array
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                WHERE b.type = ?
                ORDER BY b.date_saisie ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins par produit
     * @param string $produit Nom du produit
     * @return array Liste des besoins pour ce produit
     */
    public function getBesoinsByProduit(string $produit): array
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                WHERE b.produit = ?
                ORDER BY b.date_saisie ASC, b.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un besoin par son ID
     * @param int $id ID du besoin
     * @return array|false Données du besoin ou false si non trouvé
     */
    public function getBesoinById(int $id): array|false
    {
        $sql = "SELECT b.*, v.nom as ville_nom, v.region as ville_region,
                       (b.quantite * b.prix_unitaire) as valeur_totale
                FROM besoin b
                LEFT JOIN ville v ON b.ville_id = v.id
                WHERE b.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les types distincts
     * @return array Liste des types
     */
    public function getAllTypes(): array
    {
        $sql = "SELECT DISTINCT type FROM besoin ORDER BY type ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer tous les produits distincts
     * @return array Liste des produits
     */
    public function getAllProduits(): array
    {
        $sql = "SELECT DISTINCT produit FROM besoin ORDER BY produit ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour la quantité d'un besoin
     * Note: Le prix unitaire n'est PAS modifiable après insertion
     * @param int $id ID du besoin
     * @param int $quantite Nouvelle quantité
     * @return bool Succès ou échec
     */
    public function updateQuantite(int $id, int $quantite): bool
    {
        $sql = "UPDATE besoin SET quantite = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantite, $id]);
    }

    /**
     * Mettre à jour un besoin (sans modifier le prix unitaire)
     * @param int $id ID du besoin
     * @param int $villeId ID de la ville
     * @param string $type Type
     * @param string $produit Produit
     * @param int $quantite Quantité
     * @return bool Succès ou échec
     */
    public function updateBesoin(int $id, int $villeId, string $type, string $produit, int $quantite): bool
    {
        $sql = "UPDATE besoin SET ville_id = ?, type = ?, produit = ?, quantite = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$villeId, $type, $produit, $quantite, $id]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer un besoin
     * @param int $id ID du besoin
     * @return bool Succès ou échec
     */
    public function deleteBesoin(int $id): bool
    {
        $sql = "DELETE FROM besoin WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Compter le nombre total de besoins
     * @return int Nombre de besoins
     */
    public function countBesoins(): int
    {
        $sql = "SELECT COUNT(*) FROM besoin";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Calculer la valeur totale de tous les besoins
     * @return float Valeur totale
     */
    public function getTotalValeur(): float
    {
        $sql = "SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM besoin";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer les statistiques par ville
     * @return array Statistiques par ville
     */
    public function getStatsByVille(): array
    {
        $sql = "SELECT v.id, v.nom, v.region,
                       COUNT(b.id) as nombre_besoins,
                       COALESCE(SUM(b.quantite), 0) as total_quantite,
                       COALESCE(SUM(b.quantite * b.prix_unitaire), 0) as valeur_totale
                FROM ville v
                LEFT JOIN besoin b ON v.id = b.ville_id
                GROUP BY v.id, v.nom, v.region
                ORDER BY v.nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les statistiques par produit
     * @return array Statistiques par produit
     */
    public function getStatsByProduit(): array
    {
        $sql = "SELECT produit,
                       SUM(quantite) as total_quantite,
                       SUM(quantite * prix_unitaire) as valeur_totale,
                       COUNT(*) as nombre_besoins
                FROM besoin
                GROUP BY produit
                ORDER BY produit ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

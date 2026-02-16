<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Ville - Gestion des villes
 * Projet BNGRC - Module Bolton
 */
class Ville
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Insérer une nouvelle ville
     * @param string $nom Nom de la ville
     * @param string $region Région de la ville
     * @return int|false ID de la ville insérée ou false en cas d'échec
     */
    public function insertVille(string $nom, string $region): int|false
    {
        $sql = "INSERT INTO ville (nom, region) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$nom, $region]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer toutes les villes
     * @return array Liste des villes
     */
    public function getAllVilles(): array
    {
        $sql = "SELECT * FROM ville ORDER BY nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une ville par son ID
     * @param int $id ID de la ville
     * @return array|false Données de la ville ou false si non trouvée
     */
    public function getVilleById(int $id): array|false
    {
        $sql = "SELECT * FROM ville WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher des villes par nom
     * @param string $search Terme de recherche
     * @return array Liste des villes correspondantes
     */
    public function searchVilles(string $search): array
    {
        $sql = "SELECT * FROM ville WHERE nom LIKE ? OR region LIKE ? ORDER BY nom ASC";
        $stmt = $this->db->prepare($sql);
        $search = "%{$search}%";
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les villes par région
     * @param string $region Nom de la région
     * @return array Liste des villes de la région
     */
    public function getVillesByRegion(string $region): array
    {
        $sql = "SELECT * FROM ville WHERE region = ? ORDER BY nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$region]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les régions distinctes
     * @return array Liste des régions
     */
    public function getAllRegions(): array
    {
        $sql = "SELECT DISTINCT region FROM ville ORDER BY region ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour une ville
     * @param int $id ID de la ville
     * @param string $nom Nouveau nom
     * @param string $region Nouvelle région
     * @return bool Succès de la mise à jour
     */
    public function updateVille(int $id, string $nom, string $region): bool
    {
        $sql = "UPDATE ville SET nom = ?, region = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nom, $region, $id]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer une ville
     * @param int $id ID de la ville
     * @return bool Succès de la suppression
     */
    public function deleteVille(int $id): bool
    {
        $sql = "DELETE FROM ville WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Vérifier si une ville existe
     * @param int $id ID de la ville
     * @return bool True si la ville existe
     */
    public function villeExists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM ville WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifier si une ville a des besoins associés
     * @param int $id ID de la ville
     * @return bool True si la ville a des besoins
     */
    public function hasBesoins(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM besoin WHERE ville_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Compter le nombre total de villes
     * @return int Nombre de villes
     */
    public function countVilles(): int
    {
        $sql = "SELECT COUNT(*) FROM ville";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}

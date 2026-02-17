<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model DonArgent - Gestion des dons en argent
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class DonArgent
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Créer un nouveau don argent
     * @param float $montant Montant du don
     * @param string $donateur Nom du donateur
     * @param string|null $dateSaisie Date de saisie
     * @param string|null $notes Notes additionnelles
     * @return int|false ID du don créé ou false en cas d'échec
     */
    public function create(float $montant, string $donateur = 'Anonyme', ?string $dateSaisie = null, ?string $notes = null): int|false
    {
        $dateSaisie = $dateSaisie ?? date('Y-m-d');
        
        // Store montant_initial equal to montant for reset purposes
        $sql = "INSERT INTO don_argent (montant, montant_initial, donateur, date_saisie, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$montant, $montant, $donateur, $dateSaisie, $notes]);
        
        if ($result) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer tous les dons argent
     * @return array Liste des dons argent
     */
    public function getAll(): array
    {
        $sql = "SELECT *, 
                       (montant - montant_utilise) as montant_disponible
                FROM don_argent 
                ORDER BY date_saisie ASC, id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer un don argent par son ID
     * @param int $id ID du don
     * @return array|false Données du don ou false si non trouvé
     */
    public function getById(int $id): array|false
    {
        $sql = "SELECT *, (montant - montant_utilise) as montant_disponible FROM don_argent WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les dons argent disponibles (non épuisés)
     * @return array Liste des dons disponibles
     */
    public function getAvailable(): array
    {
        $sql = "SELECT *, (montant - montant_utilise) as montant_disponible
                FROM don_argent 
                WHERE statut != 'epuise'
                ORDER BY date_saisie ASC, id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le montant total disponible
     * @return float Montant total disponible
     */
    public function getTotalDisponible(): float
    {
        $sql = "SELECT COALESCE(SUM(montant - montant_utilise), 0) FROM don_argent WHERE statut != 'epuise'";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer le montant total des dons argent
     * @return float Montant total
     */
    public function getTotal(): float
    {
        $sql = "SELECT COALESCE(SUM(montant), 0) FROM don_argent";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer le montant total utilisé
     * @return float Montant utilisé
     */
    public function getTotalUtilise(): float
    {
        $sql = "SELECT COALESCE(SUM(montant_utilise), 0) FROM don_argent";
        $stmt = $this->db->query($sql);
        return (float) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour un don argent
     * @param int $id ID du don
     * @param array $data Données à mettre à jour
     * @return bool Succès ou échec
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE don_argent SET montant = ?, donateur = ?, date_saisie = ?, notes = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['montant'],
            $data['donateur'] ?? 'Anonyme',
            $data['date_saisie'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Utiliser un montant d'un don argent
     * @param int $id ID du don
     * @param float $montantUtilise Montant à utiliser
     * @return bool Succès ou échec
     */
    public function utiliserMontant(int $id, float $montantUtilise): bool
    {
        $don = $this->getById($id);
        if (!$don) {
            return false;
        }

        $nouveauMontantUtilise = $don['montant_utilise'] + $montantUtilise;
        
        // Vérifier qu'on ne dépasse pas le montant total
        if ($nouveauMontantUtilise > $don['montant']) {
            return false;
        }

        // Déterminer le nouveau statut
        $statut = 'disponible';
        if ($nouveauMontantUtilise >= $don['montant']) {
            $statut = 'epuise';
        } elseif ($nouveauMontantUtilise > 0) {
            $statut = 'partiellement_utilise';
        }

        $sql = "UPDATE don_argent SET montant_utilise = ?, statut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nouveauMontantUtilise, $statut, $id]);
    }

    /**
     * Réinitialiser l'utilisation d'un don argent
     * @param int $id ID du don
     * @return bool Succès ou échec
     */
    public function resetUtilisation(int $id): bool
    {
        $sql = "UPDATE don_argent SET montant_utilise = 0, statut = 'disponible' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Réinitialiser toutes les utilisations
     * @return bool Succès ou échec
     */
    public function resetAllUtilisations(): bool
    {
        $sql = "UPDATE don_argent SET montant_utilise = 0, statut = 'disponible'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer un don argent
     * @param int $id ID du don
     * @return bool Succès ou échec
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM don_argent WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ===================== STATISTIQUES ===================== */

    /**
     * Compter le nombre de dons argent
     * @return int Nombre de dons
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM don_argent";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupérer les statistiques globales
     * @return array Statistiques
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as nombre_dons,
                    COALESCE(SUM(montant), 0) as total_montant,
                    COALESCE(SUM(montant_utilise), 0) as total_utilise,
                    COALESCE(SUM(montant - montant_utilise), 0) as total_disponible,
                    COUNT(CASE WHEN statut = 'disponible' THEN 1 END) as dons_disponibles,
                    COUNT(CASE WHEN statut = 'partiellement_utilise' THEN 1 END) as dons_partiels,
                    COUNT(CASE WHEN statut = 'epuise' THEN 1 END) as dons_epuises
                FROM don_argent";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

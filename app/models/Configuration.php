<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Configuration - Gestion de la configuration système
 * Projet BNGRC - Module Bolton (Configuration des frais)
 */
class Configuration
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== CREATE ===================== */

    /**
     * Insérer ou mettre à jour une configuration
     * @param string $cle Clé de configuration
     * @param string $valeur Valeur de configuration
     * @param string|null $description Description optionnelle
     * @return bool Succès de l'opération
     */
    public function setConfig(string $cle, string $valeur, ?string $description = null): bool
    {
        $sql = "INSERT INTO configuration (cle, valeur, description) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valeur = ?, description = COALESCE(?, description), updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle, $valeur, $description, $valeur, $description]);
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer une valeur de configuration
     * @param string $cle Clé de configuration
     * @param string|null $default Valeur par défaut si non trouvée
     * @return string|null Valeur de configuration
     */
    public function getConfig(string $cle, ?string $default = null): ?string
    {
        $sql = "SELECT valeur FROM configuration WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['valeur'] : $default;
    }

    /**
     * Récupérer toutes les configurations
     * @return array Liste des configurations
     */
    public function getAllConfigs(): array
    {
        $sql = "SELECT * FROM configuration ORDER BY cle ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le pourcentage de frais d'achat
     * @return float Pourcentage de frais
     */
    public function getFraisAchatPourcentage(): float
    {
        return (float) ($this->getConfig('frais_achat_pourcentage', '5') ?? 5);
    }

    /**
     * Mettre à jour le pourcentage de frais d'achat
     * @param float $pourcentage Nouveau pourcentage
     * @return bool Succès de l'opération
     */
    public function setFraisAchatPourcentage(float $pourcentage): bool
    {
        if ($pourcentage < 0 || $pourcentage > 100) {
            return false;
        }
        return $this->setConfig(
            'frais_achat_pourcentage', 
            (string) $pourcentage, 
            'Pourcentage de frais appliqué sur les achats avec dons argent'
        );
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer une configuration
     * @param string $cle Clé de configuration
     * @return bool Succès de la suppression
     */
    public function deleteConfig(string $cle): bool
    {
        $sql = "DELETE FROM configuration WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle]);
    }
}

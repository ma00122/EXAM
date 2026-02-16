<?php
namespace app\models;

use PDO;
use flight\database\PdoWrapper;

/**
 * Model Configuration - Gestion des paramètres système
 * Projet BNGRC - Module Mahery (ETU004546)
 */
class Configuration
{
    private PdoWrapper $db;

    public function __construct(PdoWrapper $db)
    {
        $this->db = $db;
    }

    /* ===================== READ ===================== */

    /**
     * Récupérer toutes les configurations
     * @return array Liste des configurations
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM configuration ORDER BY cle ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer une configuration par sa clé
     * @param string $cle Clé de configuration
     * @return string|null Valeur ou null si non trouvée
     */
    public function get(string $cle): ?string
    {
        $sql = "SELECT valeur FROM configuration WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        return $result !== false ? $result : null;
    }

    /**
     * Récupérer une configuration complète par sa clé
     * @param string $cle Clé de configuration
     * @return array|false Données de la configuration ou false si non trouvée
     */
    public function getByKey(string $cle): array|false
    {
        $sql = "SELECT * FROM configuration WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le pourcentage de frais d'achat
     * @return float Pourcentage de frais
     */
    public function getFraisAchatPourcentage(): float
    {
        $valeur = $this->get('frais_achat_pourcentage');
        return $valeur !== null ? (float) $valeur : 5.0; // Défaut: 5%
    }

    /**
     * Récupérer le montant minimum d'achat
     * @return float Montant minimum
     */
    public function getMontantMinimumAchat(): float
    {
        $valeur = $this->get('montant_minimum_achat');
        return $valeur !== null ? (float) $valeur : 1000.0;
    }

    /* ===================== UPDATE ===================== */

    /**
     * Mettre à jour une configuration
     * @param string $cle Clé de configuration
     * @param string $valeur Nouvelle valeur
     * @return bool Succès ou échec
     */
    public function set(string $cle, string $valeur): bool
    {
        // Vérifier si la clé existe
        $existing = $this->get($cle);
        
        if ($existing !== null) {
            // Mettre à jour
            $sql = "UPDATE configuration SET valeur = ? WHERE cle = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$valeur, $cle]);
        } else {
            // Insérer
            $sql = "INSERT INTO configuration (cle, valeur) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cle, $valeur]);
        }
    }

    /**
     * Mettre à jour le pourcentage de frais d'achat
     * @param float $pourcentage Nouveau pourcentage
     * @return bool Succès ou échec
     */
    public function setFraisAchatPourcentage(float $pourcentage): bool
    {
        if ($pourcentage < 0 || $pourcentage > 100) {
            return false;
        }
        return $this->set('frais_achat_pourcentage', (string) $pourcentage);
    }

    /* ===================== CREATE ===================== */

    /**
     * Créer une nouvelle configuration
     * @param string $cle Clé
     * @param string $valeur Valeur
     * @param string|null $description Description optionnelle
     * @return bool Succès ou échec
     */
    public function create(string $cle, string $valeur, ?string $description = null): bool
    {
        $sql = "INSERT INTO configuration (cle, valeur, description) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle, $valeur, $description]);
    }

    /* ===================== DELETE ===================== */

    /**
     * Supprimer une configuration
     * @param string $cle Clé de configuration
     * @return bool Succès ou échec
     */
    public function delete(string $cle): bool
    {
        $sql = "DELETE FROM configuration WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$cle]);
    }

    /* ===================== CALCULS ===================== */

    /**
     * Calculer les frais d'achat pour un montant donné
     * @param float $montant Montant de l'achat
     * @return array [montant_ht, frais, montant_total]
     */
    public function calculerFraisAchat(float $montant): array
    {
        $pourcentage = $this->getFraisAchatPourcentage();
        $frais = $montant * ($pourcentage / 100);
        
        return [
            'montant_ht' => $montant,
            'frais' => round($frais, 2),
            'montant_total' => round($montant + $frais, 2),
            'pourcentage_frais' => $pourcentage
        ];
    }
}

<?php
namespace app\models;

use flight\ActiveRecord;

/**
 * Model Besoin - Gestion des besoins
 * 
 * @property int $id
 * @property int $ville_id
 * @property int $type_id
 * @property string $produit
 * @property int $quantite
 * @property float $prix_unitaire
 * @property string $created_at
 * @property string $updated_at
 */
class Besoin extends ActiveRecord
{
    public function __construct($database_connection = null)
    {
        parent::__construct($database_connection, 'besoin');
    }

    /**
     * Calcul automatique de la valeur totale
     * valeur_totale = quantite × prix_unitaire
     */
    public function getValeurTotale(): float
    {
        return ($this->quantite ?? 0) * ($this->prix_unitaire ?? 0);
    }

    /**
     * Récupérer toutes les villes
     */
    public static function getAllVilles(): array
    {
        $db = \Flight::db();
        $stmt = $db->query("SELECT * FROM ville ORDER BY nom");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer tous les types de besoin
     */
    public static function getAllTypes(): array
    {
        $db = \Flight::db();
        $stmt = $db->query("SELECT * FROM type_besoin ORDER BY id");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Insérer un nouveau besoin
     */
    public static function insertBesoin(array $data): bool
    {
        // Validation
        if (!self::validateBesoin($data)) {
            return false;
        }

        $db = \Flight::db();
        $stmt = $db->prepare("INSERT INTO besoin (ville_id, type_id, produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            (int) $data['ville_id'],
            (int) $data['type_id'],
            trim($data['produit']),
            (int) $data['quantite'],
            (float) $data['prix_unitaire']
        ]);
    }

    /**
     * Récupérer tous les besoins
     */
    public static function getAllBesoins(): array
    {
        $besoin = new Besoin(\Flight::db());
        return $besoin->findAll();
    }

    /**
     * Récupérer un besoin par ID
     */
    public static function getBesoinById(int $id): ?array
    {
        $db = \Flight::db();
        $stmt = $db->prepare("
            SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom 
            FROM besoin b 
            LEFT JOIN type_besoin t ON b.type_id = t.id 
            LEFT JOIN ville v ON b.ville_id = v.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Mettre à jour la quantité uniquement
     * Note: Le prix unitaire ne doit pas être modifiable après insertion
     */
    public static function updateQuantite(int $id, int $quantite): bool
    {
        if ($quantite <= 0) {
            return false;
        }

        $db = \Flight::db();
        $stmt = $db->prepare("UPDATE besoin SET quantite = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$quantite, $id]);
    }

    /**
     * Supprimer un besoin
     */
    public static function deleteBesoin(int $id): bool
    {
        $db = \Flight::db();
        $stmt = $db->prepare("DELETE FROM besoin WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Validation des données du besoin
     */
    public static function validateBesoin(array $data): bool
    {
        // ville_id requis
        if (!isset($data['ville_id']) || (int) $data['ville_id'] <= 0) {
            return false;
        }

        // type_id requis
        if (!isset($data['type_id']) || (int) $data['type_id'] <= 0) {
            return false;
        }

        // produit non vide
        if (!isset($data['produit']) || empty(trim($data['produit']))) {
            return false;
        }

        // quantite > 0
        if (!isset($data['quantite']) || (int) $data['quantite'] <= 0) {
            return false;
        }

        // prix_unitaire > 0
        if (!isset($data['prix_unitaire']) || (float) $data['prix_unitaire'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Récupérer tous les besoins avec détails
     */
    public static function getAllBesoinsWithDetails(): array
    {
        $db = \Flight::db();
        $sql = "
            SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom
            FROM besoin b
            LEFT JOIN type_besoin t ON b.type_id = t.id
            LEFT JOIN ville v ON b.ville_id = v.id
            ORDER BY b.id DESC
        ";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

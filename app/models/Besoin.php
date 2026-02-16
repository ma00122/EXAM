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
     * Relation avec la ville
     */
    public function ville(): Ville
    {
        return $this->belongsTo(Ville::class, 'ville_id');
    }

    /**
     * Relation avec le type de besoin
     */
    public function typeBesoin(): TypeBesoin
    {
        return $this->belongsTo(TypeBesoin::class, 'type_id');
    }

    /**
     * Calcul automatique de la valeur totale
     * valeur_totale = quantite × prix_unitaire
     */
    public function getValeurTotale(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }

    /**
     * Insérer un nouveau besoin
     */
    public static function insertBesoin(array $data): ?Besoin
    {
        // Validation
        if (!self::validateBesoin($data)) {
            return null;
        }

        $besoin = new Besoin(\Flight::db());
        $besoin->ville_id = (int) $data['ville_id'];
        $besoin->type_id = (int) $data['type_id'];
        $besoin->produit = trim($data['produit']);
        $besoin->quantite = (int) $data['quantite'];
        $besoin->prix_unitaire = (float) $data['prix_unitaire'];
        $besoin->save();

        return $besoin;
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
     * Récupérer les besoins par ville
     */
    public static function getBesoinsByVille(int $ville_id): array
    {
        $besoin = new Besoin(\Flight::db());
        return $besoin->eq('ville_id', $ville_id)->findAll();
    }

    /**
     * Récupérer les besoins par type
     */
    public static function getBesoinsByType(int $type_id): array
    {
        $besoin = new Besoin(\Flight::db());
        return $besoin->eq('type_id', $type_id)->findAll();
    }

    /**
     * Récupérer un besoin par ID
     */
    public static function getBesoinById(int $id): ?Besoin
    {
        $besoin = new Besoin(\Flight::db());
        $besoin->eq('id', $id)->find();
        // Vérifier si le besoin existe (id chargé)
        if ($besoin->id === null) {
            return null;
        }
        return $besoin;
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

        $besoin = self::getBesoinById($id);
        if (!$besoin) {
            return false;
        }

        $besoin->quantite = $quantite;
        $besoin->save();

        return true;
    }

    /**
     * Supprimer un besoin
     */
    public static function deleteBesoin(int $id): bool
    {
        $besoin = self::getBesoinById($id);
        if (!$besoin) {
            return false;
        }

        $besoin->delete();
        return true;
    }

    /**
     * Validation des données du besoin
     */
    public static function validateBesoin(array $data): bool
    {
        // quantite > 0
        if (!isset($data['quantite']) || (int) $data['quantite'] <= 0) {
            return false;
        }

        // prix_unitaire > 0
        if (!isset($data['prix_unitaire']) || (float) $data['prix_unitaire'] <= 0) {
            return false;
        }

        // produit non vide
        if (!isset($data['produit']) || empty(trim($data['produit']))) {
            return false;
        }

        // ville_id et type_id requis
        if (!isset($data['ville_id']) || (int) $data['ville_id'] <= 0) {
            return false;
        }

        if (!isset($data['type_id']) || (int) $data['type_id'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Récupérer tous les besoins avec jointures (ville et type)
     */
    public static function getAllBesoinsWithDetails(): array
    {
        $db = \Flight::db();
        $sql = "
            SELECT b.*, v.nom as ville_nom, t.nom_type as type_nom
            FROM besoin b
            LEFT JOIN ville v ON b.ville_id = v.id
            LEFT JOIN type_besoin t ON b.type_id = t.id
            ORDER BY b.created_at DESC
        ";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Filtrer les besoins par ville et/ou type
     */
    public static function filterBesoins(?int $ville_id = null, ?int $type_id = null): array
    {
        $db = \Flight::db();
        $sql = "
            SELECT b.*, v.nom as ville_nom, t.nom_type as type_nom
            FROM besoin b
            LEFT JOIN ville v ON b.ville_id = v.id
            LEFT JOIN type_besoin t ON b.type_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if ($ville_id !== null) {
            $sql .= " AND b.ville_id = ?";
            $params[] = $ville_id;
        }

        if ($type_id !== null) {
            $sql .= " AND b.type_id = ?";
            $params[] = $type_id;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

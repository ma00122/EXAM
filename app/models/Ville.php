<?php
namespace app\models;

use flight\ActiveRecord;

/**
 * Model Ville - Gestion des villes
 * 
 * @property int $id
 * @property string $nom
 * @property string $region
 * @property string $created_at
 * @property string $updated_at
 */
class Ville extends ActiveRecord
{
    public function __construct($database_connection = null)
    {
        parent::__construct($database_connection, 'ville');
    }

    /**
     * Relation avec les besoins
     */
    public function besoins(): array
    {
        return $this->hasMany(Besoin::class, 'ville_id');
    }

    /**
     * Insérer une nouvelle ville
     */
    public static function insertVille(array $data): ?Ville
    {
        if (empty(trim($data['nom'] ?? ''))) {
            return null;
        }

        $ville = new Ville(\Flight::db());
        $ville->nom = trim($data['nom']);
        $ville->region = trim($data['region'] ?? '');
        $ville->save();

        return $ville;
    }

    /**
     * Récupérer toutes les villes
     */
    public static function getAllVilles(): array
    {
        $ville = new Ville(\Flight::db());
        return $ville->findAll();
    }

    /**
     * Récupérer une ville par ID
     */
    public static function getVilleById(int $id): ?Ville
    {
        $ville = new Ville(\Flight::db());
        $ville->eq('id', $id)->find();
        if ($ville->id === null) {
            return null;
        }
        return $ville;
    }

    /**
     * Mettre à jour une ville
     */
    public static function updateVille(int $id, array $data): bool
    {
        $ville = self::getVilleById($id);
        if (!$ville) {
            return false;
        }

        if (isset($data['nom'])) {
            $ville->nom = trim($data['nom']);
        }
        if (isset($data['region'])) {
            $ville->region = trim($data['region']);
        }
        $ville->save();

        return true;
    }

    /**
     * Supprimer une ville
     */
    public static function deleteVille(int $id): bool
    {
        $ville = self::getVilleById($id);
        if (!$ville) {
            return false;
        }

        $ville->delete();
        return true;
    }
}

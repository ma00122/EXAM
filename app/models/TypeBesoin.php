<?php
namespace app\models;

use flight\ActiveRecord;

/**
 * Model TypeBesoin - Gestion des types de besoins
 * 
 * @property int $id
 * @property string $nom_type
 * @property string $created_at
 * @property string $updated_at
 */
class TypeBesoin extends ActiveRecord
{
    public function __construct($database_connection = null)
    {
        parent::__construct($database_connection, 'type_besoin');
    }

    /**
     * Récupérer tous les types de besoin
     */
    public static function getAllTypes(): array
    {
        $type = new TypeBesoin(\Flight::db());
        return $type->findAll();
    }

    /**
     * Récupérer un type par ID
     */
    public static function getTypeById(int $id): ?TypeBesoin
    {
        $type = new TypeBesoin(\Flight::db());
        $type->eq('id', $id)->find();
        if ($type->id === null) {
            return null;
        }
        return $type;
    }
}

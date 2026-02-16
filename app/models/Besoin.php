<?php
namespace app\models;

use flight\ActiveRecord;

/**
 * Model Besoin - Gestion des besoins
 * Projet BNGRC - Module Bolton (Calculs Financiers)
 * 
 * @property int $id
 * @property int $ville_id
 * @property int $type_id
 * @property string $produit
 * @property int $quantite
 * @property int $quantite_satisfaite
 * @property float $prix_unitaire
 * @property string $date_saisie
 * @property string $created_at
 * @property string $updated_at
 */
class Besoin extends ActiveRecord
{
    public function __construct($database_connection = null)
    {
        parent::__construct($database_connection, 'besoin');
    }

    /* =====================================================
     * CALCULS FINANCIERS - BOLTON
     * ===================================================== */

    /**
     * Calcul automatique de la valeur totale (montant_total)
     * montant_total = quantite × prix_unitaire
     */
    public function getValeurTotale(): float
    {
        return ($this->quantite ?? 0) * ($this->prix_unitaire ?? 0);
    }

    /**
     * Calcul du montant total demandé
     * montant_total = quantite * prix_unitaire
     */
    public function getMontantTotal(): float
    {
        return ($this->quantite ?? 0) * ($this->prix_unitaire ?? 0);
    }

    /**
     * Calcul du montant satisfait
     * montant_satisfait = quantite_satisfaite * prix_unitaire
     */
    public function getMontantSatisfait(): float
    {
        return ($this->quantite_satisfaite ?? 0) * ($this->prix_unitaire ?? 0);
    }

    /**
     * Calcul du montant restant
     * montant_restant = montant_total - montant_satisfait
     */
    public function getMontantRestant(): float
    {
        return $this->getMontantTotal() - $this->getMontantSatisfait();
    }

    /**
     * Calcul de la quantité restante
     */
    public function getQuantiteRestante(): int
    {
        return max(0, ($this->quantite ?? 0) - ($this->quantite_satisfaite ?? 0));
    }

    /* =====================================================
     * FONCTIONS GLOBALES CALCULS FINANCIERS - BOLTON
     * ===================================================== */

    /**
     * Récupérer le montant total global de tous les besoins
     * @return float Montant total global
     */
    public static function getMontantTotalGlobal(): float
    {
        $db = \Flight::db();
        $sql = "SELECT COALESCE(SUM(quantite * prix_unitaire), 0) as total FROM besoin";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Récupérer le montant satisfait global
     * @return float Montant satisfait global
     */
    public static function getMontantSatisfaitGlobal(): float
    {
        $db = \Flight::db();
        $sql = "SELECT COALESCE(SUM(COALESCE(quantite_satisfaite, 0) * prix_unitaire), 0) as total FROM besoin";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Récupérer le montant restant global
     * @return float Montant restant global
     */
    public static function getMontantRestantGlobal(): float
    {
        $db = \Flight::db();
        $sql = "SELECT COALESCE(SUM((quantite - COALESCE(quantite_satisfaite, 0)) * prix_unitaire), 0) as total FROM besoin";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Récupérer les statistiques globales des besoins
     * @return array Statistiques complètes
     */
    public static function getStatistiquesGlobales(): array
    {
        $db = \Flight::db();
        $sql = "SELECT 
            COALESCE(SUM(quantite * prix_unitaire), 0) as montant_total_global,
            COALESCE(SUM(COALESCE(quantite_satisfaite, 0) * prix_unitaire), 0) as montant_satisfait_global,
            COALESCE(SUM((quantite - COALESCE(quantite_satisfaite, 0)) * prix_unitaire), 0) as montant_restant_global,
            COUNT(*) as nombre_besoins,
            COALESCE(SUM(quantite), 0) as quantite_totale_demandee,
            COALESCE(SUM(COALESCE(quantite_satisfaite, 0)), 0) as quantite_totale_recue,
            COALESCE(SUM(quantite - COALESCE(quantite_satisfaite, 0)), 0) as quantite_totale_restante
        FROM besoin";
        $stmt = $db->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Récupérer les statistiques par ville
     * @param int $villeId ID de la ville
     * @return array Statistiques de la ville
     */
    public static function getStatistiquesParVille(int $villeId): array
    {
        $db = \Flight::db();
        $sql = "SELECT 
            COALESCE(SUM(quantite * prix_unitaire), 0) as montant_total,
            COALESCE(SUM(COALESCE(quantite_satisfaite, 0) * prix_unitaire), 0) as montant_satisfait,
            COALESCE(SUM((quantite - COALESCE(quantite_satisfaite, 0)) * prix_unitaire), 0) as montant_restant,
            COUNT(*) as nombre_besoins,
            COALESCE(SUM(quantite), 0) as quantite_demandee,
            COALESCE(SUM(COALESCE(quantite_satisfaite, 0)), 0) as quantite_recue,
            COALESCE(SUM(quantite - COALESCE(quantite_satisfaite, 0)), 0) as quantite_restante
        FROM besoin WHERE ville_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /* =====================================================
     * FONCTIONS POUR SIMULATION AVEC FRAIS - BOLTON
     * ===================================================== */

    /**
     * Récupérer le pourcentage de frais d'achat depuis la configuration
     * @return float Pourcentage de frais
     */
    public static function getFraisAchatPourcentage(): float
    {
        $db = \Flight::db();
        $sql = "SELECT valeur FROM configuration WHERE cle = 'frais_achat_pourcentage'";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($result['valeur'] ?? 5); // Par défaut 5%
    }

    /**
     * Calculer le coût d'achat avec frais
     * @param float $montantProduit Montant du produit à acheter
     * @param float|null $fraisPourcentage Pourcentage de frais (optionnel, sinon récupéré de la config)
     * @return array ['montant_produit', 'frais', 'montant_total']
     */
    public static function calculerCoutAchatAvecFrais(float $montantProduit, ?float $fraisPourcentage = null): array
    {
        if ($fraisPourcentage === null) {
            $fraisPourcentage = self::getFraisAchatPourcentage();
        }
        
        $frais = ($montantProduit * $fraisPourcentage) / 100;
        $montantTotal = $montantProduit + $frais;
        
        return [
            'montant_produit' => $montantProduit,
            'frais' => $frais,
            'frais_pourcentage' => $fraisPourcentage,
            'montant_total' => $montantTotal
        ];
    }

    /**
     * Calculer ce qui peut être acheté avec un montant d'argent disponible
     * @param int $besoinId ID du besoin
     * @param float $argentDisponible Montant d'argent disponible pour l'achat
     * @param float|null $fraisPourcentage Pourcentage de frais (optionnel)
     * @return array Détails de l'achat possible (complet ou partiel)
     */
    public static function calculerAchatPossible(int $besoinId, float $argentDisponible, ?float $fraisPourcentage = null): array
    {
        $besoin = self::getBesoinById($besoinId);
        
        if (!$besoin) {
            return [
                'success' => false,
                'message' => 'Besoin introuvable'
            ];
        }
        
        if ($fraisPourcentage === null) {
            $fraisPourcentage = self::getFraisAchatPourcentage();
        }
        
        $prixUnitaire = (float) $besoin['prix_unitaire'];
        $quantiteRestante = (int) $besoin['quantite'] - (int) ($besoin['quantite_satisfaite'] ?? 0);
        
        if ($quantiteRestante <= 0) {
            return [
                'success' => false,
                'message' => 'Ce besoin est déjà entièrement satisfait',
                'besoin' => $besoin
            ];
        }
        
        // Coût total pour satisfaire le besoin restant (avec frais)
        $montantProduitTotal = $quantiteRestante * $prixUnitaire;
        $coutTotal = self::calculerCoutAchatAvecFrais($montantProduitTotal, $fraisPourcentage);
        
        // Vérifier si l'argent disponible suffit
        if ($argentDisponible >= $coutTotal['montant_total']) {
            // Achat complet possible
            return [
                'success' => true,
                'type' => 'complet',
                'besoin_id' => $besoinId,
                'produit' => $besoin['produit'],
                'quantite_achetee' => $quantiteRestante,
                'prix_unitaire' => $prixUnitaire,
                'montant_produit' => $montantProduitTotal,
                'frais' => $coutTotal['frais'],
                'frais_pourcentage' => $fraisPourcentage,
                'montant_total' => $coutTotal['montant_total'],
                'argent_restant' => $argentDisponible - $coutTotal['montant_total'],
                'message' => 'Achat complet possible'
            ];
        }
        
        // Achat partiel - Calculer combien on peut acheter
        // montant_disponible = (quantite * prix_unitaire) * (1 + frais/100)
        // donc quantite = montant_disponible / (prix_unitaire * (1 + frais/100))
        $coefficientFrais = 1 + ($fraisPourcentage / 100);
        $quantiteAchetable = floor($argentDisponible / ($prixUnitaire * $coefficientFrais));
        
        if ($quantiteAchetable <= 0) {
            return [
                'success' => false,
                'type' => 'insuffisant',
                'message' => 'Argent insuffisant pour acheter au moins une unité',
                'besoin' => $besoin,
                'argent_disponible' => $argentDisponible,
                'cout_minimum' => $prixUnitaire * $coefficientFrais
            ];
        }
        
        // Limiter à la quantité restante
        $quantiteAchetable = min($quantiteAchetable, $quantiteRestante);
        $montantProduitPartiel = $quantiteAchetable * $prixUnitaire;
        $coutPartiel = self::calculerCoutAchatAvecFrais($montantProduitPartiel, $fraisPourcentage);
        
        return [
            'success' => true,
            'type' => 'partiel',
            'besoin_id' => $besoinId,
            'produit' => $besoin['produit'],
            'quantite_demandee' => $quantiteRestante,
            'quantite_achetee' => $quantiteAchetable,
            'quantite_non_satisfaite' => $quantiteRestante - $quantiteAchetable,
            'prix_unitaire' => $prixUnitaire,
            'montant_produit' => $montantProduitPartiel,
            'frais' => $coutPartiel['frais'],
            'frais_pourcentage' => $fraisPourcentage,
            'montant_total' => $coutPartiel['montant_total'],
            'argent_restant' => $argentDisponible - $coutPartiel['montant_total'],
            'message' => 'Achat partiel - Argent insuffisant pour satisfaire tout le besoin'
        ];
    }

    /**
     * Mettre à jour la quantité satisfaite d'un besoin
     * @param int $id ID du besoin
     * @param int $quantiteAjoutee Quantité à ajouter au satisfait
     * @return bool Succès de la mise à jour
     */
    public static function ajouterQuantiteSatisfaite(int $id, int $quantiteAjoutee): bool
    {
        if ($quantiteAjoutee <= 0) {
            return false;
        }
        
        $db = \Flight::db();
        $sql = "UPDATE besoin 
                SET quantite_satisfaite = COALESCE(quantite_satisfaite, 0) + ?, 
                    updated_at = NOW() 
                WHERE id = ? AND (quantite - COALESCE(quantite_satisfaite, 0)) >= ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$quantiteAjoutee, $id, $quantiteAjoutee]);
    }

    /**
     * Récupérer les besoins restants (non entièrement satisfaits)
     * @return array Liste des besoins avec quantité restante > 0
     */
    public static function getBesoinsRestants(): array
    {
        $db = \Flight::db();
        $sql = "SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom,
                (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante,
                (b.quantite * b.prix_unitaire) as montant_total,
                (COALESCE(b.quantite_satisfaite, 0) * b.prix_unitaire) as montant_satisfait,
                ((b.quantite - COALESCE(b.quantite_satisfaite, 0)) * b.prix_unitaire) as montant_restant
            FROM besoin b
            LEFT JOIN type_besoin t ON b.type_id = t.id
            LEFT JOIN ville v ON b.ville_id = v.id
            WHERE (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0
            ORDER BY v.nom ASC, b.produit ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins restants par ville
     * @param int $villeId ID de la ville
     * @return array Liste des besoins restants pour cette ville
     */
    public static function getBesoinsRestantsParVille(int $villeId): array
    {
        $db = \Flight::db();
        $sql = "SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom,
                (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante,
                (b.quantite * b.prix_unitaire) as montant_total,
                (COALESCE(b.quantite_satisfaite, 0) * b.prix_unitaire) as montant_satisfait,
                ((b.quantite - COALESCE(b.quantite_satisfaite, 0)) * b.prix_unitaire) as montant_restant
            FROM besoin b
            LEFT JOIN type_besoin t ON b.type_id = t.id
            LEFT JOIN ville v ON b.ville_id = v.id
            WHERE b.ville_id = ? AND (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0
            ORDER BY b.produit ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$villeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les besoins par produit pour la simulation
     * @param string $produit Nom du produit
     * @return array Liste des besoins pour ce produit
     */
    public static function getBesoinsParProduit(string $produit): array
    {
        $db = \Flight::db();
        $sql = "SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom,
                (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante,
                ((b.quantite - COALESCE(b.quantite_satisfaite, 0)) * b.prix_unitaire) as montant_restant
            FROM besoin b
            LEFT JOIN type_besoin t ON b.type_id = t.id
            LEFT JOIN ville v ON b.ville_id = v.id
            WHERE LOWER(b.produit) = LOWER(?) AND (b.quantite - COALESCE(b.quantite_satisfaite, 0)) > 0
            ORDER BY b.date_saisie ASC, b.id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$produit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
     * Supprimer un besoin SSSS
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

    /**
     * Récupérer les besoins pour une ville donnée
     */
    public static function getBesoinsByVille(int $ville_id): array
    {
        $db = \Flight::db();
        $sql = "
            SELECT b.*, t.nom_type as type_nom, v.nom as ville_nom
            FROM besoin b
            LEFT JOIN type_besoin t ON b.type_id = t.id
            LEFT JOIN ville v ON b.ville_id = v.id
            WHERE b.ville_id = :ville_id
            ORDER BY b.id DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':ville_id' => $ville_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

-- =====================================================
-- BOLTON - Module Besoins + Calculs Financiers
-- Modifications table besoin + table configuration
-- =====================================================

-- =====================================================
-- 1. Modification de la table besoin
-- Ajout colonne quantite_satisfaite pour suivre besoins satisfaits
-- Ajout colonne date_saisie
-- =====================================================

-- Ajouter colonne quantite_satisfaite si elle n'existe pas
ALTER TABLE besoin ADD COLUMN IF NOT EXISTS quantite_satisfaite INT DEFAULT 0;

-- Ajouter colonne date_saisie si elle n'existe pas
ALTER TABLE besoin ADD COLUMN IF NOT EXISTS date_saisie DATE DEFAULT (CURRENT_DATE);

-- =====================================================
-- 2. Table configuration pour les frais d'achat
-- =====================================================

CREATE TABLE IF NOT EXISTS configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insérer le pourcentage de frais d'achat par défaut (5%)
INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais appliqué sur les achats avec dons argent')
ON DUPLICATE KEY UPDATE valeur = valeur;

-- =====================================================
-- 3. Table achat pour enregistrer les achats avec dons argent
-- =====================================================

CREATE TABLE IF NOT EXISTS achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    besoin_id INT NOT NULL,
    don_id INT DEFAULT NULL,
    montant_produit DECIMAL(15,2) NOT NULL,
    frais DECIMAL(15,2) NOT NULL DEFAULT 0,
    montant_total DECIMAL(15,2) NOT NULL,
    quantite_achetee INT NOT NULL DEFAULT 0,
    date_achat DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE,
    FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE SET NULL
);

-- =====================================================
-- 4. Données de test pour les besoins
-- Exemples: Riz, Tole, Clous, Huile avec prix_unitaire
-- =====================================================

-- Mise à jour des besoins existants avec date_saisie et quantite_satisfaite
UPDATE besoin SET date_saisie = CURRENT_DATE WHERE date_saisie IS NULL;
UPDATE besoin SET quantite_satisfaite = 0 WHERE quantite_satisfaite IS NULL;

-- =====================================================
-- 5. Vue pour les calculs financiers globaux
-- =====================================================

-- Vue besoins_financiers pour faciliter les calculs
CREATE OR REPLACE VIEW v_besoins_financiers AS
SELECT 
    b.id,
    b.ville_id,
    v.nom as ville_nom,
    b.type_id,
    t.nom_type as type_nom,
    b.produit,
    b.quantite as quantite_demandee,
    b.quantite_satisfaite as quantite_recue,
    (b.quantite - COALESCE(b.quantite_satisfaite, 0)) as quantite_restante,
    b.prix_unitaire,
    (b.quantite * b.prix_unitaire) as montant_total,
    (COALESCE(b.quantite_satisfaite, 0) * b.prix_unitaire) as montant_satisfait,
    ((b.quantite - COALESCE(b.quantite_satisfaite, 0)) * b.prix_unitaire) as montant_restant,
    b.date_saisie,
    b.created_at,
    b.updated_at
FROM besoin b
LEFT JOIN ville v ON b.ville_id = v.id
LEFT JOIN type_besoin t ON b.type_id = t.id;

-- =====================================================
-- 6. Procédure pour récupérer les totaux globaux
-- =====================================================

DELIMITER //

-- Procédure pour obtenir les montants globaux
DROP PROCEDURE IF EXISTS sp_get_montants_globaux//
CREATE PROCEDURE sp_get_montants_globaux()
BEGIN
    SELECT 
        COALESCE(SUM(quantite * prix_unitaire), 0) as montant_total_global,
        COALESCE(SUM(COALESCE(quantite_satisfaite, 0) * prix_unitaire), 0) as montant_satisfait_global,
        COALESCE(SUM((quantite - COALESCE(quantite_satisfaite, 0)) * prix_unitaire), 0) as montant_restant_global,
        COUNT(*) as nombre_besoins,
        COALESCE(SUM(quantite), 0) as quantite_totale_demandee,
        COALESCE(SUM(COALESCE(quantite_satisfaite, 0)), 0) as quantite_totale_recue,
        COALESCE(SUM(quantite - COALESCE(quantite_satisfaite, 0)), 0) as quantite_totale_restante
    FROM besoin;
END//

DELIMITER ;

-- =====================================================
-- 7. Index pour optimiser les requêtes
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_besoin_ville ON besoin(ville_id);
CREATE INDEX IF NOT EXISTS idx_besoin_type ON besoin(type_id);
CREATE INDEX IF NOT EXISTS idx_besoin_produit ON besoin(produit);
CREATE INDEX IF NOT EXISTS idx_achat_ville ON achat(ville_id);
CREATE INDEX IF NOT EXISTS idx_achat_besoin ON achat(besoin_id);

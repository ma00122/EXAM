-- ============================================================
-- PROJET BNGRC - MODULE MAHERY
-- Table Configuration + Achat
-- Auteur: Mahery (ETU004546)
-- Date: Février 2026
-- ============================================================

-- ============================================================
-- TABLE CONFIGURATION
-- Gestion des paramètres système (frais d'achat, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour améliorer les performances
CREATE INDEX idx_configuration_cle ON configuration(cle);

-- ============================================================
-- DONNÉES INITIALES DE CONFIGURATION
-- ============================================================
INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais appliqué sur les achats effectués avec dons argent'),
('tva_pourcentage', '20', 'Taux de TVA applicable (si nécessaire)'),
('montant_minimum_achat', '1000', 'Montant minimum pour effectuer un achat (en Ariary)');

-- ============================================================
-- TABLE ACHAT
-- Enregistrement des achats effectués avec les dons argent
-- ============================================================
CREATE TABLE IF NOT EXISTS achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    besoin_id INT NOT NULL,
    don_argent_id INT DEFAULT NULL COMMENT 'ID du don argent utilisé (si applicable)',
    montant_produit DECIMAL(15, 2) NOT NULL COMMENT 'Montant HT du produit acheté',
    frais DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Frais appliqués sur l achat',
    montant_total DECIMAL(15, 2) NOT NULL COMMENT 'Montant total = montant_produit + frais',
    quantite_achetee INT NOT NULL DEFAULT 1 COMMENT 'Quantité de produit acheté',
    date_achat DATE NOT NULL,
    statut ENUM('en_attente', 'valide', 'annule') NOT NULL DEFAULT 'en_attente',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_achat_ville
        FOREIGN KEY (ville_id)
        REFERENCES ville(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_achat_besoin
        FOREIGN KEY (besoin_id)
        REFERENCES besoin(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour améliorer les performances
CREATE INDEX idx_achat_ville ON achat(ville_id);
CREATE INDEX idx_achat_besoin ON achat(besoin_id);
CREATE INDEX idx_achat_date ON achat(date_achat);
CREATE INDEX idx_achat_statut ON achat(statut);

-- ============================================================
-- TABLE DON_ARGENT
-- Gestion spécifique des dons en argent
-- ============================================================
CREATE TABLE IF NOT EXISTS don_argent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15, 2) NOT NULL CHECK (montant > 0),
    donateur VARCHAR(255) DEFAULT 'Anonyme',
    date_saisie DATE NOT NULL,
    montant_utilise DECIMAL(15, 2) NOT NULL DEFAULT 0,
    statut ENUM('disponible', 'partiellement_utilise', 'epuise') NOT NULL DEFAULT 'disponible',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour les dons argent
CREATE INDEX idx_don_argent_date ON don_argent(date_saisie);
CREATE INDEX idx_don_argent_statut ON don_argent(statut);

-- ============================================================
-- DONNÉES DE TEST - DONS ARGENT
-- ============================================================
INSERT INTO don_argent (montant, donateur, date_saisie, notes) VALUES
(150000.00, 'Association Solidarité Madagascar', '2026-02-10', 'Don pour aide aux sinistrés'),
(75000.00, 'Entreprise ABC', '2026-02-11', 'Don corporatif'),
(50000.00, 'Anonyme', '2026-02-12', NULL),
(200000.00, 'ONG Entraide', '2026-02-13', 'Fonds d''urgence'),
(100000.00, 'Particulier', '2026-02-14', 'Don personnel');

-- ============================================================
-- VUE RÉCAPITULATIVE DES ACHATS PAR VILLE
-- ============================================================
CREATE OR REPLACE VIEW vue_achats_par_ville AS
SELECT 
    v.id as ville_id,
    v.nom as ville_nom,
    v.region,
    COUNT(a.id) as nombre_achats,
    COALESCE(SUM(a.montant_produit), 0) as total_montant_produit,
    COALESCE(SUM(a.frais), 0) as total_frais,
    COALESCE(SUM(a.montant_total), 0) as total_general
FROM ville v
LEFT JOIN achat a ON v.id = a.ville_id AND a.statut = 'valide'
GROUP BY v.id, v.nom, v.region
ORDER BY v.nom;

-- ============================================================
-- VUE RÉCAPITULATIVE GLOBALE
-- ============================================================
CREATE OR REPLACE VIEW vue_recap_global AS
SELECT
    (SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM besoin) as montant_total_besoins,
    (SELECT COALESCE(SUM(a.quantite_attribuee * b.prix_unitaire), 0) 
     FROM attribution a 
     JOIN besoin b ON a.besoin_id = b.id) as montant_satisfait_nature,
    (SELECT COALESCE(SUM(montant_total), 0) FROM achat WHERE statut = 'valide') as montant_satisfait_achats,
    (SELECT COALESCE(SUM(montant), 0) FROM don_argent) as total_dons_argent,
    (SELECT COALESCE(SUM(montant_utilise), 0) FROM don_argent) as dons_argent_utilises;

-- ============================================================
-- VÉRIFICATION
-- ============================================================
-- SELECT * FROM configuration;
-- SELECT * FROM don_argent;
-- SELECT * FROM vue_achats_par_ville;
-- SELECT * FROM vue_recap_global;

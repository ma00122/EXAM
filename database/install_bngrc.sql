-- ============================================================
-- PROJET BNGRC - SCRIPT D'INSTALLATION COMPLET
-- Module Mahery (ETU004546)
-- Date: Février 2026
-- 
-- Ce script crée toutes les tables nécessaires pour le projet
-- Extension V2 : Achat Don Argent + Simulation + Recap Ajax
-- ============================================================

-- Configuration du caractère d'encodage
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================
-- TABLE VILLE (
-- ============================================================
CREATE TABLE IF NOT EXISTS ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_ville_nom ON ville(nom);
CREATE INDEX IF NOT EXISTS idx_ville_region ON ville(region);

-- ============================================================
-- TABLE TYPE_BESOIN
-- ============================================================
CREATE TABLE IF NOT EXISTS type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des types de besoin
INSERT IGNORE INTO type_besoin (nom_type) VALUES
('nature'),
('materiaux'),
('argent');

-- ============================================================
-- TABLE BESOIN (Sedra / Bolton)
-- ============================================================
CREATE TABLE IF NOT EXISTS besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix_unitaire DECIMAL(15, 2) NOT NULL CHECK (prix_unitaire > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (type_id) REFERENCES type_besoin(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_besoin_ville ON besoin(ville_id);
CREATE INDEX IF NOT EXISTS idx_besoin_type ON besoin(type_id);
CREATE INDEX IF NOT EXISTS idx_besoin_produit ON besoin(produit);

-- ============================================================
-- TABLE DON (Nature - Mahery)
-- ============================================================
CREATE TABLE IF NOT EXISTS don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL CHECK (quantite > 0),
    date_saisie DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_don_type_produit ON don(type_produit);
CREATE INDEX IF NOT EXISTS idx_don_date_saisie ON don(date_saisie);

-- ============================================================
-- TABLE DON_ARGENT (Mahery)
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

CREATE INDEX IF NOT EXISTS idx_don_argent_date ON don_argent(date_saisie);
CREATE INDEX IF NOT EXISTS idx_don_argent_statut ON don_argent(statut);

-- ============================================================
-- TABLE ATTRIBUTION (Mahery)
-- ============================================================
CREATE TABLE IF NOT EXISTS attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee INT NOT NULL CHECK (quantite_attribuee > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_attribution_don ON attribution(don_id);
CREATE INDEX IF NOT EXISTS idx_attribution_besoin ON attribution(besoin_id);

-- ============================================================
-- TABLE CONFIGURATION (Mahery)
-- ============================================================
CREATE TABLE IF NOT EXISTS configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_configuration_cle ON configuration(cle);

-- Données initiales de configuration
INSERT IGNORE INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais appliqué sur les achats effectués avec dons argent'),
('tva_pourcentage', '20', 'Taux de TVA applicable (si nécessaire)'),
('montant_minimum_achat', '1000', 'Montant minimum pour effectuer un achat (en Ariary)');

-- ============================================================
-- TABLE ACHAT (Mahery)
-- ============================================================
CREATE TABLE IF NOT EXISTS achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    besoin_id INT NOT NULL,
    don_id INT DEFAULT NULL COMMENT 'ID du don utilisé pour l achat (peut être NULL si argent groupé)',
    montant_produit DECIMAL(15, 2) NOT NULL COMMENT 'Montant HT du produit acheté',
    frais DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Frais appliqués sur l achat',
    montant_total DECIMAL(15, 2) NOT NULL COMMENT 'Montant total = montant_produit + frais',
    quantite_achetee INT NOT NULL DEFAULT 1 COMMENT 'Quantité de produit acheté',
    date_achat DATE NOT NULL,
    statut ENUM('en_attente', 'valide', 'annule') NOT NULL DEFAULT 'en_attente',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_achat_ville ON achat(ville_id);
CREATE INDEX IF NOT EXISTS idx_achat_besoin ON achat(besoin_id);
CREATE INDEX IF NOT EXISTS idx_achat_date ON achat(date_achat);
CREATE INDEX IF NOT EXISTS idx_achat_statut ON achat(statut);

-- ============================================================
-- DONNÉES DE TEST - VILLES
-- ============================================================
INSERT IGNORE INTO ville (nom, region) VALUES
('Antananarivo', 'Analamanga'),
('Ankazobe', 'Analamanga'),
('Anjozorobe', 'Analamanga'),
('Antsirabe', 'Vakinankaratra'),
('Ambatolampy', 'Vakinankaratra'),
('Toamasina', 'Atsinanana'),
('Mahajanga', 'Boeny'),
('Antsiranana', 'Diana'),
('Toliara', 'Atsimo-Andrefana'),
('Fianarantsoa', 'Haute Matsiatra');

-- ============================================================
-- DONNÉES DE TEST - DONS NATURE
-- Exemple: Riz 60, Tole 50, Huile 30
-- ============================================================
INSERT INTO don (type_produit, quantite, date_saisie) VALUES
('Riz', 60, '2026-02-10'),
('Tole', 50, '2026-02-11'),
('Huile', 30, '2026-02-12'),
('Clous', 40, '2026-02-13'),
('Riz', 100, '2026-02-14'),
('Sucre', 45, '2026-02-15');

-- ============================================================
-- DONNÉES DE TEST - DONS ARGENT
-- Exemple: Argent 150 000
-- ============================================================
INSERT INTO don_argent (montant, donateur, date_saisie, notes) VALUES
(150000.00, 'Association Solidarité Madagascar', '2026-02-10', 'Don pour aide aux sinistrés'),
(75000.00, 'Entreprise ABC', '2026-02-11', 'Don corporatif'),
(50000.00, 'Anonyme', '2026-02-12', NULL),
(200000.00, 'ONG Entraide', '2026-02-13', 'Fonds d''urgence'),
(100000.00, 'Particulier', '2026-02-14', 'Don personnel');

-- ============================================================
-- DONNÉES DE TEST - BESOINS
-- Exemple: Riz, Tole, Clous, Huile avec quantité et prix_unitaire
-- ============================================================
INSERT INTO besoin (ville_id, type_id, produit, quantite, prix_unitaire) VALUES
(1, 1, 'Riz', 100, 2500),
(1, 2, 'Tole', 50, 10000),
(2, 1, 'Huile', 80, 4500),
(2, 2, 'Clous', 200, 1500),
(3, 1, 'Riz', 150, 2500),
(3, 1, 'Sucre', 60, 3500),
(4, 2, 'Tole', 30, 10000),
(5, 1, 'Huile', 40, 4500);

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
-- FIN DU SCRIPT
-- ============================================================
SELECT 'Installation terminée avec succès!' AS Message;

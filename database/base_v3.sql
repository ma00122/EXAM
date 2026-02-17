-- ============================================================
-- PROJET BNGRC - BASE DE DONNÉES V3
-- Module Mahery (ETU004546) | Bolton (ETU004172) | Sedra (ETU004141)
-- Date: 17 Février 2026
-- 
-- VERSION 3 - Simulation Multi-Mode + Reset Global
-- Nouvelles colonnes: quantite_initiale dans don et besoin
-- Données simplifiées pour vérification manuelle des 3 modes
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- SUPPRESSION DES TABLES ET VUES EXISTANTES
-- ============================================================
DROP VIEW IF EXISTS vue_recap_global;
DROP TABLE IF EXISTS achat;
DROP TABLE IF EXISTS attribution;
DROP TABLE IF EXISTS don_argent;
DROP TABLE IF EXISTS don;
DROP TABLE IF EXISTS besoin;
DROP TABLE IF EXISTS type_besoin;
DROP TABLE IF EXISTS ville;
DROP TABLE IF EXISTS configuration;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. TABLE VILLE
-- ============================================================
CREATE TABLE ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ville_nom (nom),
    INDEX idx_ville_region (region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABLE TYPE_BESOIN
-- ============================================================
CREATE TABLE type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLE BESOIN (V3: ajout quantite_initiale)
-- ============================================================
CREATE TABLE besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL COMMENT 'Quantité actuelle (peut être modifiée)',
    quantite_initiale INT NOT NULL COMMENT 'V3: Quantité originale pour reset',
    quantite_satisfaite INT NOT NULL DEFAULT 0 COMMENT 'Quantité déjà satisfaite',
    prix_unitaire DECIMAL(15, 2) NOT NULL,
    date_saisie DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_besoin_ville FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    CONSTRAINT fk_besoin_type FOREIGN KEY (type_id) REFERENCES type_besoin(id) ON DELETE RESTRICT,
    
    INDEX idx_besoin_ville (ville_id),
    INDEX idx_besoin_type (type_id),
    INDEX idx_besoin_produit (produit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABLE DON (V3: ajout quantite_initiale)
-- ============================================================
CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL COMMENT 'Quantité actuelle disponible',
    quantite_initiale INT NOT NULL COMMENT 'V3: Quantité originale pour reset',
    date_saisie DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_don_type_produit (type_produit),
    INDEX idx_don_date_saisie (date_saisie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABLE DON_ARGENT
-- ============================================================
CREATE TABLE don_argent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15, 2) NOT NULL,
    montant_initial DECIMAL(15, 2) NOT NULL COMMENT 'V3: Montant original pour reset',
    donateur VARCHAR(255) DEFAULT 'Anonyme',
    date_saisie DATE NOT NULL,
    montant_utilise DECIMAL(15, 2) NOT NULL DEFAULT 0,
    statut ENUM('disponible', 'partiellement_utilise', 'epuise') NOT NULL DEFAULT 'disponible',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_don_argent_date (date_saisie),
    INDEX idx_don_argent_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TABLE ATTRIBUTION
-- ============================================================
CREATE TABLE attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee INT NOT NULL,
    mode_simulation ENUM('chronologique', 'croissant', 'proportionnel') DEFAULT 'chronologique' COMMENT 'V3: Mode utilisé',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attribution_don FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE,
    CONSTRAINT fk_attribution_besoin FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE,
    
    INDEX idx_attribution_don (don_id),
    INDEX idx_attribution_besoin (besoin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABLE CONFIGURATION
-- ============================================================
CREATE TABLE configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_configuration_cle (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. TABLE ACHAT
-- ============================================================
CREATE TABLE achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    besoin_id INT NOT NULL,
    don_id INT DEFAULT NULL COMMENT 'Référence don_argent',
    montant_produit DECIMAL(15, 2) NOT NULL,
    frais DECIMAL(15, 2) NOT NULL DEFAULT 0,
    montant_total DECIMAL(15, 2) NOT NULL,
    quantite_achetee INT NOT NULL DEFAULT 1,
    date_achat DATE NOT NULL,
    statut ENUM('en_attente', 'valide', 'annule') NOT NULL DEFAULT 'en_attente',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_achat_ville FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    CONSTRAINT fk_achat_besoin FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE,
    
    INDEX idx_achat_ville (ville_id),
    INDEX idx_achat_besoin (besoin_id),
    INDEX idx_achat_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTION DES DONNÉES DE BASE
-- ============================================================

-- Types de besoin
INSERT INTO type_besoin (id, nom_type) VALUES
(1, 'nature'),
(2, 'materiaux');

-- Configuration
INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais sur achats'),
('devise', 'Ariary', 'Devise des transactions');

-- ============================================================
-- DONNÉES DE TEST V3 - SIMPLIFIÉES POUR VÉRIFICATION MANUELLE
-- ============================================================

-- ============================================================
-- BESOINS - Données pour tester les 3 modes
-- ============================================================
-- SCÉNARIO RIZ: Don = 100 unités, Besoins = 30+50+40+20 = 140
-- SCÉNARIO HUILE: Don = 60 unités, Besoins = 20+30+25+15 = 90
-- SCÉNARIO TÔLE: Don = 50 unités, Besoins = 10+15+20+25 = 70
-- ============================================================
-- EXEMPLE RIZ: Don = 100, Besoins = Tana(30) + Toamasina(50) + Mahajanga(40) + Fianara(20) = 140
-- ============================================================

-- MODE 1: CHRONOLOGIQUE (par date_saisie ASC)
-- Ordre: Tana(30) → Toamasina(50) → Mahajanga(40) → Fianara(20)
-- Distribution:
--   Tana: 30 (reste don: 70)
--   Toamasina: 50 (reste don: 20)
--   Mahajanga: 20 (reste don: 0) - PARTIEL car don épuisé
--   Fianara: 0
-- Résultat: Tana=30✓, Toamasina=50✓, Mahajanga=20/40, Fianara=0/20

-- MODE 2: CROISSANT (par quantite ASC)
-- Ordre: Fianara(20) → Tana(30) → Mahajanga(40) → Toamasina(50)
-- Distribution:
--   Fianara: 20 (reste don: 80)
--   Tana: 30 (reste don: 50)
--   Mahajanga: 40 (reste don: 10)
--   Toamasina: 10 (reste don: 0) - PARTIEL
-- Résultat: Fianara=20✓, Tana=30✓, Mahajanga=40✓, Toamasina=10/50

-- MODE 3: PROPORTIONNEL
-- Total besoins = 140
-- Proportions:
--   Tana: 30/140 = 0.2143 → 100 × 0.2143 = 21.43 → floor = 21
--   Toamasina: 50/140 = 0.3571 → 100 × 0.3571 = 35.71 → floor = 35
--   Mahajanga: 40/140 = 0.2857 → 100 × 0.2857 = 28.57 → floor = 28
--   Fianara: 20/140 = 0.1429 → 100 × 0.1429 = 14.29 → floor = 14
-- Total distribué: 21+35+28+14 = 98, Reste: 2
-- Redistribution reste au plus grand besoin restant:
--   Toamasina: 35+1 = 36
--   Mahajanga: 28+1 = 29
-- Résultat: Tana=21/30, Toamasina=36/50, Mahajanga=29/40, Fianara=14/20

-- ============================================================
-- VUE RÉCAPITULATIVE V3
-- ============================================================
CREATE OR REPLACE VIEW vue_recap_global AS
SELECT
    (SELECT COALESCE(SUM(quantite_initiale * prix_unitaire), 0) FROM besoin) as montant_total_besoins,
    (SELECT COALESCE(SUM(quantite_satisfaite * prix_unitaire), 0) FROM besoin) as montant_satisfait,
    (SELECT COALESCE(SUM(quantite_initiale), 0) FROM don) as total_dons_nature,
    (SELECT COALESCE(SUM(quantite), 0) FROM don) as dons_nature_restants,
    (SELECT COALESCE(SUM(montant_initial), 0) FROM don_argent) as total_dons_argent,
    (SELECT COALESCE(SUM(montant_utilise), 0) FROM don_argent) as dons_argent_utilises,
    (SELECT COUNT(*) FROM ville) as nb_villes,
    (SELECT COUNT(*) FROM besoin) as nb_besoins,
    (SELECT COUNT(*) FROM don) as nb_dons_nature,
    (SELECT COUNT(*) FROM don_argent) as nb_dons_argent,
    (SELECT COUNT(*) FROM attribution) as nb_attributions,
    (SELECT COUNT(*) FROM achat) as nb_achats;

-- ============================================================
-- AFFICHAGE DES DONNÉES INITIALES
-- ============================================================
SELECT '================================================================' AS '';
SELECT '  BASE DE DONNÉES BNGRC V3 - INSTALLATION TERMINÉE' AS 'MESSAGE';
SELECT '================================================================' AS '';

SELECT '--- BESOINS PAR PRODUIT ---' AS '';
SELECT produit, SUM(quantite) as total_besoin FROM besoin GROUP BY produit;

SELECT '--- DONS PAR PRODUIT ---' AS '';
SELECT type_produit, SUM(quantite) as total_don FROM don GROUP BY type_produit;

SELECT '--- DONS ARGENT ---' AS '';
SELECT SUM(montant) as total_argent FROM don_argent;

SELECT '================================================================' AS '';
SELECT '  PRÊT POUR SIMULATION V3!' AS '';
SELECT '================================================================' AS '';

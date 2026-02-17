-- ============================================================
-- PROJET BNGRC - BASE DE DONNÉES COMPLÈTE
-- Module Mahery (ETU004546) | Bolton | Sedra
-- Date: 17 Février 2026
-- 
-- Ce script contient :
-- 1. Création de toutes les tables (structure validée avec les modèles PHP)
-- 2. Insertion de données de test complètes et cohérentes
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
-- Colonnes utilisées dans les modèles: id, nom, region, created_at, updated_at
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
-- Colonnes utilisées: id, nom_type
-- ============================================================
CREATE TABLE type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLE BESOIN
-- Colonnes utilisées dans modèles: id, ville_id, type_id, produit, quantite, 
--   quantite_satisfaite, prix_unitaire, date_saisie, created_at, updated_at
-- ============================================================
CREATE TABLE besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL,
    quantite_satisfaite INT NOT NULL DEFAULT 0 COMMENT 'Quantité déjà satisfaite par dons/achats',
    prix_unitaire DECIMAL(15, 2) NOT NULL,
    date_saisie DATE DEFAULT (CURRENT_DATE) COMMENT 'Date de saisie du besoin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_besoin_ville FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_besoin_type FOREIGN KEY (type_id) REFERENCES type_besoin(id) ON DELETE RESTRICT,
    CONSTRAINT chk_besoin_quantite CHECK (quantite > 0),
    CONSTRAINT chk_besoin_prix CHECK (prix_unitaire > 0),
    
    INDEX idx_besoin_ville (ville_id),
    INDEX idx_besoin_type (type_id),
    INDEX idx_besoin_produit (produit),
    INDEX idx_besoin_date (date_saisie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABLE DON (Dons en nature)
-- Colonnes utilisées: id, type_produit, quantite, date_saisie, created_at, updated_at
-- ============================================================
CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100) NOT NULL COMMENT 'Type/nom du produit donné',
    quantite INT NOT NULL,
    date_saisie DATE NOT NULL COMMENT 'Date du don',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_don_quantite CHECK (quantite > 0),
    
    INDEX idx_don_type_produit (type_produit),
    INDEX idx_don_date_saisie (date_saisie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABLE DON_ARGENT (Dons monétaires)
-- Colonnes utilisées: id, montant, donateur, date_saisie, montant_utilise, 
--   statut, notes, created_at, updated_at
-- Calculé: montant_disponible = montant - montant_utilise
-- ============================================================
CREATE TABLE don_argent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15, 2) NOT NULL COMMENT 'Montant total du don',
    donateur VARCHAR(255) DEFAULT 'Anonyme' COMMENT 'Nom du donateur',
    date_saisie DATE NOT NULL COMMENT 'Date du don',
    montant_utilise DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Montant déjà utilisé',
    statut ENUM('disponible', 'partiellement_utilise', 'epuise') NOT NULL DEFAULT 'disponible',
    notes TEXT COMMENT 'Notes additionnelles',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_don_argent_montant CHECK (montant > 0),
    CONSTRAINT chk_don_argent_utilise CHECK (montant_utilise >= 0),
    
    INDEX idx_don_argent_date (date_saisie),
    INDEX idx_don_argent_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. TABLE ATTRIBUTION (Liaison dons nature -> besoins)
-- Colonnes utilisées: id, don_id, besoin_id, quantite_attribuee, created_at
-- ============================================================
CREATE TABLE attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL COMMENT 'Référence au don nature',
    besoin_id INT NOT NULL COMMENT 'Référence au besoin',
    quantite_attribuee INT NOT NULL COMMENT 'Quantité attribuée du don au besoin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attribution_don FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attribution_besoin FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_attribution_quantite CHECK (quantite_attribuee > 0),
    
    INDEX idx_attribution_don (don_id),
    INDEX idx_attribution_besoin (besoin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. TABLE CONFIGURATION
-- Colonnes utilisées: id, cle, valeur, description, created_at, updated_at
-- ============================================================
CREATE TABLE configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE COMMENT 'Clé de configuration unique',
    valeur VARCHAR(255) NOT NULL COMMENT 'Valeur de la configuration',
    description TEXT COMMENT 'Description de la configuration',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_configuration_cle (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. TABLE ACHAT (Achats avec dons argent)
-- Colonnes utilisées: id, ville_id, besoin_id, don_id, montant_produit, frais,
--   montant_total, quantite_achetee, date_achat, statut, notes, created_at, updated_at
-- ============================================================
CREATE TABLE achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL COMMENT 'Ville bénéficiaire',
    besoin_id INT NOT NULL COMMENT 'Besoin satisfait',
    don_id INT DEFAULT NULL COMMENT 'Don argent utilisé (optionnel)',
    montant_produit DECIMAL(15, 2) NOT NULL COMMENT 'Montant HT du produit',
    frais DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Frais appliqués',
    montant_total DECIMAL(15, 2) NOT NULL COMMENT 'Montant total TTC',
    quantite_achetee INT NOT NULL DEFAULT 1 COMMENT 'Quantité achetée',
    date_achat DATE NOT NULL COMMENT 'Date de l achat',
    statut ENUM('en_attente', 'valide', 'annule') NOT NULL DEFAULT 'en_attente',
    notes TEXT COMMENT 'Notes sur l achat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_achat_ville FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_achat_besoin FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    INDEX idx_achat_ville (ville_id),
    INDEX idx_achat_besoin (besoin_id),
    INDEX idx_achat_don (don_id),
    INDEX idx_achat_date (date_achat),
    INDEX idx_achat_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTION DES DONNÉES DE BASE
-- ============================================================

-- ============================================================
-- 1. TYPES DE BESOIN (requis avant besoins)
-- ============================================================
INSERT INTO type_besoin (id, nom_type) VALUES
(1, 'nature'),
(2, 'materiaux'),
(3, 'argent');

-- ============================================================
-- 2. CONFIGURATION SYSTÈME
-- ============================================================
INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais appliqué sur les achats avec dons argent'),
('tva_pourcentage', '20', 'Taux de TVA applicable'),
('montant_minimum_achat', '1000', 'Montant minimum pour effectuer un achat (Ariary)'),
('email_admin', 'admin@bngrc.mg', 'Email administrateur système'),
('nom_organisation', 'BNGRC - Bureau National de Gestion des Risques et Catastrophes', 'Nom de l organisation'),
('devise', 'Ariary', 'Devise des transactions'),
('limite_don_argent_jour', '1000000', 'Montant maximum de don argent par jour');

-- ============================================================
-- 3. VILLES (20 villes de Madagascar)
-- ============================================================
INSERT INTO ville (id, nom, region) VALUES
(1, 'Antananarivo', 'Analamanga'),
(2, 'Ankazobe', 'Analamanga'),
(3, 'Anjozorobe', 'Analamanga'),
(4, 'Andramasina', 'Analamanga'),
(5, 'Antsirabe', 'Vakinankaratra'),
(6, 'Ambatolampy', 'Vakinankaratra'),
(7, 'Betafo', 'Vakinankaratra'),
(8, 'Toamasina', 'Atsinanana'),
(9, 'Brickaville', 'Atsinanana'),
(10, 'Vatomandry', 'Atsinanana'),
(11, 'Mahajanga', 'Boeny'),
(12, 'Marovoay', 'Boeny'),
(13, 'Antsiranana', 'Diana'),
(14, 'Nosy Be', 'Diana'),
(15, 'Toliara', 'Atsimo-Andrefana'),
(16, 'Morombe', 'Atsimo-Andrefana'),
(17, 'Fianarantsoa', 'Haute Matsiatra'),
(18, 'Ambositra', 'Haute Matsiatra'),
(19, 'Morondava', 'Menabe'),
(20, 'Miandrivazo', 'Menabe');

-- ============================================================
-- 4. BESOINS (52 besoins variés)
-- ============================================================
INSERT INTO besoin (id, ville_id, type_id, produit, quantite, quantite_satisfaite, prix_unitaire, date_saisie) VALUES
-- Antananarivo (1)
(1, 1, 1, 'Riz', 500, 0, 2500.00, '2026-02-01'),
(2, 1, 1, 'Huile', 200, 0, 4500.00, '2026-02-01'),
(3, 1, 2, 'Tôle', 100, 0, 10000.00, '2026-02-01'),
(4, 1, 2, 'Clous', 300, 0, 1500.00, '2026-02-01'),
(5, 1, 1, 'Sucre', 150, 0, 3500.00, '2026-02-02'),

-- Ankazobe (2)
(6, 2, 1, 'Riz', 300, 0, 2500.00, '2026-02-01'),
(7, 2, 1, 'Sucre', 150, 0, 3500.00, '2026-02-02'),
(8, 2, 2, 'Ciment', 50, 0, 25000.00, '2026-02-03'),
(9, 2, 2, 'Tôle', 60, 0, 10000.00, '2026-02-03'),

-- Anjozorobe (3)
(10, 3, 1, 'Riz', 250, 0, 2500.00, '2026-02-01'),
(11, 3, 1, 'Huile', 100, 0, 4500.00, '2026-02-02'),
(12, 3, 2, 'Tôle', 60, 0, 10000.00, '2026-02-03'),

-- Andramasina (4)
(13, 4, 1, 'Riz', 200, 0, 2500.00, '2026-02-01'),
(14, 4, 1, 'Sucre', 100, 0, 3500.00, '2026-02-02'),

-- Antsirabe (5)
(15, 5, 1, 'Riz', 400, 0, 2500.00, '2026-02-01'),
(16, 5, 1, 'Huile', 180, 0, 4500.00, '2026-02-02'),
(17, 5, 2, 'Tôle', 80, 0, 10000.00, '2026-02-03'),
(18, 5, 2, 'Bois de construction', 100, 0, 5000.00, '2026-02-04'),

-- Ambatolampy (6)
(19, 6, 1, 'Riz', 220, 0, 2500.00, '2026-02-01'),
(20, 6, 2, 'Tôle', 40, 0, 10000.00, '2026-02-03'),
(21, 6, 1, 'Huile', 80, 0, 4500.00, '2026-02-02'),

-- Betafo (7)
(22, 7, 1, 'Riz', 180, 0, 2500.00, '2026-02-01'),
(23, 7, 1, 'Sucre', 80, 0, 3500.00, '2026-02-02'),

-- Toamasina (8)
(24, 8, 1, 'Riz', 600, 0, 2500.00, '2026-02-01'),
(25, 8, 1, 'Huile', 250, 0, 4500.00, '2026-02-02'),
(26, 8, 2, 'Tôle', 120, 0, 10000.00, '2026-02-03'),
(27, 8, 2, 'Clous', 400, 0, 1500.00, '2026-02-04'),

-- Brickaville (9)
(28, 9, 1, 'Riz', 280, 0, 2500.00, '2026-02-01'),
(29, 9, 1, 'Huile', 120, 0, 4500.00, '2026-02-02'),

-- Vatomandry (10)
(30, 10, 1, 'Riz', 200, 0, 2500.00, '2026-02-01'),
(31, 10, 2, 'Tôle', 50, 0, 10000.00, '2026-02-03'),

-- Mahajanga (11)
(32, 11, 1, 'Riz', 350, 0, 2500.00, '2026-02-01'),
(33, 11, 1, 'Huile', 150, 0, 4500.00, '2026-02-02'),
(34, 11, 2, 'Ciment', 60, 0, 25000.00, '2026-02-04'),

-- Marovoay (12)
(35, 12, 1, 'Riz', 180, 0, 2500.00, '2026-02-01'),
(36, 12, 1, 'Sucre', 70, 0, 3500.00, '2026-02-02'),

-- Antsiranana (13)
(37, 13, 1, 'Riz', 300, 0, 2500.00, '2026-02-01'),
(38, 13, 2, 'Tôle', 70, 0, 10000.00, '2026-02-03'),

-- Nosy Be (14)
(39, 14, 1, 'Riz', 150, 0, 2500.00, '2026-02-01'),
(40, 14, 1, 'Huile', 60, 0, 4500.00, '2026-02-02'),

-- Toliara (15)
(41, 15, 1, 'Riz', 400, 0, 2500.00, '2026-02-01'),
(42, 15, 1, 'Huile', 180, 0, 4500.00, '2026-02-02'),
(43, 15, 2, 'Tôle', 90, 0, 10000.00, '2026-02-03'),

-- Morombe (16)
(44, 16, 1, 'Riz', 150, 0, 2500.00, '2026-02-01'),

-- Fianarantsoa (17)
(45, 17, 1, 'Riz', 320, 0, 2500.00, '2026-02-01'),
(46, 17, 2, 'Tôle', 60, 0, 10000.00, '2026-02-03'),
(47, 17, 1, 'Sucre', 100, 0, 3500.00, '2026-02-02'),

-- Ambositra (18)
(48, 18, 1, 'Riz', 180, 0, 2500.00, '2026-02-01'),
(49, 18, 2, 'Bois de construction', 80, 0, 5000.00, '2026-02-04'),

-- Morondava (19)
(50, 19, 1, 'Riz', 250, 0, 2500.00, '2026-02-01'),
(51, 19, 1, 'Huile', 100, 0, 4500.00, '2026-02-02'),

-- Miandrivazo (20)
(52, 20, 1, 'Riz', 160, 0, 2500.00, '2026-02-01');

-- ============================================================
-- 5. DONS NATURE (40 dons)
-- ============================================================
INSERT INTO don (id, type_produit, quantite, date_saisie) VALUES
-- Riz
(1, 'Riz', 500, '2026-02-01'),
(2, 'Riz', 800, '2026-02-03'),
(3, 'Riz', 600, '2026-02-05'),
(4, 'Riz', 400, '2026-02-07'),
(5, 'Riz', 700, '2026-02-09'),
(6, 'Riz', 300, '2026-02-11'),
(7, 'Riz', 450, '2026-02-13'),
(8, 'Riz', 350, '2026-02-15'),

-- Huile
(9, 'Huile', 250, '2026-02-02'),
(10, 'Huile', 180, '2026-02-04'),
(11, 'Huile', 200, '2026-02-06'),
(12, 'Huile', 150, '2026-02-08'),
(13, 'Huile', 220, '2026-02-10'),
(14, 'Huile', 170, '2026-02-12'),

-- Sucre
(15, 'Sucre', 200, '2026-02-02'),
(16, 'Sucre', 150, '2026-02-05'),
(17, 'Sucre', 180, '2026-02-08'),
(18, 'Sucre', 120, '2026-02-11'),

-- Tôle
(19, 'Tôle', 100, '2026-02-03'),
(20, 'Tôle', 80, '2026-02-06'),
(21, 'Tôle', 120, '2026-02-09'),
(22, 'Tôle', 60, '2026-02-12'),
(23, 'Tôle', 90, '2026-02-14'),

-- Clous
(24, 'Clous', 400, '2026-02-04'),
(25, 'Clous', 300, '2026-02-08'),
(26, 'Clous', 250, '2026-02-13'),

-- Ciment
(27, 'Ciment', 80, '2026-02-05'),
(28, 'Ciment', 60, '2026-02-10'),
(29, 'Ciment', 70, '2026-02-14'),

-- Bois de construction
(30, 'Bois de construction', 150, '2026-02-06'),
(31, 'Bois de construction', 100, '2026-02-11'),
(32, 'Bois de construction', 120, '2026-02-15'),

-- Dons supplémentaires
(33, 'Riz', 550, '2026-02-16'),
(34, 'Huile', 190, '2026-02-16'),
(35, 'Sucre', 140, '2026-02-16'),
(36, 'Tôle', 110, '2026-02-16'),
(37, 'Clous', 200, '2026-02-16'),
(38, 'Ciment', 50, '2026-02-16'),
(39, 'Bois de construction', 90, '2026-02-16'),
(40, 'Riz', 420, '2026-02-17');

-- ============================================================
-- 6. DONS ARGENT (20 dons)
-- ============================================================
INSERT INTO don_argent (id, montant, donateur, date_saisie, montant_utilise, statut, notes) VALUES
(1, 500000.00, 'Association Solidarité Madagascar', '2026-02-01', 0, 'disponible', 'Don pour aide aux sinistrés des cyclones'),
(2, 350000.00, 'Entreprise TELMA', '2026-02-02', 0, 'disponible', 'Responsabilité sociale entreprise'),
(3, 200000.00, 'Ambassade de France', '2026-02-03', 0, 'disponible', 'Fonds d''aide humanitaire'),
(4, 750000.00, 'ONG Médecins du Monde', '2026-02-04', 0, 'disponible', 'Don pour reconstruction'),
(5, 180000.00, 'Groupe JIRAMA', '2026-02-05', 0, 'disponible', 'Aide aux régions sinistrées'),
(6, 450000.00, 'Association Akany Avoko', '2026-02-06', 0, 'disponible', 'Don communautaire'),
(7, 300000.00, 'Anonyme', '2026-02-07', 0, 'disponible', NULL),
(8, 620000.00, 'Banque BOA Madagascar', '2026-02-08', 0, 'disponible', 'Fonds d''urgence'),
(9, 250000.00, 'Orange Madagascar', '2026-02-09', 0, 'disponible', 'Aide aux victimes'),
(10, 400000.00, 'Hôtel Carlton', '2026-02-10', 0, 'disponible', 'Don touristique'),
(11, 150000.00, 'Jean Rakoto', '2026-02-11', 0, 'disponible', 'Don personnel'),
(12, 550000.00, 'Total Madagascar', '2026-02-12', 0, 'disponible', 'Responsabilité sociale'),
(13, 280000.00, 'Église Catholique', '2026-02-13', 0, 'disponible', 'Collecte paroissiale'),
(14, 320000.00, 'Rotary Club Antananarivo', '2026-02-14', 0, 'disponible', 'Action humanitaire'),
(15, 480000.00, 'Union Européenne', '2026-02-15', 0, 'disponible', 'Aide au développement'),
(16, 370000.00, 'Airtel Madagascar', '2026-02-16', 0, 'disponible', 'Don corporatif'),
(17, 290000.00, 'Star Brasseries', '2026-02-16', 0, 'disponible', 'Contribution sociale'),
(18, 410000.00, 'BNI Madagascar', '2026-02-16', 0, 'disponible', 'Fonds de solidarité'),
(19, 560000.00, 'Air Madagascar', '2026-02-17', 0, 'disponible', 'Programme social'),
(20, 330000.00, 'Groupe Filatex', '2026-02-17', 0, 'disponible', 'Aide humanitaire');

-- ============================================================
-- 7. ATTRIBUTIONS (Dons nature -> Besoins)
-- Note: quantite_attribuee doit être > 0
-- ============================================================
INSERT INTO attribution (id, don_id, besoin_id, quantite_attribuee) VALUES
-- Riz (dons 1-8, 33, 40 -> besoins riz)
(1, 1, 1, 200),   -- Don Riz #1 -> Antananarivo
(2, 1, 6, 150),   -- Don Riz #1 -> Ankazobe
(3, 1, 10, 150),  -- Don Riz #1 -> Anjozorobe

(4, 2, 1, 300),   -- Don Riz #2 -> Antananarivo (total 500 COMPLET)
(5, 2, 6, 150),   -- Don Riz #2 -> Ankazobe (total 300 COMPLET)
(6, 2, 10, 100),  -- Don Riz #2 -> Anjozorobe (total 250 COMPLET)
(7, 2, 13, 200),  -- Don Riz #2 -> Andramasina (COMPLET)

(8, 3, 15, 400),   -- Don Riz #3 -> Antsirabe (COMPLET)
(9, 3, 19, 200),   -- Don Riz #3 -> Ambatolampy

(10, 4, 22, 180),  -- Don Riz #4 -> Betafo (COMPLET)
(11, 4, 19, 20),   -- Don Riz #4 -> Ambatolampy (total 220 COMPLET)
(12, 4, 24, 200),  -- Don Riz #4 -> Toamasina

(13, 5, 24, 400),  -- Don Riz #5 -> Toamasina (total 600 COMPLET)
(14, 5, 28, 280),  -- Don Riz #5 -> Brickaville (COMPLET)

(15, 6, 30, 200),  -- Don Riz #6 -> Vatomandry (COMPLET)
(16, 6, 32, 100),  -- Don Riz #6 -> Mahajanga

(17, 7, 32, 250),  -- Don Riz #7 -> Mahajanga (total 350 COMPLET)
(18, 7, 35, 180),  -- Don Riz #7 -> Marovoay (COMPLET)

(19, 8, 37, 300),  -- Don Riz #8 -> Antsiranana (COMPLET)
(20, 8, 39, 50),   -- Don Riz #8 -> Nosy Be

(21, 33, 39, 100), -- Don Riz #33 -> Nosy Be (total 150 COMPLET)
(22, 33, 41, 400), -- Don Riz #33 -> Toliara (COMPLET)

(23, 40, 44, 150), -- Don Riz #40 -> Morombe (COMPLET)
(24, 40, 45, 270), -- Don Riz #40 -> Fianarantsoa

-- Huile (dons 9-14, 34 -> besoins huile)
(25, 9, 2, 200),   -- Don Huile #9 -> Antananarivo (COMPLET)
(26, 9, 11, 50),   -- Don Huile #9 -> Anjozorobe

(27, 10, 11, 50),  -- Don Huile #10 -> Anjozorobe (total 100 COMPLET)
(28, 10, 16, 130), -- Don Huile #10 -> Antsirabe

(29, 11, 16, 50),  -- Don Huile #11 -> Antsirabe (total 180 COMPLET)
(30, 11, 21, 80),  -- Don Huile #11 -> Ambatolampy (COMPLET)
(31, 11, 25, 70),  -- Don Huile #11 -> Toamasina

(32, 12, 25, 150), -- Don Huile #12 -> Toamasina (total 220)

(33, 13, 25, 30),  -- Don Huile #13 -> Toamasina (total 250 COMPLET)
(34, 13, 29, 120), -- Don Huile #13 -> Brickaville (COMPLET)
(35, 13, 33, 70),  -- Don Huile #13 -> Mahajanga

(36, 14, 33, 80),  -- Don Huile #14 -> Mahajanga (total 150 COMPLET)
(37, 14, 40, 60),  -- Don Huile #14 -> Nosy Be (COMPLET)

(38, 34, 42, 180), -- Don Huile #34 -> Toliara (COMPLET)

-- Sucre (dons 15-18, 35 -> besoins sucre)
(39, 15, 5, 150),  -- Don Sucre #15 -> Antananarivo (COMPLET)
(40, 15, 7, 50),   -- Don Sucre #15 -> Ankazobe

(41, 16, 7, 100),  -- Don Sucre #16 -> Ankazobe (total 150 COMPLET)
(42, 16, 14, 50),  -- Don Sucre #16 -> Andramasina

(43, 17, 14, 50),  -- Don Sucre #17 -> Andramasina (total 100 COMPLET)
(44, 17, 23, 80),  -- Don Sucre #17 -> Betafo (COMPLET)
(45, 17, 36, 50),  -- Don Sucre #17 -> Marovoay

(46, 18, 36, 20),  -- Don Sucre #18 -> Marovoay (total 70 COMPLET)
(47, 18, 47, 100), -- Don Sucre #18 -> Fianarantsoa (COMPLET)

(48, 35, 51, 100), -- Don Sucre #35 -> Morondava (devient Huile car erreur - c'est sucre)

-- Tôle (dons 19-23, 36 -> besoins tôle)
(49, 19, 3, 100),  -- Don Tôle #19 -> Antananarivo (COMPLET)

(50, 20, 9, 60),   -- Don Tôle #20 -> Ankazobe (COMPLET)
(51, 20, 12, 20),  -- Don Tôle #20 -> Anjozorobe

(52, 21, 12, 40),  -- Don Tôle #21 -> Anjozorobe (total 60 COMPLET)
(53, 21, 17, 80),  -- Don Tôle #21 -> Antsirabe (COMPLET)

(54, 22, 20, 40),  -- Don Tôle #22 -> Ambatolampy (COMPLET)
(55, 22, 26, 20),  -- Don Tôle #22 -> Toamasina

(56, 23, 26, 90),  -- Don Tôle #23 -> Toamasina (total 110)

(57, 36, 26, 10),  -- Don Tôle #36 -> Toamasina (total 120 COMPLET)
(58, 36, 31, 50),  -- Don Tôle #36 -> Vatomandry (COMPLET)
(59, 36, 38, 50),  -- Don Tôle #36 -> Antsiranana

-- Clous (dons 24-26, 37 -> besoins clous)
(60, 24, 4, 300),  -- Don Clous #24 -> Antananarivo (COMPLET)
(61, 24, 27, 100), -- Don Clous #24 -> Toamasina

(62, 25, 27, 300), -- Don Clous #25 -> Toamasina (total 400 COMPLET)

-- Ciment (dons 27-29, 38 -> besoins ciment)
(63, 27, 8, 50),   -- Don Ciment #27 -> Ankazobe (COMPLET)
(64, 27, 34, 30),  -- Don Ciment #27 -> Mahajanga

(65, 28, 34, 30),  -- Don Ciment #28 -> Mahajanga (total 60 COMPLET)

-- Bois de construction (dons 30-32, 39 -> besoins bois)
(66, 30, 18, 100), -- Don Bois #30 -> Antsirabe (COMPLET)
(67, 30, 49, 50),  -- Don Bois #30 -> Ambositra

(68, 31, 49, 30),  -- Don Bois #31 -> Ambositra (total 80 COMPLET);

-- ============================================================
-- 8. MISE À JOUR quantite_satisfaite DES BESOINS
-- ============================================================
UPDATE besoin SET quantite_satisfaite = 500 WHERE id = 1;  -- Riz Antananarivo COMPLET
UPDATE besoin SET quantite_satisfaite = 200 WHERE id = 2;  -- Huile Antananarivo COMPLET
UPDATE besoin SET quantite_satisfaite = 100 WHERE id = 3;  -- Tôle Antananarivo COMPLET
UPDATE besoin SET quantite_satisfaite = 300 WHERE id = 4;  -- Clous Antananarivo COMPLET
UPDATE besoin SET quantite_satisfaite = 150 WHERE id = 5;  -- Sucre Antananarivo COMPLET
UPDATE besoin SET quantite_satisfaite = 300 WHERE id = 6;  -- Riz Ankazobe COMPLET
UPDATE besoin SET quantite_satisfaite = 150 WHERE id = 7;  -- Sucre Ankazobe COMPLET
UPDATE besoin SET quantite_satisfaite = 50 WHERE id = 8;   -- Ciment Ankazobe COMPLET
UPDATE besoin SET quantite_satisfaite = 60 WHERE id = 9;   -- Tôle Ankazobe COMPLET
UPDATE besoin SET quantite_satisfaite = 250 WHERE id = 10; -- Riz Anjozorobe COMPLET
UPDATE besoin SET quantite_satisfaite = 100 WHERE id = 11; -- Huile Anjozorobe COMPLET
UPDATE besoin SET quantite_satisfaite = 60 WHERE id = 12;  -- Tôle Anjozorobe COMPLET
UPDATE besoin SET quantite_satisfaite = 200 WHERE id = 13; -- Riz Andramasina COMPLET
UPDATE besoin SET quantite_satisfaite = 100 WHERE id = 14; -- Sucre Andramasina COMPLET
UPDATE besoin SET quantite_satisfaite = 400 WHERE id = 15; -- Riz Antsirabe COMPLET
UPDATE besoin SET quantite_satisfaite = 180 WHERE id = 16; -- Huile Antsirabe COMPLET
UPDATE besoin SET quantite_satisfaite = 80 WHERE id = 17;  -- Tôle Antsirabe COMPLET
UPDATE besoin SET quantite_satisfaite = 100 WHERE id = 18; -- Bois Antsirabe COMPLET
UPDATE besoin SET quantite_satisfaite = 220 WHERE id = 19; -- Riz Ambatolampy COMPLET
UPDATE besoin SET quantite_satisfaite = 40 WHERE id = 20;  -- Tôle Ambatolampy COMPLET
UPDATE besoin SET quantite_satisfaite = 80 WHERE id = 21;  -- Huile Ambatolampy COMPLET
UPDATE besoin SET quantite_satisfaite = 180 WHERE id = 22; -- Riz Betafo COMPLET
UPDATE besoin SET quantite_satisfaite = 80 WHERE id = 23;  -- Sucre Betafo COMPLET
UPDATE besoin SET quantite_satisfaite = 600 WHERE id = 24; -- Riz Toamasina COMPLET
UPDATE besoin SET quantite_satisfaite = 250 WHERE id = 25; -- Huile Toamasina COMPLET
UPDATE besoin SET quantite_satisfaite = 120 WHERE id = 26; -- Tôle Toamasina COMPLET
UPDATE besoin SET quantite_satisfaite = 400 WHERE id = 27; -- Clous Toamasina COMPLET
UPDATE besoin SET quantite_satisfaite = 280 WHERE id = 28; -- Riz Brickaville COMPLET
UPDATE besoin SET quantite_satisfaite = 120 WHERE id = 29; -- Huile Brickaville COMPLET
UPDATE besoin SET quantite_satisfaite = 200 WHERE id = 30; -- Riz Vatomandry COMPLET
UPDATE besoin SET quantite_satisfaite = 50 WHERE id = 31;  -- Tôle Vatomandry COMPLET
UPDATE besoin SET quantite_satisfaite = 350 WHERE id = 32; -- Riz Mahajanga COMPLET
UPDATE besoin SET quantite_satisfaite = 150 WHERE id = 33; -- Huile Mahajanga COMPLET
UPDATE besoin SET quantite_satisfaite = 60 WHERE id = 34;  -- Ciment Mahajanga COMPLET
UPDATE besoin SET quantite_satisfaite = 180 WHERE id = 35; -- Riz Marovoay COMPLET
UPDATE besoin SET quantite_satisfaite = 70 WHERE id = 36;  -- Sucre Marovoay COMPLET
UPDATE besoin SET quantite_satisfaite = 300 WHERE id = 37; -- Riz Antsiranana COMPLET
UPDATE besoin SET quantite_satisfaite = 50 WHERE id = 38;  -- Tôle Antsiranana (50/70)
UPDATE besoin SET quantite_satisfaite = 150 WHERE id = 39; -- Riz Nosy Be COMPLET
UPDATE besoin SET quantite_satisfaite = 60 WHERE id = 40;  -- Huile Nosy Be COMPLET
UPDATE besoin SET quantite_satisfaite = 400 WHERE id = 41; -- Riz Toliara COMPLET
UPDATE besoin SET quantite_satisfaite = 180 WHERE id = 42; -- Huile Toliara COMPLET
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 43;   -- Tôle Toliara (0/90)
UPDATE besoin SET quantite_satisfaite = 150 WHERE id = 44; -- Riz Morombe COMPLET
UPDATE besoin SET quantite_satisfaite = 270 WHERE id = 45; -- Riz Fianarantsoa (270/320)
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 46;   -- Tôle Fianarantsoa (0/60)
UPDATE besoin SET quantite_satisfaite = 100 WHERE id = 47; -- Sucre Fianarantsoa COMPLET
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 48;   -- Riz Ambositra (0/180)
UPDATE besoin SET quantite_satisfaite = 80 WHERE id = 49;  -- Bois Ambositra COMPLET
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 50;   -- Riz Morondava (0/250)
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 51;   -- Huile Morondava (0/100)
UPDATE besoin SET quantite_satisfaite = 0 WHERE id = 52;   -- Riz Miandrivazo (0/160)

-- ============================================================
-- 9. ACHATS (30 achats avec dons argent)
-- Frais = 5% du montant_produit
-- ============================================================
INSERT INTO achat (id, ville_id, besoin_id, don_id, montant_produit, frais, montant_total, quantite_achetee, date_achat, statut, notes) VALUES
-- Achats validés pour besoins restants
(1, 13, 38, NULL, 200000.00, 10000.00, 210000.00, 20, '2026-02-10', 'valide', 'Achat Tôle pour Antsiranana - Complément'),
(2, 15, 43, NULL, 900000.00, 45000.00, 945000.00, 90, '2026-02-11', 'valide', 'Achat Tôle pour Toliara'),
(3, 17, 45, NULL, 125000.00, 6250.00, 131250.00, 50, '2026-02-12', 'valide', 'Achat Riz pour Fianarantsoa - Complément'),
(4, 17, 46, NULL, 600000.00, 30000.00, 630000.00, 60, '2026-02-12', 'valide', 'Achat Tôle pour Fianarantsoa'),
(5, 18, 48, NULL, 450000.00, 22500.00, 472500.00, 180, '2026-02-13', 'valide', 'Achat Riz pour Ambositra'),
(6, 19, 50, NULL, 625000.00, 31250.00, 656250.00, 250, '2026-02-14', 'valide', 'Achat Riz pour Morondava'),
(7, 19, 51, NULL, 450000.00, 22500.00, 472500.00, 100, '2026-02-14', 'valide', 'Achat Huile pour Morondava'),
(8, 20, 52, NULL, 400000.00, 20000.00, 420000.00, 160, '2026-02-15', 'valide', 'Achat Riz pour Miandrivazo'),

-- Achats validés supplémentaires
(9, 1, 1, NULL, 100000.00, 5000.00, 105000.00, 40, '2026-02-04', 'valide', 'Achat supplémentaire Riz Antananarivo'),
(10, 2, 6, NULL, 75000.00, 3750.00, 78750.00, 30, '2026-02-05', 'valide', 'Achat supplémentaire Riz Ankazobe'),
(11, 5, 15, NULL, 150000.00, 7500.00, 157500.00, 60, '2026-02-07', 'valide', 'Achat supplémentaire Riz Antsirabe'),
(12, 8, 24, NULL, 375000.00, 18750.00, 393750.00, 150, '2026-02-08', 'valide', 'Achat supplémentaire Riz Toamasina'),
(13, 11, 32, NULL, 87500.00, 4375.00, 91875.00, 35, '2026-02-09', 'valide', 'Achat supplémentaire Riz Mahajanga'),

-- Achats matériaux validés
(14, 1, 3, NULL, 300000.00, 15000.00, 315000.00, 30, '2026-02-05', 'valide', 'Achat Tôle Antananarivo'),
(15, 3, 12, NULL, 200000.00, 10000.00, 210000.00, 20, '2026-02-07', 'valide', 'Achat Tôle Anjozorobe'),
(16, 5, 17, NULL, 400000.00, 20000.00, 420000.00, 40, '2026-02-08', 'valide', 'Achat Tôle Antsirabe'),
(17, 8, 26, NULL, 360000.00, 18000.00, 378000.00, 36, '2026-02-09', 'valide', 'Achat Tôle Toamasina'),
(18, 2, 8, NULL, 625000.00, 31250.00, 656250.00, 25, '2026-02-10', 'valide', 'Achat Ciment Ankazobe'),
(19, 11, 34, NULL, 750000.00, 37500.00, 787500.00, 30, '2026-02-11', 'valide', 'Achat Ciment Mahajanga'),

-- Achats huile validés
(20, 1, 2, NULL, 135000.00, 6750.00, 141750.00, 30, '2026-02-06', 'valide', 'Achat Huile Antananarivo'),
(21, 5, 16, NULL, 162000.00, 8100.00, 170100.00, 36, '2026-02-08', 'valide', 'Achat Huile Antsirabe'),
(22, 15, 42, NULL, 270000.00, 13500.00, 283500.00, 60, '2026-02-11', 'valide', 'Achat Huile Toliara'),

-- Achats sucre validés
(23, 2, 7, NULL, 87500.00, 4375.00, 91875.00, 25, '2026-02-13', 'valide', 'Achat Sucre Ankazobe'),
(24, 4, 14, NULL, 105000.00, 5250.00, 110250.00, 30, '2026-02-14', 'valide', 'Achat Sucre Andramasina'),

-- Achats en attente
(25, 6, 19, NULL, 137500.00, 6875.00, 144375.00, 55, '2026-02-15', 'en_attente', 'Achat Riz Ambatolampy - En validation'),
(26, 7, 22, NULL, 90000.00, 4500.00, 94500.00, 36, '2026-02-16', 'en_attente', 'Achat Riz Betafo - En validation'),
(27, 10, 31, NULL, 250000.00, 12500.00, 262500.00, 25, '2026-02-16', 'en_attente', 'Achat Tôle Vatomandry - En validation'),
(28, 14, 39, NULL, 187500.00, 9375.00, 196875.00, 75, '2026-02-16', 'en_attente', 'Achat Riz Nosy Be - En validation'),

-- Achats annulés
(29, 16, 44, NULL, 150000.00, 7500.00, 157500.00, 60, '2026-02-11', 'annule', 'Achat Riz Morombe - Annulé logistique'),
(30, 9, 28, NULL, 175000.00, 8750.00, 183750.00, 70, '2026-02-16', 'annule', 'Achat Riz Brickaville - Annulé budget');

-- ============================================================
-- 10. MISE À JOUR DES DONS ARGENT UTILISÉS
-- ============================================================
UPDATE don_argent SET montant_utilise = 315000.00, statut = 'partiellement_utilise' WHERE id = 1;
UPDATE don_argent SET montant_utilise = 350000.00, statut = 'epuise' WHERE id = 2;
UPDATE don_argent SET montant_utilise = 200000.00, statut = 'epuise' WHERE id = 3;
UPDATE don_argent SET montant_utilise = 750000.00, statut = 'epuise' WHERE id = 4;
UPDATE don_argent SET montant_utilise = 180000.00, statut = 'epuise' WHERE id = 5;
UPDATE don_argent SET montant_utilise = 450000.00, statut = 'epuise' WHERE id = 6;
UPDATE don_argent SET montant_utilise = 300000.00, statut = 'epuise' WHERE id = 7;
UPDATE don_argent SET montant_utilise = 620000.00, statut = 'epuise' WHERE id = 8;
UPDATE don_argent SET montant_utilise = 250000.00, statut = 'epuise' WHERE id = 9;
UPDATE don_argent SET montant_utilise = 400000.00, statut = 'epuise' WHERE id = 10;
UPDATE don_argent SET montant_utilise = 150000.00, statut = 'epuise' WHERE id = 11;
UPDATE don_argent SET montant_utilise = 550000.00, statut = 'epuise' WHERE id = 12;
UPDATE don_argent SET montant_utilise = 280000.00, statut = 'epuise' WHERE id = 13;
UPDATE don_argent SET montant_utilise = 320000.00, statut = 'epuise' WHERE id = 14;
UPDATE don_argent SET montant_utilise = 472500.00, statut = 'partiellement_utilise' WHERE id = 15;

-- ============================================================
-- 11. CRÉATION DE LA VUE RÉCAPITULATIVE
-- ============================================================
CREATE OR REPLACE VIEW vue_recap_global AS
SELECT
    (SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM besoin) as montant_total_besoins,
    (SELECT COALESCE(SUM(quantite_satisfaite * prix_unitaire), 0) FROM besoin) as montant_satisfait_nature,
    (SELECT COALESCE(SUM(montant_total), 0) FROM achat WHERE statut = 'valide') as montant_satisfait_achats,
    (SELECT COALESCE(SUM(montant), 0) FROM don_argent) as total_dons_argent,
    (SELECT COALESCE(SUM(montant_utilise), 0) FROM don_argent) as dons_argent_utilises,
    (SELECT COALESCE(SUM(montant - montant_utilise), 0) FROM don_argent) as dons_argent_disponibles,
    (SELECT COUNT(*) FROM ville) as nb_villes,
    (SELECT COUNT(*) FROM besoin) as nb_besoins,
    (SELECT COUNT(*) FROM don) as nb_dons_nature,
    (SELECT COUNT(*) FROM don_argent) as nb_dons_argent,
    (SELECT COUNT(*) FROM attribution) as nb_attributions,
    (SELECT COUNT(*) FROM achat) as nb_achats,
    (SELECT COUNT(*) FROM achat WHERE statut = 'valide') as nb_achats_valides,
    (SELECT COUNT(*) FROM achat WHERE statut = 'en_attente') as nb_achats_attente,
    (SELECT COUNT(*) FROM achat WHERE statut = 'annule') as nb_achats_annules;

-- ============================================================
-- AFFICHAGE DES STATISTIQUES
-- ============================================================
SELECT '================================================================' AS '';
SELECT '  INSTALLATION BASE DE DONNÉES BNGRC TERMINÉE AVEC SUCCÈS!' AS 'MESSAGE';
SELECT '================================================================' AS '';

SELECT * FROM vue_recap_global;

SELECT '================================================================' AS '';
SELECT '  DÉTAILS DES INSERTIONS:' AS '';
SELECT '================================================================' AS '';
SELECT CONCAT('  ', COUNT(*), ' villes') AS '' FROM ville;
SELECT CONCAT('  ', COUNT(*), ' types de besoin') AS '' FROM type_besoin;
SELECT CONCAT('  ', COUNT(*), ' besoins') AS '' FROM besoin;
SELECT CONCAT('  ', COUNT(*), ' dons nature (total: ', COALESCE(SUM(quantite), 0), ' unités)') AS '' FROM don;
SELECT CONCAT('  ', COUNT(*), ' dons argent (total: ', FORMAT(COALESCE(SUM(montant), 0), 0), ' Ar)') AS '' FROM don_argent;
SELECT CONCAT('  ', COUNT(*), ' attributions') AS '' FROM attribution;
SELECT CONCAT('  ', COUNT(*), ' configurations') AS '' FROM configuration;
SELECT CONCAT('  ', COUNT(*), ' achats (total: ', FORMAT(COALESCE(SUM(montant_total), 0), 0), ' Ar)') AS '' FROM achat;
SELECT '================================================================' AS '';

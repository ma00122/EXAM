SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
-- TABLE VILLE
-- ============================================================

CREATE TABLE ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO ville (id, nom, region) VALUES
(1, 'Toamasina', 'Atsinanana'),
(2, 'Mananjary', 'Vatovavy'),
(3, 'Farafangana', 'Atsimo-Atsinanana'),
(4, 'Nosy Be', 'Diana'),
(5, 'Morondava', 'Menabe');

-- ============================================================
-- TYPE BESOIN
-- ============================================================

CREATE TABLE type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO type_besoin (id, nom_type) VALUES
(1, 'nature'),
(2, 'materiaux');

-- ============================================================
-- TABLE BESOIN
-- ============================================================

CREATE TABLE besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL,
    quantite_initiale INT NOT NULL,
    quantite_satisfaite INT NOT NULL DEFAULT 0,
    prix_unitaire DECIMAL(15,2) NOT NULL,
    date_saisie DATE,
    FOREIGN KEY (ville_id) REFERENCES ville(id),
    FOREIGN KEY (type_id) REFERENCES type_besoin(id)
) ENGINE=InnoDB;

-- ============================================================
-- BESOINS (IMAGE FOURNIE)
-- ============================================================

INSERT INTO besoin
(ville_id, type_id, produit, quantite, quantite_initiale, quantite_satisfaite, prix_unitaire, date_saisie)
VALUES

-- TOAMASINA
(1,1,'Riz (kg)',800,800,0,3000,'2026-02-16'),
(1,1,'Eau (L)',1500,1500,0,1000,'2026-02-15'),
(1,2,'Tôle',120,120,0,25000,'2026-02-15'),
(1,2,'Bâche',200,200,0,15000,'2026-02-15'),
(1,2,'Groupe',3,3,0,6750000,'2026-02-15'),

-- MANANJARY
(2,1,'Riz (kg)',500,500,0,3000,'2026-02-15'),
(2,1,'Huile (L)',120,120,0,6000,'2026-02-15'),
(2,2,'Tôle',80,80,0,25000,'2026-02-15'),
(2,2,'Clous (kg)',60,60,0,8000,'2026-02-15'),

-- FARAFANGANA
(3,1,'Riz (kg)',600,600,0,3000,'2026-02-15'),
(3,1,'Eau (L)',1000,1000,0,1000,'2026-02-15'),
(3,2,'Bâche',150,150,0,15000,'2026-02-15'),
(3,2,'Bois',100,100,0,10000,'2026-02-15'),

-- NOSY BE
(4,1,'Riz (kg)',300,300,0,3000,'2026-02-15'),
(4,1,'Haricots',200,200,0,4000,'2026-02-15'),
(4,2,'Tôle',40,40,0,25000,'2026-02-15'),
(4,2,'Clous (kg)',30,30,0,8000,'2026-02-15'),

-- MORONDAVA
(5,1,'Riz (kg)',700,700,0,3000,'2026-02-15'),
(5,1,'Eau (L)',1200,1200,0,1000,'2026-02-15'),
(5,2,'Bâche',180,180,0,15000,'2026-02-15'),
(5,2,'Bois',150,150,0,10000,'2026-02-15');

-- ============================================================
-- TABLE DON (NATURE)
-- ============================================================

CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100),
    quantite INT,
    quantite_initiale INT,
    date_saisie DATE
) ENGINE=InnoDB;

-- DONS NATURE (Excel Cyclone S3)

INSERT INTO don (type_produit, quantite, quantite_initiale, date_saisie) VALUES
('Riz (kg)',400,400,'2026-02-16'),
('Eau (L)',600,600,'2026-02-16'),
('Tôle',50,50,'2026-02-17'),
('Bâche',70,70,'2026-02-17'),
('Haricots',100,100,'2026-02-17'),
('Riz (kg)',2000,2000,'2026-02-18'),
('Tôle',300,300,'2026-02-18'),
('Eau (L)',5000,5000,'2026-02-18'),
('Bâche',500,500,'2026-02-19'),
('Haricots',88,88,'2026-02-17');

-- ============================================================
-- TABLE DON ARGENT
-- ============================================================

CREATE TABLE don_argent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15,2),
    montant_initial DECIMAL(15,2),
    donateur VARCHAR(255) DEFAULT 'Anonyme',
    date_saisie DATE,
    montant_utilise DECIMAL(15,2) DEFAULT 0,
    statut ENUM('disponible','partiellement_utilise','epuise') DEFAULT 'disponible'
) ENGINE=InnoDB;

-- DONS ARGENT (Excel)

INSERT INTO don_argent
(montant, montant_initial, date_saisie)
VALUES
(5000000,5000000,'2026-02-16'),
(3000000,3000000,'2026-02-16'),
(4000000,4000000,'2026-02-17'),
(1500000,1500000,'2026-02-17'),
(6000000,6000000,'2026-02-17'),
(20000000,20000000,'2026-02-19');

-- ============================================================
-- TABLE ATTRIBUTION
-- ============================================================

CREATE TABLE attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee INT NOT NULL,
    mode_simulation ENUM('chronologique', 'croissant', 'proportionnel') DEFAULT 'chronologique',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE ACHAT
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
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE CONFIGURATION
-- ============================================================

CREATE TABLE configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

INSERT INTO configuration (cle, valeur, description) VALUES
('frais_achat_pourcentage', '5', 'Pourcentage de frais sur achats'),
('devise', 'Ariary', 'Devise officielle');

-- ============================================================
-- VUE RÉCAP
-- ============================================================

CREATE OR REPLACE VIEW vue_recap_global AS
SELECT
(SELECT SUM(quantite_initiale*prix_unitaire) FROM besoin) as montant_total_besoins,
(SELECT SUM(quantite_satisfaite*prix_unitaire) FROM besoin) as montant_satisfait,
(SELECT SUM(quantite_initiale) FROM don) as total_dons_nature,
(SELECT SUM(quantite) FROM don) as dons_restants,
(SELECT SUM(montant_initial) FROM don_argent) as total_dons_argent;

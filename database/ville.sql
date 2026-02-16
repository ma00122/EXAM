-- ============================================================
-- PROJET BNGRC - MODULE VILLES
-- Auteur: Bolton
-- Date: Février 2026
-- ============================================================

-- Création de la table ville
CREATE TABLE IF NOT EXISTS ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour améliorer les performances de recherche
CREATE INDEX idx_ville_nom ON ville(nom);
CREATE INDEX idx_ville_region ON ville(region);

-- ============================================================
-- DONNÉES DE TEST - Quelques villes de Madagascar
-- ============================================================

INSERT INTO ville (nom, region) VALUES
-- Région Analamanga
('Antananarivo', 'Analamanga'),
('Ankazobe', 'Analamanga'),
('Anjozorobe', 'Analamanga'),
('Andramasina', 'Analamanga'),
('Manjakandriana', 'Analamanga'),

-- Région Vakinankaratra
('Antsirabe', 'Vakinankaratra'),
('Ambatolampy', 'Vakinankaratra'),
('Betafo', 'Vakinankaratra'),

-- Région Atsinanana
('Toamasina', 'Atsinanana'),
('Brickaville', 'Atsinanana'),
('Vatomandry', 'Atsinanana'),

-- Région Boeny
('Mahajanga', 'Boeny'),
('Marovoay', 'Boeny'),

-- Région Diana
('Antsiranana', 'Diana'),
('Nosy Be', 'Diana'),

-- Région Atsimo-Andrefana
('Toliara', 'Atsimo-Andrefana'),
('Morondava', 'Menabe'),

-- Région Haute Matsiatra
('Fianarantsoa', 'Haute Matsiatra'),
('Ambalavao', 'Haute Matsiatra'),

-- Région Sava
('Sambava', 'Sava'),
('Antalaha', 'Sava');

-- ============================================================
-- VÉRIFICATION
-- ============================================================
-- SELECT * FROM ville ORDER BY region, nom;
-- SELECT region, COUNT(*) as nb_villes FROM ville GROUP BY region ORDER BY nb_villes DESC;

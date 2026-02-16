-- ============================================================
-- PROJET BNGRC - MODULE MAHERY
-- Auteur: Mahery
-- Date: Février 2026
-- ============================================================

-- ============================================================
-- TABLE DON
-- ============================================================
CREATE TABLE IF NOT EXISTS don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL CHECK (quantite > 0),
    date_saisie DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour améliorer les performances
CREATE INDEX idx_don_type_produit ON don(type_produit);
CREATE INDEX idx_don_date_saisie ON don(date_saisie);

-- ============================================================
-- TABLE BESOIN (si non existante - Module Sedra)
-- ============================================================
CREATE TABLE IF NOT EXISTS besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type ENUM('nature', 'materiaux', 'argent') NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix_unitaire DECIMAL(15, 2) NOT NULL CHECK (prix_unitaire > 0),
    date_saisie DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_besoin_ville
        FOREIGN KEY (ville_id)
        REFERENCES ville(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour les besoins
CREATE INDEX idx_besoin_ville ON besoin(ville_id);
CREATE INDEX idx_besoin_produit ON besoin(produit);
CREATE INDEX idx_besoin_type ON besoin(type);
CREATE INDEX idx_besoin_date ON besoin(date_saisie);

-- ============================================================
-- TABLE ATTRIBUTION
-- ============================================================
CREATE TABLE IF NOT EXISTS attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee INT NOT NULL CHECK (quantite_attribuee > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attribution_don
        FOREIGN KEY (don_id)
        REFERENCES don(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_attribution_besoin
        FOREIGN KEY (besoin_id)
        REFERENCES besoin(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour les attributions
CREATE INDEX idx_attribution_don ON attribution(don_id);
CREATE INDEX idx_attribution_besoin ON attribution(besoin_id);

-- ============================================================
-- DONNÉES DE TEST - DONS
-- ============================================================
INSERT INTO don (type_produit, quantite, date_saisie) VALUES
('Riz', 100, '2026-02-10'),
('Huile', 50, '2026-02-11'),
('Savon', 200, '2026-02-12'),
('Sucre', 75, '2026-02-13'),
('Riz', 150, '2026-02-14'),
('Huile', 80, '2026-02-15');

-- ============================================================
-- DONNÉES DE TEST - BESOINS (si la table est vide)
-- Note: Assurez-vous que les ville_id existent dans la table ville
-- ============================================================
INSERT INTO besoin (ville_id, type, produit, quantite, prix_unitaire, date_saisie) VALUES
(1, 'nature', 'Riz', 50, 2500.00, '2026-02-08'),
(1, 'nature', 'Huile', 30, 15000.00, '2026-02-08'),
(2, 'nature', 'Riz', 80, 2500.00, '2026-02-09'),
(2, 'nature', 'Savon', 100, 3000.00, '2026-02-09'),
(3, 'nature', 'Sucre', 60, 5000.00, '2026-02-10'),
(3, 'nature', 'Riz', 120, 2500.00, '2026-02-10'),
(4, 'materiaux', 'Huile', 40, 15000.00, '2026-02-11'),
(5, 'nature', 'Savon', 150, 3000.00, '2026-02-12');

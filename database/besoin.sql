
-- Table Ville (nécessaire pour les besoins)
CREATE TABLE IF NOT EXISTS ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Données de test pour les villes
INSERT INTO ville (nom, region) VALUES
('Antananarivo', 'Analamanga'),
('Toamasina', 'Atsinanana'),
('Antsirabe', 'Vakinankaratra'),
('Mahajanga', 'Boeny'),
('Fianarantsoa', 'Haute Matsiatra')
ON DUPLICATE KEY UPDATE nom = VALUES(nom);

CREATE TABLE IF NOT EXISTS type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO type_besoin (nom_type) VALUES
('nature'),
('materiaux'),
('argent')
ON DUPLICATE KEY UPDATE nom_type = VALUES(nom_type);


CREATE TABLE IF NOT EXISTS besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES type_besoin(id) ON DELETE RESTRICT
);

CREATE INDEX idx_besoin_ville_id ON besoin(ville_id);
CREATE INDEX idx_besoin_type_id ON besoin(type_id);
CREATE INDEX idx_besoin_produit ON besoin(produit);

INSERT INTO besoin (ville_id, type_id, produit, quantite, prix_unitaire) VALUES
(1, 1, 'Riz', 100, 2500),       -- Antananarivo, nature
(1, 2, 'Tôle', 50, 10000),      -- Antananarivo, materiaux
(2, 1, 'Huile', 200, 4500),     -- Toamasina, nature
(2, 3, 'Donation', 100000, 1),  -- Toamasina, argent
(3, 2, 'Clou', 300, 1500),      -- Antsirabe, materiaux
(4, 1, 'Sucre', 150, 3500),     -- Mahajanga, nature
(5, 3, 'Donation', 50000, 1);   -- Fianarantso

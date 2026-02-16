file-- Table type_besoin
CREATE TABLE IF NOT EXISTS type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des types de besoin
INSERT IGNORE INTO type_besoin (nom_type) VALUES
('nature'),
('materiaux'),
('argent');

-- Table besoin
CREATE TABLE IF NOT EXISTS besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES type_besoin(id) ON DELETE RESTRICT
);

-- Données de test
INSERT INTO besoin (type_id, produit, quantite, prix_unitaire) VALUES
(1, 'Riz', 100, 77.7),
(2, 'Tôle', 50, 10000),
(1, 'Huile', 200, 4500),
(3, 'Donation', 100000, 1),
(2, 'Clou', 300, 1500),
(1, 'Sucre', 150, 3500);

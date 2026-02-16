-- Table des achats effectués avec dons argent
-- Projet BNGRC - Module Sedra

CREATE TABLE IF NOT EXISTS achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    besoin_id INT NOT NULL,
    don_id INT NOT NULL,
    montant_produit DECIMAL(15,2) NOT NULL DEFAULT 0,
    frais DECIMAL(15,2) NOT NULL DEFAULT 0,
    montant_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE,
    FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour filtrage par ville
CREATE INDEX idx_achat_ville ON achat(ville_id);
CREATE INDEX idx_achat_besoin ON achat(besoin_id);
CREATE INDEX idx_achat_don ON achat(don_id);

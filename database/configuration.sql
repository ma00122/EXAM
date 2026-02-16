-- Table de configuration (frais d'achat, etc.)
-- Projet BNGRC - Module Sedra

CREATE TABLE IF NOT EXISTS configuration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer la configuration par défaut des frais d'achat (10%)
INSERT INTO configuration (cle, valeur, description) 
VALUES ('frais_achat_pourcentage', '10', 'Pourcentage de frais appliqué aux achats avec dons argent')
ON DUPLICATE KEY UPDATE valeur = valeur;

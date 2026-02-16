-- Table DON
CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_produit VARCHAR(100) NOT NULL,
    quantite INT NOT NULL,
    date_saisie DATE NOT NULL
);

-- Table ATTRIBUTION
CREATE TABLE attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee INT NOT NULL,
    
    CONSTRAINT fk_don
        FOREIGN KEY (don_id)
        REFERENCES don(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

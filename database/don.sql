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

INSERT INTO don (type_produit, quantite, date_saisie)
VALUES ('Riz', 100, '2026-02-10');

INSERT INTO don (type_produit, quantite, date_saisie)
VALUES ('Huile', 50, '2026-02-11');

INSERT INTO don (type_produit, quantite, date_saisie)
VALUES ('Savon', 200, '2026-02-12');

INSERT INTO don (type_produit, quantite, date_saisie)
VALUES ('Sucre', 75, '2026-02-13');

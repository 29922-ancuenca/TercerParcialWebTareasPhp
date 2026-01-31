CREATE DATABASE pizzabd;

USE pizzabd;

CREATE TABLE ingredientes(
    id CHAR(38),
    nombre VARCHAR(100),
	precio float,
    PRIMARY KEY(id)
);

INSERT INTO ingredientes VALUES(UUID(), 'Peperoni',0.80);
INSERT INTO ingredientes VALUES(UUID(), 'Camaron',1.25);
INSERT INTO ingredientes VALUES(UUID(), 'Pina',0.9);
INSERT INTO ingredientes VALUES(UUID(), 'Queso',1);
INSERT INTO ingredientes VALUES(UUID(), 'Harina',1);
INSERT INTO ingredientes VALUES(UUID(), 'Agua',1);
INSERT INTO ingredientes VALUES(UUID(), 'Sal',1);
INSERT INTO ingredientes VALUES(UUID(), 'Jamon',1.10);
INSERT INTO ingredientes VALUES(UUID(), 'Pollo',1.25);
INSERT INTO ingredientes VALUES(UUID(), 'Pimiento',0.5);
INSERT INTO ingredientes VALUES(UUID(), 'Tomate'0.40);

SELECT * FROM ingredientes;

CREATE TABLE pizza(
    id CHAR(38),
	nombre CHAR(38),
    fk_ingredientes CHAR(38),
    fecha DATETIME,
    PRIMARY KEY(id),
    FOREIGN KEY (fk_ingredientes) REFERENCES ingredientes(id)
);

SELECT * FROM pizza;



CREATE VIEW conteoIngredientes AS
SELECT
    ca.nombre AS 'ingredientes',
    COUNT(v.fk_ingredientes) AS 'pizza'
FROM
    ingredientes ca
INNER JOIN pizza v ON ca.id = v.fk_ingredientes
GROUP BY ca.nombre
ORDER BY COUNT(v.fk_ingredientes) DESC;

SELECT * FROM conteoIngredientes;
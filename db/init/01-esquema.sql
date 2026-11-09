CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    edad INTEGER NOT NULL,
    sexo VARCHAR(10) NOT NULL
);

INSERT INTO usuarios (nombre, apellido, edad, sexo)
VALUES ('Pablo', 'Perez', 25, 'M');

CREATE DATABASE IF NOT EXISTS tienda_mascotas;
USE tienda_mascotas;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL
);

INSERT INTO productos (codigo, nombre, categoria, precio) VALUES 
('7421098', 'Saco Alimento Perro 15kg', 'Alimentos', 45990.00),
('7421099', 'Collar Antipulgas', 'Farmacia', 12500.00);
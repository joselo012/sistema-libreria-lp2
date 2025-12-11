DROP DATABASE IF EXISTS bdlibreria;
CREATE DATABASE bdlibreria CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bdlibreria;

-- 1. Tabla de Administradores
CREATE TABLE administrador (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    pass_admin VARCHAR(255) NOT NULL 
);

-- 2. Tabla de Clientes
CREATE TABLE cliente (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    usu_cliente VARCHAR(100) NOT NULL, -- Puede ser igual al nombre o un nickname
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE, 
    telefono VARCHAR(15),
    pass_cli VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla de Libros
CREATE TABLE libro (
    id_libro INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    autor VARCHAR(150),
    editorial VARCHAR(100),
    isbn VARCHAR(50) UNIQUE NOT NULL, -- Aumenté a 50 por si acaso
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    url_imagen VARCHAR(250)
);

-- 4. Tabla de Pedidos (Cabecera)
CREATE TABLE pedido (
    id_pedido INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP, -- Cambiado a DATETIME para saber la hora exacta
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'listo_para_recoger', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE
);

-- 5. Tabla de Detalles del Pedido
CREATE TABLE detalle_pedido (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_pedido INT NOT NULL,
    id_libro INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libro(id_libro)
);

-- 6. Tabla de Movimientos de Inventario (Auditoría)
CREATE TABLE inventario_movimiento (
    id_movimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_libro INT NOT NULL,
    tipo ENUM('ingreso', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_libro) REFERENCES libro(id_libro)
);

-- 7. Tabla de Notificaciones de PEDIDOS (Tu tabla original)
-- Sirve para avisar: "Tu pedido ya está listo"
CREATE TABLE notificacion_pedido (
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_pedido INT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    mensaje TEXT NOT NULL,
    estado ENUM('enviado', 'pendiente_envio') NOT NULL DEFAULT 'pendiente_envio',
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE
);

-- 8. [NUEVA] Tabla de Alertas de Stock (Lista de Espera)
-- Sirve para avisar: "El libro que querías ya volvió a tener stock"
CREATE TABLE alerta_stock (
    id_alerta INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'notificado') DEFAULT 'pendiente',
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libro(id_libro) ON DELETE CASCADE
);

-- ==========================================
-- CARGA DE DATOS (Tus datos solicitados)
-- ==========================================

-- A. Insertar Administrador
INSERT INTO administrador (usuario, nombre, pass_admin) 
VALUES ('admin1', 'Administrador1', 'holas123');

-- B. Insertar Clientes
-- Nota: usu_cliente lo llené con el mismo nombre, puedes cambiarlo si quieres
INSERT INTO cliente (usu_cliente, nombre, correo, telefono, pass_cli) 
VALUES 
('Enmanuel', 'Enmanuel Trinidad', 'trini@correo.com', '978563412', 'holas123'),
('Jonel', 'Jonel Marroquin', 'meza@correo.com', '987654321', 'holas321');

-- C. Insertar Libros (Tus 20 libros)
INSERT INTO libro (id_libro, titulo, autor, editorial, isbn, precio, stock, url_imagen) VALUES
(1, 'Cien Años de Soledad', 'Gabriel García Márquez', 'Sudamericana', '978-0307474728', 45.00, 15, 'img/libro1.jpg'),
(2, '1984', 'George Orwell', 'Debolsillo', '978-0451524935', 35.50, 20, 'img/libro2.jpg'),
(3, 'Harry Potter y la Piedra Filosofal', 'J.K. Rowling', 'Salamandra', '978-8478884452', 59.90, 10, 'img/libro3.jpg'),
(4, 'IT (Eso)', 'Stephen King', 'Debolsillo', '978-1501142970', 65.00, 8, 'img/libro4.jpg'),
(5, 'El Principito', 'Antoine de Saint-Exupéry', 'Océano', '978-0156013987', 25.00, 30, 'img/libro5.jpg'),
(6, 'Dune', 'Frank Herbert', 'Nova', '978-0441013593', 48.00, 12, 'img/libro6.jpg'),
(7, 'El Código Da Vinci', 'Dan Brown', 'Planeta', '978-0307474278', 39.00, 18, 'img/libro7.jpg'),
(8, 'Don Quijote de la Mancha', 'Miguel de Cervantes', 'RAE', '978-8420412146', 55.00, 5, 'img/libro8.jpg'),
(9, 'Fundación', 'Isaac Asimov', 'Debolsillo', '978-0553293357', 32.00, 14, 'img/libro9.jpg'),
(10, 'El Señor de los Anillos', 'J.R.R. Tolkien', 'Minotauro', '978-0544003415', 70.00, 7, 'img/libro10.jpg'),
(11, 'Fahrenheit 451', 'Ray Bradbury', 'Minotauro', '978-1451673319', 29.90, 22, 'img/libro11.jpg'),
(12, 'Drácula', 'Bram Stoker', 'Austral', '978-0486411095', 28.00, 15, 'img/libro12.jpg'),
(13, 'Crónica de una Muerte Anunciada', 'Gabriel García Márquez', 'Diana', '978-1400034710', 34.00, 25, 'img/libro13.jpg'),
(14, 'La Metamorfosis', 'Franz Kafka', 'Alianza', '978-1557427666', 22.00, 20, 'img/libro14.jpg'),
(15, 'El Alquimista', 'Paulo Coelho', 'Grijalbo', '978-0062315007', 38.00, 16, 'img/libro15.jpg'),
(16, 'Orgullo y Prejuicio', 'Jane Austen', 'Penguin', '978-0141439518', 26.50, 12, 'img/libro16.jpg'),
(17, 'Un Mundo Feliz', 'Aldous Huxley', 'Debolsillo', '978-0060850524', 31.00, 18, 'img/libro17.jpg'),
(18, 'Juego de Tronos', 'George R.R. Martin', 'Plaza & Janés', '978-0553103540', 85.00, 6, 'img/libro18.jpg'),
(19, 'El Resplandor', 'Stephen King', 'Debolsillo', '978-0307743657', 42.00, 9, 'img/libro19.jpg'),
(20, 'Steve Jobs', 'Walter Isaacson', 'Debate', '978-1451648539', 68.00, 11, 'img/libro20.jpg');

INSERT INTO administrador (usuario, nombre, pass_admin) 
VALUES ('admin1','Administrador1','holas123');

-- 2. Insertar Cliente
INSERT INTO cliente (usuario, nombre, correo, telefono, pass_cli) 
VALUES ('Enmanuel Trinidad','trini@correo.com','978563412','holas123'),
('Jonel Marroquin','meza@correo.com','987654321','holas321');

INSERT INTO libro (id_libro, titulo, autor, editorial, isbn, precio, stock, url_imagen) VALUES
(1, 'Cien Años de Soledad', 'Gabriel García Márquez', 'Sudamericana', '978-0307474728', 45.00, 15, 'img/libro1.jpg'),
(2, '1984', 'George Orwell', 'Debolsillo', '978-0451524935', 35.50, 20, 'img/libro2.jpg'),
(3, 'Harry Potter y la Piedra Filosofal', 'J.K. Rowling', 'Salamandra', '978-8478884452', 59.90, 10, 'img/libro3.jpg'),
(4, 'IT (Eso)', 'Stephen King', 'Debolsillo', '978-1501142970', 65.00, 8, 'img/libro4.jpg'),
(5, 'El Principito', 'Antoine de Saint-Exupéry', 'Océano', '978-0156013987', 25.00, 30, 'img/libro5.jpg'),
(6, 'Dune', 'Frank Herbert', 'Nova', '978-0441013593', 48.00, 12, 'img/libro6.jpg'),
(7, 'El Código Da Vinci', 'Dan Brown', 'Planeta', '978-0307474278', 39.00, 18, 'img/libro7.jpg'),
(8, 'Don Quijote de la Mancha', 'Miguel de Cervantes', 'RAE', '978-8420412146', 55.00, 5, 'img/libro8.jpg'),
(9, 'Fundación', 'Isaac Asimov', 'Debolsillo', '978-0553293357', 32.00, 14, 'img/libro9.jpg'),
(10, 'El Señor de los Anillos', 'J.R.R. Tolkien', 'Minotauro', '978-0544003415', 70.00, 7, 'img/libro10.jpg'),
(11, 'Fahrenheit 451', 'Ray Bradbury', 'Minotauro', '978-1451673319', 29.90, 22, 'img/libro11.jpg'),
(12, 'Drácula', 'Bram Stoker', 'Austral', '978-0486411095', 28.00, 15, 'img/libro12.jpg'),
(13, 'Crónica de una Muerte Anunciada', 'Gabriel García Márquez', 'Diana', '978-1400034710', 34.00, 25, 'img/libro13.jpg'),
(14, 'La Metamorfosis', 'Franz Kafka', 'Alianza', '978-1557427666', 22.00, 20, 'img/libro14.jpg'),
(15, 'El Alquimista', 'Paulo Coelho', 'Grijalbo', '978-0062315007', 38.00, 16, 'img/libro15.jpg'),
(16, 'Orgullo y Prejuicio', 'Jane Austen', 'Penguin', '978-0141439518', 26.50, 12, 'img/libro16.jpg'),
(17, 'Un Mundo Feliz', 'Aldous Huxley', 'Debolsillo', '978-0060850524', 31.00, 18, 'img/libro17.jpg'),
(18, 'Juego de Tronos', 'George R.R. Martin', 'Plaza & Janés', '978-0553103540', 85.00, 6, 'img/libro18.jpg'),
(19, 'El Resplandor', 'Stephen King', 'Debolsillo', '978-0307743657', 42.00, 9, 'img/libro19.jpg'),
(20, 'Steve Jobs', 'Walter Isaacson', 'Debate', '978-1451648539', 68.00, 11, 'img/libro20.jpg');




create database bdlibreria;
use bdlibreria;

create table administrador (
    id_admin int primary key auto_increment,
    usuario varchar(50) not null unique,
    nombre varchar(100) not null,
    pass_admin varchar(255) not null 
);

-- 2. Tabla de Clientes (cliente)
create table cliente (
    id_cliente int primary key auto_increment,
    usu_cliente varchar(100) not null,
    nombre varchar(100) not null,
    correo varchar(100) not null unique, 
    telefono varchar(15),
    pass_cli varchar(255) not null,
    fecha_registro datetime default current_timestamp
);

-- 3. Tabla de Libros (libro)
create table libro (
    id_libro int primary key auto_increment,
    titulo varchar(255) not null,
    autor varchar(150),
    editorial varchar(100),
    isbn varchar(20) unique not null, 
    precio decimal(10, 2) not null,
    stock int not null,
    url_imagen varchar(250)
);

-- 4. Tabla de Pedidos (pedido)
create table pedido (
    id_pedido int primary key auto_increment,
    id_cliente int not null,
    fecha_pedido date not null,
    total decimal(10, 2) not null,
    estado enum('pendiente', 'listo para recoger', 'entregado', 'cancelado') not null, -- Estados del pedido (RF08)
    
    foreign key (id_cliente) references cliente(id_cliente)
);

-- 5. Tabla de Detalles del Pedido (detalle_pedido)
create table detalle_pedido (
    id_detalle int primary key auto_increment,
    id_pedido int not null,
    id_libro int not null,
    cantidad int not null,
    subtotal decimal(10, 2) not null,
    
    foreign key (id_pedido) references pedido(id_pedido),
    foreign key (id_libro) references libro(id_libro)
);

-- 6. Tabla de Movimientos de Inventario (inventario_movimiento)
create table inventario_movimiento (
    id_movimiento int primary key auto_increment,
    id_libro int not null,
    tipo enum('ingreso', 'salida', 'ajuste') not null,
    cantidad int not null,
    fecha_movimiento datetime default current_timestamp,
    
    foreign key (id_libro) references libro(id_libro)
);

-- 7. Tabla de Notificaciones (notificacion)
create table notificacion (
    id_notificacion int primary key auto_increment,
    id_pedido int not null,
    fecha_envio datetime default current_timestamp,
    mensaje text not null,
    estado enum('enviado', 'pendiente_envio') not null,
    
    foreign key (id_pedido) references pedido(id_pedido)
);

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




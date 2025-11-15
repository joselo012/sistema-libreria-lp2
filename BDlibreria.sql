-- Estructura de la Base de Datos 'libreria_a'
-- Usando comandos y nombres de tablas/columnas en minúsculas.

-- 1. Tabla de Usuarios Administradores (administrador)
create table administrador (
    id_admin int primary key auto_increment,
    usuario varchar(50) not null unique,
    nombre varchar(100) not null,
    password_hash varchar(255) not null -- Almacena el hash cifrado de la contraseña (RNF04)
);

-- 2. Tabla de Clientes (cliente)
create table cliente (
    id_cliente int primary key auto_increment,
    nombre varchar(100) not null,
    correo varchar(100) not null unique, -- Debe ser único (Precondición de UC6)
    telefono varchar(15),
    password_hash varchar(255) not null,
    fecha_registro datetime default current_timestamp
);

-- 3. Tabla de Libros (libro)
create table libro (
    id_libro int primary key auto_increment,
    titulo varchar(255) not null,
    autor varchar(150),
    editorial varchar(100),
    isbn varchar(20) unique not null, -- Control de duplicidad de ISBN (Flujo Alterno UC4)
    precio decimal(10, 2) not null,
    stock int not null -- Cantidad de libros disponibles
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

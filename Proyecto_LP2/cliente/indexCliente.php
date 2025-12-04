<?php
session_start();
require_once '../conexion.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion(); 
$id_cliente = $_SESSION['id_usuario'];

$total_pedidos = 0;
$ultimo_libro = "Ninguno";

if ($conn && !$conn->connect_error) {
    
    $sql_count = "SELECT COUNT(*) FROM pedido WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql_count);
    if ($stmt) {
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $stmt->bind_result($total_pedidos);
        $stmt->fetch();
        $stmt->close();
    }

    $sql_ultimo = "SELECT l.titulo 
                   FROM pedido p
                   JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                   JOIN libro l ON dp.id_libro = l.id_libro
                   WHERE p.id_cliente = ?
                   ORDER BY p.fecha_pedido DESC LIMIT 1";
                   
    $stmt2 = $conn->prepare($sql_ultimo);
    if ($stmt2) {
        $stmt2->bind_param("i", $id_cliente);
        $stmt2->execute();
        $stmt2->bind_result($titulo_libro);
        if ($stmt2->fetch()) {
            $ultimo_libro = $titulo_libro;
        }
        $stmt2->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio | BIBLIOTECA UDH</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> UDH <span class="highlight">MIEMBRO</span>
        </div>

        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item active">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            
            <a href="catalogo.php" class="nav-item">
                <i class="ph-bold ph-books"></i> Catálogo
            </a>
            
            <a href="misLibros.php" class="nav-item">
                <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="nav-item small" style="color: #10b981">
                <i class="ph-fill ph-circle"></i> Cuenta Activa
            </div>
            <a href="logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        
        <div class="welcome-box">
            <h1>Hola, <span class="highlight"><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></span></h1>
            <p style="font-size: 1.2rem; color: var(--text-muted);">
                Bienvenido a tu espacio personal de lectura. ¿Qué aprenderemos hoy?
            </p>
        </div>

        <div class="stats-container">
            
            <div class="stat-card">
                <i class="ph-duotone ph-read-cv-logo icon"></i>
                <div>
                    <h3><?= $total_pedidos ?></h3>
                    <p>Libros Solicitados</p>
                </div>
            </div>

            <div class="stat-card" style="flex: 2;"> 
                <i class="ph-duotone ph-bookmark-simple icon"></i>
                <div>
                    <h3 style="font-size: 1.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
                        <?= htmlspecialchars($ultimo_libro) ?>
                    </h3>
                    <p>Última Lectura</p>
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-top: 40px; text-align: center; padding: 60px;">
            <i class="ph-duotone ph-books" style="font-size: 4rem; color: var(--accent); margin-bottom: 20px;"></i>
            <h2 style="margin-bottom: 10px;">Explora nuestra colección</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Tenemos nuevos títulos disponibles esperando por ti.</p>
            
            <a href="catalogo.php" class="btn-action" style="padding: 15px 40px; font-size: 1.1rem; text-decoration: none;">
                IR AL CATÁLOGO <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

    </main>

</body>
</html>
<?php 
if(isset($db)) {
    $db->cerrarConexion();
}
?>
<?php
session_start();

// 1. Seguridad: Solo Administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../conexion.php';

// Variables iniciales
$total_libros = 0;
$total_clientes = 0;
$pedidos_pendientes = 0;
$total_ganancias = 0;
$conexion_activa = false;

// 2. Instancia de conexión
$db = new Conexion();
$conn = $db->getConexion();

if ($conn && !$conn->connect_error) {
    $conexion_activa = true;

    // A. Contar Libros
    $sql_libros = "SELECT COUNT(*) FROM libro";
    $result = $conn->query($sql_libros);
    if ($result) $total_libros = $result->fetch_row()[0];

    // B. Contar Clientes
    $sql_clientes = "SELECT COUNT(*) FROM cliente";
    $result = $conn->query($sql_clientes);
    if ($result) $total_clientes = $result->fetch_row()[0];

    // C. Contar Pedidos Pendientes (Vital para el flujo del negocio)
    $sql_pendientes = "SELECT COUNT(*) FROM pedido WHERE estado = 'pendiente'";
    $result = $conn->query($sql_pendientes);
    if ($result) $pedidos_pendientes = $result->fetch_row()[0];

    // D. Calcular Ganancias (Suma de pedidos aprobados/comprados)
    $sql_ganancias = "SELECT SUM(total) FROM pedido WHERE estado = 'aprobado' OR estado = 'comprado'";
    $result = $conn->query($sql_ganancias);
    if ($result) {
        $row = $result->fetch_row();
        $total_ganancias = $row[0] ?? 0; // Si es null, pone 0
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Admin | Biblioteca</title>
    <link rel="stylesheet" href="style.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* Pequeños ajustes locales para el dashboard */
        .stat-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-shield-check"></i> PANEL <span class="highlight">ADMIN</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item active">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            
            <a href="pedidos.php" class="nav-item">
                <i class="ph-bold ph-list-checks"></i> Pedidos
                <?php if($pedidos_pendientes > 0): ?>
                    <span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-left: auto;">
                        <?= $pedidos_pendientes ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="libros.php" class="nav-item">
                <i class="ph-bold ph-books"></i> Inventario Libros
            </a>
            
            <a href="nuevo_libro.php" class="nav-item">
                <i class="ph-bold ph-plus-circle"></i> Nuevo Libro
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="nav-item small" style="color: <?= $conexion_activa ? '#10b981' : '#ef4444' ?>">
                <i class="ph-fill ph-circle"></i> <?= $conexion_activa ? 'Base de Datos: OK' : 'Error Conexión' ?>
            </div>
            <a href="../logout.php" class="nav-item logout">
                <i class="ph-bold ph-sign-out"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="welcome-box">
            <h1>Hola, <span class="highlight"><?= htmlspecialchars($_SESSION['nombre']) ?></span></h1>
            <p style="font-size: 1.1rem; color: var(--text-muted);">Bienvenido al centro de control. Aquí tienes el resumen de hoy.</p>
        </div>

        

        <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            
            <div class="stat-card" onclick="window.location.href='pedidos.php'" style="border-left: 4px solid #eab308;">
                <i class="ph-duotone ph-bell-ringing icon" style="color: #eab308;"></i>
                <div>
                    <h3><?= $pedidos_pendientes ?></h3>
                    <p>Pedidos Pendientes</p>
                </div>
            </div>

            <div class="stat-card" style="border-left: 4px solid #10b981;">
                <i class="ph-duotone ph-currency-dollar icon" style="color: #10b981;"></i>
                <div>
                    <h3>S/. <?= number_format($total_ganancias, 0) ?></h3>
                    <p>Ingresos Totales</p>
                </div>
            </div>
              <div class="stat-card" onclick="window.location.href='listaUsuarios.php'" style="cursor: pointer;">
                <i class="ph-duotone ph-users-three icon"></i>
                <div>
                    <h3><?= $total_clientes ?></h3>
                    <p>Clientes Registrados</p>
                </div>
            </div>

            <div class="stat-card" onclick="window.location.href='libros.php'">
                <i class="ph-duotone ph-books icon"></i>
                <div>
                    <h3><?= $total_libros ?></h3>
                    <p>Libros en Catálogo</p>
                </div>
            </div>
            
        </div>
    </main>

</body>
</html>

<?php 
if(isset($db)) {
    $db->cerrarConexion();
}
?>
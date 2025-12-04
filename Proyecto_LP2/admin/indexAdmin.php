<?php
// 1. Iniciar sesión antes que nada
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'administrador') {
    // Si falla, lo mandamos al login o al index de clientes si se equivocó de área
    header("Location: ../login.php");
    exit;
}

require_once '../conexion.php'; // Asumo que está en la misma carpeta que el login

// Variables por defecto
$total_libros = 0;
$total_users = 0;
$conexion_activa = false;

// Instancia de la conexión (usando la lógica de tu login)
$db = new Conexion();
$conn = $db->getConexion(); // Usamos getConexion() como en tu login

if ($conn && !$conn->connect_error) {
    $conexion_activa = true;

    // --- Consultas adaptadas a MySQLi (compatible con tu login) ---
    
    // Contar libros
    $sql_libros = "SELECT COUNT(*) FROM libro"; // Asegúrate que la tabla se llame 'libro'
    $result_libros = $conn->query($sql_libros);
    if ($result_libros) {
        $row = $result_libros->fetch_row();
        $total_libros = $row[0];
    }

    // Contar usuarios (clientes)
    // Nota: En tu login la tabla se llama 'cliente', aquí la mantengo igual.
    $sql_users = "SELECT COUNT(*) FROM cliente"; 
    $result_users = $conn->query($sql_users);
    if ($result_users) {
        $row = $result_users->fetch_row();
        $total_users = $row[0];
    }
    
    // No cerramos la conexión aquí si la vas a usar abajo, 
    // pero como no hay más php abajo, podemos dejarla abierta o cerrarla al final.
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio Admin</title>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> INDEX <span class="highlight">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item active">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            <a href="listaUsuarios.php" class="nav-item">
                <i class="ph-bold ph-users"></i> Usuarios
            </a>
            <a href="Libros.php" class="nav-item">
                <i class="ph-bold ph-books"></i> Libros
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="nav-item small" style="color: <?= $conexion_activa ? '#10b981' : '#ef4444' ?>">
                <i class="ph-fill ph-circle"></i> <?= $conexion_activa ? 'Online' : 'Offline' ?>
            </div>
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <div class="welcome-box">
            <h1>Bienvenido, <span class="highlight"><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></span></h1>
            <p style="font-size: 1.2rem; color: var(--text-muted);">
                Sesión iniciada correctamente. Panel de control activo.
            </p>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <i class="ph-duotone ph-users-three icon"></i>
                <div>
                    <h3><?= $total_users ?></h3>
                    <p>Usuarios Registrados</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="ph-duotone ph-book-open-text icon"></i>
                <div>
                    <h3><?= $total_libros ?></h3>
                    <p>Libros en Catálogo</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="ph-duotone ph-shield-check icon"></i>
                <div>
                    <h3>SECURE</h3>
                    <p>Acceso Autorizado</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php 
// Opcional: Cerrar conexión al final del todo
if(isset($db)) {
    $db->cerrarConexion();
}
?>
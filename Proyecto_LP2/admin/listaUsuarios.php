<?php
session_start();
require_once '../conexion.php';

// 1. SEGURIDAD
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion();
$mensaje = '';

// 2. LÓGICA: ELIMINAR CLIENTE
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    
    // Primero verificamos si tiene pedidos para evitar errores de integridad
    $check_pedidos = "SELECT COUNT(*) FROM pedido WHERE id_cliente = ?";
    $stmt_check = $conn->prepare($check_pedidos);
    $stmt_check->bind_param("i", $id_eliminar);
    $stmt_check->execute();
    $stmt_check->bind_result($num_pedidos);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($num_pedidos > 0) {
        $mensaje = "<div class='alert-error'>No se puede eliminar: Este cliente tiene $num_pedidos pedidos registrados.</div>";
    } else {
        // Si no tiene pedidos, procedemos a borrar
        $sql_del = "DELETE FROM cliente WHERE id_cliente = ?";
        $stmt = $conn->prepare($sql_del);
        $stmt->bind_param("i", $id_eliminar);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert-success'>Usuario eliminado del sistema.</div>";
        } else {
            $mensaje = "<div class='alert-error'>Error al eliminar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// 3. LÓGICA: LISTAR Y BUSCAR
$busqueda = '';
$sql = "SELECT * FROM cliente";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $busqueda = $conn->real_escape_string($_GET['q']);
    // Busca por Nombre O por Usuario 
    $sql .= " WHERE nombre LIKE '%$busqueda%' OR usu_cliente LIKE '%$busqueda%'";
}

$sql .= " ORDER BY id_cliente DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .table-container { background: #1e293b; border-radius: 12px; border: 1px solid #334155; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; vertical-align: middle; }
        th { background: #0f172a; color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: #253346; }
        
        .avatar-circle {
            width: 40px; height: 40px; background: #3b82f6; color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;
        }

        .btn-delete { 
            background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-delete:hover { background: #ef4444; color: white; }

        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Buscador */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box { display: flex; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 5px 10px; width: 100%; max-width: 400px; }
        .search-input { background: transparent; border: none; color: white; padding: 8px; outline: none; flex-grow: 1; }
        .search-btn { background: #3b82f6; border: none; color: white; padding: 8px 15px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header"> <i class="ph-fill ph-shield-check"></i> PANEL <span class="highlight">ADMIN</span> </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="pedidos.php" class="nav-item"> <i class="ph-bold ph-list-checks"></i> Pedidos </a>
            <a href="libros.php" class="nav-item"> <i class="ph-bold ph-books"></i> Inventario Libros </a>
            <a href="nuevo_libro.php" class="nav-item"> <i class="ph-bold ph-plus-circle"></i> Nuevo Libro </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">GESTIÓN DE USUARIOS</h2>
        
        <?= $mensaje ?>

        <div class="toolbar">
            <form method="GET" action="listaUsuarios.php" class="search-box">
                <input type="text" name="q" class="search-input" placeholder="Buscar por nombre o usuario..." value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit" class="search-btn"><i class="ph-bold ph-magnifying-glass"></i></button>
            </form>
            <div style="color: #94a3b8;">Total: <?= $resultado->num_rows ?> clientes</div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Usuario de Acceso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id_cliente'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div class="avatar-circle">
                                        <?= strtoupper(substr($row['nombre'], 0, 1)) ?>
                                    </div>
                                    <span style="font-weight: 600; color: #f1f5f9;"><?= htmlspecialchars($row['nombre']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem; color: #cbd5e1;">
                                    <i class="ph-bold ph-envelope"></i> <?= htmlspecialchars($row['correo']) ?><br>
                                    <i class="ph-bold ph-phone"></i> <?= htmlspecialchars($row['telefono']) ?>
                                </div>
                            </td>
                            <td style="color: #3b82f6; font-family: monospace; font-size: 1rem;">
                                <?= htmlspecialchars($row['usu_cliente']) ?>
                            </td>
                            <td>
                                <a href="listaUsuarios.php?eliminar=<?= $row['id_cliente'] ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar a este usuario? Esta acción es irreversible.');">
                                    <i class="ph-bold ph-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">No se encontraron usuarios.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
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

// 2. ELIMINAR LIBRO
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql_del = "DELETE FROM libro WHERE id_libro = ?";
    $stmt = $conn->prepare($sql_del);
    $stmt->bind_param("i", $id_eliminar);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert-success'>Libro eliminado correctamente.</div>";
    } else {
        $mensaje = "<div class='alert-error'>No se puede eliminar: Este libro tiene pedidos asociados.</div>";
    }
    $stmt->close();
}

// 3. LÓGICA DEL BUSCADOR
$busqueda = '';
$sql = "SELECT * FROM libro";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $busqueda = $conn->real_escape_string($_GET['q']);
    $sql .= " WHERE titulo LIKE '%$busqueda%' OR autor LIKE '%$busqueda%'";
}

$sql .= " ORDER BY id_libro DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .table-container { background: #1e293b; border-radius: 12px; border: 1px solid #334155; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; vertical-align: middle; }
        th { background: #0f172a; color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: #253346; }
        
        .mini-img { width: 40px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #475569; }
        
        /* Estilos de botones de acción */
        .action-btn { 
            padding: 8px 10px; 
            border-radius: 6px; 
            color: white; 
            text-decoration: none; 
            font-size: 1.1rem; 
            transition: opacity 0.2s; 
            display: inline-block;
        }
        .btn-edit { background: #3b82f6; margin-right: 5px; } /* Azul */
        .btn-delete { background: #ef4444; } /* Rojo */
        .action-btn:hover { opacity: 0.8; }
        
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Estilos del Buscador */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; flex-wrap: wrap; }
        .search-box { display: flex; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 5px 10px; width: 100%; max-width: 400px; }
        .search-input { background: transparent; border: none; color: white; padding: 8px; outline: none; flex-grow: 1; }
        .search-btn { background: #3b82f6; border: none; color: white; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-clear { color: #94a3b8; text-decoration: none; font-size: 0.9rem; margin-left: 10px; }
        .btn-clear:hover { color: #ef4444; }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header"> <i class="ph-fill ph-shield-check"></i> PANEL <span class="highlight">ADMIN</span> </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="pedidos.php" class="nav-item"> <i class="ph-bold ph-list-checks"></i> Pedidos </a>
            <a href="libros.php" class="nav-item active"> <i class="ph-bold ph-books"></i> Inventario Libros </a>
            <a href="nuevo_libro.php" class="nav-item"> <i class="ph-bold ph-plus-circle"></i> Nuevo Libro </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">INVENTARIO DE LIBROS</h2>
        
        <?= $mensaje ?>

        <div class="toolbar">
            <form method="GET" action="libros.php" class="search-box">
                <i class="ph-bold ph-magnifying-glass" style="color: #64748b;"></i>
                <input type="text" name="q" class="search-input" placeholder="Buscar libro o autor..." value="<?= htmlspecialchars($busqueda) ?>">
                <?php if(!empty($busqueda)): ?>
                    <a href="libros.php" class="btn-clear"><i class="ph-bold ph-x"></i></a>
                <?php endif; ?>
                <button type="submit" class="search-btn">Buscar</button>
            </form>
            <a href="nuevo_libro.php" class="btn-action"><i class="ph-bold ph-plus"></i> NUEVO LIBRO</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>Título / Autor</th>
                        <th>Editorial</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): 
                            $ruta_img = "../" . $row['url_imagen'];
                            $mostrar_img = (!empty($row['url_imagen']) && file_exists($ruta_img));
                        ?>
                        <tr>
                            <td>
                                <?php if($mostrar_img): ?>
                                    <img src="<?= $ruta_img ?>" class="mini-img">
                                <?php else: ?>
                                    <div class="mini-img" style="background:#334155; display:flex; align-items:center; justify-content:center;"><i class="ph-duotone ph-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="color: #f1f5f9;">
                                <strong><?= htmlspecialchars($row['titulo']) ?></strong><br>
                                <span style="color: #94a3b8; font-size: 0.9rem;"><?= htmlspecialchars($row['autor']) ?></span>
                            </td>
                            <td style="color: #cbd5e1;"><?= htmlspecialchars($row['editorial']) ?></td>
                            <td style="color: #3b82f6; font-weight: bold;">S/. <?= number_format($row['precio'], 2) ?></td>
                            <td>
                                <?php if($row['stock'] > 5): ?>
                                    <span style="color: #10b981; font-weight: bold;"><?= $row['stock'] ?></span>
                                <?php elseif($row['stock'] > 0): ?>
                                    <span style="color: #eab308; font-weight: bold;"><?= $row['stock'] ?> (Bajo)</span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-weight: bold;">AGOTADO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar_libro.php?id=<?= $row['id_libro'] ?>" class="action-btn btn-edit" title="Editar">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                </a>

                                <a href="libros.php?eliminar=<?= $row['id_libro'] ?>" class="action-btn btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este libro?');" title="Eliminar">
                                    <i class="ph-bold ph-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">No se encontraron libros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
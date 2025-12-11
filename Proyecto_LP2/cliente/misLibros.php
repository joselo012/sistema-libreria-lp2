<?php
session_start();
require_once '../conexion.php';

// 1. Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion();
$id_cliente = $_SESSION['id_usuario'];

// 2. Consulta SQL Compleja (JOINs)
// Traemos: Fecha, Estado, Título, Autor, Precio, Imagen
$sql = "SELECT p.fecha_pedido, p.estado, l.titulo, l.autor, l.url_imagen, dp.cantidad, dp.subtotal
        FROM pedido p
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN libro l ON dp.id_libro = l.id_libro
        WHERE p.id_cliente = ? 
        ORDER BY p.fecha_pedido DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Libros </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Estilos específicos para la Tabla de Historial */
        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px; /* Espacio entre filas */
        }

        .history-row {
            background: #1e293b;
            transition: transform 0.2s;
        }
        
        .history-row td {
            padding: 20px;
            vertical-align: middle;
            border-top: 1px solid #334155;
            border-bottom: 1px solid #334155;
        }
        
        /* Bordes redondeados para la fila */
        .history-row td:first-child {
            border-left: 1px solid #334155;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .history-row td:last-child {
            border-right: 1px solid #334155;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .history-row:hover {
            transform: scale(1.01);
            background: #253346;
        }

        .mini-cover {
            width: 60px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #475569;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Colores según estado */
        .status-pendiente {
            background: rgba(234, 179, 8, 0.15);
            color: #eab308; /* Amarillo */
            border: 1px solid rgba(234, 179, 8, 0.3);
        }
        
        .status-comprado, .status-aprobado, .status-entregado {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981; /* Verde */
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-cancelado {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444; /* Rojo */
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .date-text { color: #94a3b8; font-size: 0.9rem; }
        .title-text { color: #f1f5f9; font-weight: 600; font-size: 1.1rem; font-family: 'Oswald', sans-serif; }
        .author-text { color: #64748b; font-size: 0.9rem; }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> MIEMBRO <span class="highlight">UDH</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="catalogo.php" class="nav-item"> <i class="ph-bold ph-books"></i> Catálogo </a>
            <a href="carrito.php" class="nav-item"> <i class="ph-bold ph-shopping-cart"></i> Mi Carrito </a>
            <a href="misLibros.php" class="nav-item active"> <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <div style="margin-bottom: 30px;">
            <h2 class="section-title">MIS LIBROS Y PEDIDOS</h2>
            <p style="color: var(--text-muted);">Historial de tus solicitudes y compras.</p>
        </div>

        <?php if ($resultado && $resultado->num_rows > 0): ?>
            
            <table class="history-table">
                <?php while($row = $resultado->fetch_assoc()): 
                    // Procesar Imagen
                    $nombre_archivo = $row['url_imagen'];
                    $ruta_completa = "../" . $nombre_archivo;
                    $imagen_final = (!empty($nombre_archivo) && file_exists($ruta_completa)) ? $ruta_completa : null;
                    
                    // Procesar Estado (Normalizamos a minúsculas para comparar)
                    $estado = strtolower($row['estado']);
                    $clase_estado = 'status-pendiente'; // Por defecto
                    $icono_estado = 'ph-hourglass';
                    
                    if ($estado == 'comprado' || $estado == 'aprobado' || $estado == 'entregado') {
                        $clase_estado = 'status-comprado';
                        $icono_estado = 'ph-check-circle';
                    } elseif ($estado == 'cancelado') {
                        $clase_estado = 'status-cancelado';
                        $icono_estado = 'ph-x-circle';
                    }
                ?>
                <tr class="history-row">
                    <td width="80">
                        <?php if($imagen_final): ?>
                            <img src="<?= htmlspecialchars($imagen_final) ?>" alt="Cover" class="mini-cover">
                        <?php else: ?>
                            <div class="mini-cover" style="background: #0f172a; display: flex; align-items: center; justify-content: center; color: #475569;">
                                <i class="ph-duotone ph-book"></i>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="title-text"><?= htmlspecialchars($row['titulo']) ?></div>
                        <div class="author-text"><?= htmlspecialchars($row['autor']) ?></div>
                    </td>

                    <td>
                        <div style="font-weight: 600; color: #cbd5e1;">Fecha de Solicitud</div>
                        <div class="date-text">
                            <i class="ph-bold ph-calendar-blank"></i> 
                            <?= date("d/m/Y", strtotime($row['fecha_pedido'])) ?>
                        </div>
                    </td>

                    <td>
                        <div style="color: #cbd5e1;">S/. <?= number_format($row['subtotal'], 2) ?></div>
                        <div style="font-size: 0.8rem; color: #64748b;">Cant: <?= $row['cantidad'] ?></div>
                    </td>

                    <td align="right">
                        <span class="status-badge <?= $clase_estado ?>">
                            <i class="ph-bold <?= $icono_estado ?>"></i> 
                            <?= ucfirst($estado) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        <?php else: ?>
            
            <div style="text-align: center; padding: 60px; color: var(--text-muted); border: 2px dashed #334155; border-radius: 12px;">
                <i class="ph-duotone ph-books" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h3>Aún no tienes libros en tu historial</h3>
                <p>Visita el catálogo para solicitar tu primera lectura.</p>
                <br>
                <a href="catalogo.php" class="btn-action" style="display: inline-block; width: auto; padding: 10px 30px; text-decoration: none;">
                    IR AL CATÁLOGO
                </a>
            </div>

        <?php endif; ?>

    </main>
</body>
</html>
<?php 
if(isset($db)) {
    $db->cerrarConexion();
}
?>
<?php
session_start();
require_once '../conexion.php';

// 1. SEGURIDAD: Verificar que sea ADMIN
// Si intenta entrar alguien que no es admin, lo botamos al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion();
$mensaje = '';

// 2. LÓGICA: PROCESAR ACCIONES (Aprobar o Cancelar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_pedido = intval($_GET['id']);
    $accion = $_GET['accion'];
    $nuevo_estado = '';

    if ($accion == 'aprobar') {
        // Cambiamos estado a 'aprobado' (o 'comprado' si prefieres ese término)
        $nuevo_estado = 'aprobado';
        $msg_texto = "Pedido #$id_pedido marcado como ENTREGADO.";
        $msg_clase = "alert-success";
    } elseif ($accion == 'cancelar') {
        $nuevo_estado = 'cancelado';
        $msg_texto = "Pedido #$id_pedido ha sido CANCELADO.";
        $msg_clase = "alert-error";
        
        // OJO: Aquí podrías agregar lógica para devolver el stock al libro si quisieras (opcional)
    }

    if ($nuevo_estado != '') {
        $sql_update = "UPDATE pedido SET estado = ? WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("si", $nuevo_estado, $id_pedido);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='$msg_clase'><i class='ph-bold ph-info'></i> $msg_texto</div>";
        } else {
            $mensaje = "<div class='alert-error'>Error al actualizar: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
}

// 3. CONSULTA: OBTENER LISTA DE PEDIDOS
// Hacemos un JOIN con la tabla cliente para saber el nombre de quien pidió
$sql = "SELECT p.id_pedido, p.fecha_pedido, p.total, p.estado, c.nombre, c.usu_cliente, c.telefono
        FROM pedido p
        JOIN cliente c ON p.id_cliente = c.id_cliente
        ORDER BY 
            CASE WHEN p.estado = 'pendiente' THEN 1 ELSE 2 END, -- Pendientes primero
            p.fecha_pedido DESC"; // Luego por fecha

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Estilos específicos para la tabla de administración */
        .table-container {
            background: #1e293b;
            border-radius: 12px;
            border: 1px solid #334155;
            overflow-x: auto; /* Para que no rompa en móviles */
        }
        
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        
        th { 
            background: #0f172a; 
            color: #94a3b8; 
            padding: 15px; 
            text-align: left; 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            border-bottom: 1px solid #334155;
        }
        
        td { 
            padding: 15px; 
            border-bottom: 1px solid #334155; 
            color: #e2e8f0; 
            vertical-align: middle;
        }
        
        tr:hover { background: #253346; }

        /* Badges de estado */
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-pendiente { background: rgba(234, 179, 8, 0.2); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.3); }
        .bg-aprobado { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .bg-cancelado { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Botones de acción */
        .btn-mini {
            padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 5px; margin-right: 5px; transition: opacity 0.2s;
        }
        .btn-mini:hover { opacity: 0.8; }
        
        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        
        .user-info small { display: block; color: #94a3b8; font-size: 0.8rem; margin-top: 3px; }
        
        /* Alertas */
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-shield-check"></i> PANEL <span class="highlight">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="pedidos.php" class="nav-item active"> <i class="ph-bold ph-list-checks"></i> Pedidos </a>
            <a href="libros.php" class="nav-item"> <i class="ph-bold ph-books"></i> Inventario Libros </a>
            <a href="nuevo_libro.php" class="nav-item"> <i class="ph-bold ph-plus-circle"></i> Nuevo Libro </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">CONTROL DE PEDIDOS</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Gestiona las solicitudes de préstamo y compra.</p>
        
        <?= $mensaje ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): 
                            $estado = strtolower($row['estado']);
                            
                            // Definir estilo del badge según estado
                            $badge_class = 'bg-pendiente';
                            if($estado == 'aprobado' || $estado == 'comprado') $badge_class = 'bg-aprobado';
                            if($estado == 'cancelado') $badge_class = 'bg-cancelado';
                        ?>
                        <tr>
                            <td><strong>#<?= $row['id_pedido'] ?></strong></td>
                            
                            <td class="user-info">
                                <strong><?= htmlspecialchars($row['nombre']) ?></strong>
                                <small>
                                    <i class="ph-bold ph-user"></i> <?= htmlspecialchars($row['usu_cliente']) ?> | 
                                    <i class="ph-bold ph-phone"></i> <?= htmlspecialchars($row['telefono']) ?>
                                </small>
                            </td>
                            
                            <td>
                                <?= date("d/m/Y", strtotime($row['fecha_pedido'])) ?><br>
                                <span style="font-size: 0.8rem; color: #64748b;"><?= date("H:i A", strtotime($row['fecha_pedido'])) ?></span>
                            </td>
                            
                            <td style="font-weight: 600; color: #3b82f6;">S/. <?= number_format($row['total'], 2) ?></td>
                            
                            <td><span class="badge <?= $badge_class ?>"><?= ucfirst($estado) ?></span></td>
                            
                            <td>
                                <?php if($estado == 'pendiente'): ?>
                                    <a href="pedidos.php?accion=aprobar&id=<?= $row['id_pedido'] ?>" class="btn-mini btn-approve" title="Aprobar Solicitud">
                                        <i class="ph-bold ph-check"></i> Aprobar
                                    </a>
                                    <a href="pedidos.php?accion=cancelar&id=<?= $row['id_pedido'] ?>" class="btn-mini btn-reject" onclick="return confirm('¿Estás seguro de cancelar este pedido?');" title="Rechazar">
                                        <i class="ph-bold ph-x"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #64748b; font-size: 0.85rem;"><i class="ph-bold ph-lock-key"></i> Finalizado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">
                                <i class="ph-duotone ph-inbox" style="font-size: 3rem; margin-bottom: 10px;"></i><br>
                                No hay pedidos registrados en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
<?php 
if(isset($db)) {
    $db->cerrarConexion();
}
?>
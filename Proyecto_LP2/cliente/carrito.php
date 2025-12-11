<?php
session_start();
require_once '../conexion.php';

// 1. Seguridad: Solo clientes
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion();
$id_cliente = $_SESSION['id_usuario'];
$mensaje = '';

// 2. Lógica: ELIMINAR UN ITEM
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id_eliminar]);
    header("Location: carrito.php"); // Recargar para actualizar vista
    exit;
}

// 3. Lógica: PROCESAR EL PEDIDO (CHECKOUT)
if (isset($_POST['confirmar_pedido'])) {
    if (!empty($_SESSION['carrito'])) {
        try {
            // INICIAMOS TRANSACCIÓN (Todo o nada)
            $conn->begin_transaction();

            $total_pedido = 0;
            $items_procesados = [];

            // A. Calcular total y validar stock preliminar
            // Primero obtenemos los precios actuales de la BD
            $ids = implode(',', array_keys($_SESSION['carrito']));
            $sql_precios = "SELECT id_libro, precio, stock FROM libro WHERE id_libro IN ($ids)";
            $res_precios = $conn->query($sql_precios);

            while ($libro = $res_precios->fetch_assoc()) {
                $cant = $_SESSION['carrito'][$libro['id_libro']];
                
                if ($libro['stock'] < $cant) {
                    throw new Exception("El libro con ID " . $libro['id_libro'] . " ya no tiene stock suficiente.");
                }
                
                $subtotal = $libro['precio'] * $cant;
                $total_pedido += $subtotal;
                
                // Guardamos datos para usarlos luego
                $items_procesados[] = [
                    'id' => $libro['id_libro'],
                    'cantidad' => $cant,
                    'precio' => $libro['precio'],
                    'subtotal' => $subtotal
                ];
            }

            // B. Insertar en tabla PEDIDO
            $sql_pedido = "INSERT INTO pedido (id_cliente, fecha_pedido, total, estado) VALUES (?, NOW(), ?, 'pendiente')";
            $stmt_ped = $conn->prepare($sql_pedido);
            $stmt_ped->bind_param("id", $id_cliente, $total_pedido);
            $stmt_ped->execute();
            $id_pedido_generado = $conn->insert_id;
            $stmt_ped->close();

            // C. Insertar DETALLES y ACTUALIZAR STOCK
            $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_libro, cantidad, subtotal) VALUES (?, ?, ?, ?)";
            $stmt_det = $conn->prepare($sql_detalle);

            $sql_update = "UPDATE libro SET stock = stock - ? WHERE id_libro = ?";
            $stmt_upd = $conn->prepare($sql_update);

            foreach ($items_procesados as $item) {
                // Insertar detalle
                $stmt_det->bind_param("iiid", $id_pedido_generado, $item['id'], $item['cantidad'], $item['subtotal']);
                $stmt_det->execute();

                // Restar stock
                $stmt_upd->bind_param("ii", $item['cantidad'], $item['id']);
                $stmt_upd->execute();
            }

            // SI TODO SALIÓ BIEN: CONFIRMAR (COMMIT)
            $conn->commit();
            
            // Vaciar carrito
            unset($_SESSION['carrito']);
            $mensaje = "<div class='alert-success'>¡Pedido realizado con éxito! Tu ID de pedido es #$id_pedido_generado.</div>";

        } catch (Exception $e) {
            // SI HUBO ERROR: DESHACER CAMBIOS (ROLLBACK)
            $conn->rollback();
            $mensaje = "<div class='alert-error'>Error al procesar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div class='alert-error'>Tu carrito está vacío.</div>";
    }
}

// 4. Obtener datos para MOSTRAR EL CARRITO (Vista)
$libros_en_carrito = [];
$total_vista = 0;

if (!empty($_SESSION['carrito'])) {
    // Convertimos las llaves del array (IDs) en un string separado por comas: "1,4,6"
    $ids = implode(',', array_keys($_SESSION['carrito']));
    
    // Consulta segura usando IN
    $sql_vista = "SELECT * FROM libro WHERE id_libro IN ($ids)";
    $result_vista = $conn->query($sql_vista);
    
    if ($result_vista) {
        while ($row = $result_vista->fetch_assoc()) {
            $row['cantidad_solicitada'] = $_SESSION['carrito'][$row['id_libro']];
            $row['subtotal'] = $row['precio'] * $row['cantidad_solicitada'];
            $total_vista += $row['subtotal'];
            $libros_en_carrito[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #334155;
            color: #e2e8f0;
        }
        .cart-table th {
            background: #0f172a;
            font-weight: 600;
            color: #94a3b8;
        }
        .cart-summary {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            text-align: right;
            border: 1px solid #334155;
        }
        .total-price {
            font-size: 2rem;
            color: #3b82f6;
            font-family: 'Oswald', sans-serif;
            margin: 10px 0;
        }
        .btn-delete {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            padding: 5px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.3); }
        
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }
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
            <a href="carrito.php" class="nav-item active"> <i class="ph-bold ph-shopping-cart"></i> Mi Carrito </a>
            <a href="misLibros.php" class="nav-item"> <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">RESUMEN DE PEDIDO</h2>
        
        <?= $mensaje ?>

        <?php if (empty($libros_en_carrito) && empty($mensaje)): ?>
            
            <div style="text-align: center; padding: 50px; color: #64748b;">
                <i class="ph-duotone ph-shopping-cart" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h3>Tu carrito está vacío</h3>
                <p>Parece que aún no has agregado libros.</p>
                <br>
                <a href="catalogo.php" class="btn-action" style="display: inline-block; width: auto; padding: 10px 30px;">IR AL CATÁLOGO</a>
            </div>

        <?php elseif (!empty($libros_en_carrito)): ?>
            
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unit.</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($libros_en_carrito as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['titulo']) ?></strong><br>
                            <span style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($item['autor']) ?></span>
                        </td>
                        <td>S/. <?= number_format($item['precio'], 2) ?></td>
                        <td><?= $item['cantidad_solicitada'] ?></td>
                        <td style="color: #10b981; font-weight: bold;">S/. <?= number_format($item['subtotal'], 2) ?></td>
                        <td>
                            <a href="carrito.php?eliminar=<?= $item['id_libro'] ?>" class="btn-delete" onclick="return confirm('¿Quitar este libro?');">
                                <i class="ph-bold ph-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <p style="color: #94a3b8;">Total a Pagar:</p>
                <div class="total-price">S/. <?= number_format($total_vista, 2) ?></div>
                
                <form method="POST">
                    <a href="catalogo.php" style="color: #64748b; margin-right: 20px; text-decoration: none;">Seguir buscando</a>
                    <button type="submit" name="confirmar_pedido" class="btn-action" style="width: auto; padding: 15px 40px;">
                        CONFIRMAR PRÉSTAMO <i class="ph-bold ph-check-circle"></i>
                    </button>
                </form>
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
<?php
session_start();
require_once '../conexion.php';

// 1. Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->iniciar();
$id_cliente = $_SESSION['usuario_id'];
$mensaje = '';

// 2. Lógica: SOLICITAR LIBRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alquilar'])) {
    $id_libro = $_POST['id_libro'];
    $precio = $_POST['precio'];

    try {
        // Verificar stock antes de procesar
        $stock = $conn->query("SELECT stock FROM libro WHERE id_libro = $id_libro")->fetchColumn();
        
        if ($stock > 0) {
            // a) Crear Pedido
            $sql1 = "INSERT INTO pedido (id_cliente, fecha_pedido, total, estado) VALUES (?, NOW(), ?, 'pendiente')";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->execute([$id_cliente, $precio]);
            $id_pedido = $conn->lastInsertId();

            // b) Crear Detalle
            $sql2 = "INSERT INTO detalle_pedido (id_pedido, id_libro, cantidad, subtotal) VALUES (?, ?, 1, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute([$id_pedido, $id_libro, $precio]);

            // c) Restar Stock
            $conn->query("UPDATE libro SET stock = stock - 1 WHERE id_libro = $id_libro");

            $mensaje = "<div class='alert-success'>¡Solicitud enviada con éxito! <a href='misLibros.php'>Ver mis libros</a></div>";
        } else {
            $mensaje = "<div class='alert-error'>Lo sentimos, este libro se acaba de agotar.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert-error'>Error al procesar: " . $e->getMessage() . "</div>";
    }
}

// 3. Listar Libros
$libros = $conn->query("SELECT * FROM libro WHERE stock > 0 ORDER BY id_libro DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo | Nexus Member</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> NEXUS <span class="highlight">MEMBER</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            <a href="catalogo.php" class="nav-item active"> <i class="ph-bold ph-books"></i> Catálogo
            </a>
            <a href="misLibros.php" class="nav-item">
                <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros
            </a>
            <a href="perfil.php" class="nav-item">
                <i class="ph-bold ph-user-gear"></i> Mi Perfil
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">CATÁLOGO DISPONIBLE</h2>
        
        <?= $mensaje ?>

        <div class="gallery-container" style="padding: 0; max-width: 100%;">
            <?php if(empty($libros)): ?>
                <p style="color:var(--text-muted)">No hay libros disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach($libros as $l): ?>
                <div class="book-card">
                    <input type="checkbox" id="b<?= $l['id_libro'] ?>" class="toggle-input">
                    <label for="b<?= $l['id_libro'] ?>" class="card-inner">
                        <div class="card-front">
                            <img src="<?= $l['url_imagen'] ?>" alt="Portada">
                            <div class="author-badge">
                                <i class="ph-bold ph-pen-nib"></i> <?= htmlspecialchars($l['autor']) ?>
                            </div>
                        </div>
                        <div class="card-back">
                            <div class="back-content">
                                <span class="book-code">STOCK: <?= $l['stock'] ?></span>
                                <h3 class="hook">"<?= htmlspecialchars($l['editorial']) ?>"</h3>
                                <h4 class="book-title"><?= htmlspecialchars($l['titulo']) ?></h4>
                                <p class="summary">
                                    ISBN: <?= $l['isbn'] ?><br>
                                    Precio: S/. <?= number_format($l['precio'], 2) ?>
                                </p>
                                <form method="POST" style="width:100%">
                                    <input type="hidden" name="id_libro" value="<?= $l['id_libro'] ?>">
                                    <input type="hidden" name="precio" value="<?= $l['precio'] ?>">
                                    <button name="alquilar" class="btn-action" style="width:100%">SOLICITAR</button>
                                </form>
                            </div>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
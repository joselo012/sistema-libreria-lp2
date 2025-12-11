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

// Verificar si llega un ID
if (!isset($_GET['id'])) {
    header("Location: libros.php");
    exit;
}
$id_libro = $_GET['id'];

// 2. PROCESAR ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editorial = $_POST['editorial'];
    $isbn = $_POST['isbn'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    // Lógica para la Imagen
    $nombre_imagen = $_POST['imagen_actual']; // Por defecto mantenemos la vieja

    // Si se subió una nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_original = $_FILES['imagen']['name'];
        $temp = $_FILES['imagen']['tmp_name'];
        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
        $nombre_imagen = "libro_" . time() . "." . $extension;
        $destino = "../img/" . $nombre_imagen;
        
        move_uploaded_file($temp, $destino);
    }

    // Actualizar BD
    $sql_update = "UPDATE libro SET titulo=?, autor=?, editorial=?, isbn=?, precio=?, stock=?, url_imagen=? WHERE id_libro=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssdisi", $titulo, $autor, $editorial, $isbn, $precio, $stock, $nombre_imagen, $id_libro);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert-success'>Libro actualizado correctamente. <a href='libros.php'>Volver al inventario</a></div>";
    } else {
        $mensaje = "<div class='alert-error'>Error al actualizar: " . $conn->error . "</div>";
    }
}

// 3. OBTENER DATOS ACTUALES
$sql = "SELECT * FROM libro WHERE id_libro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$resultado = $stmt->get_result();
$libro = $resultado->fetch_assoc();

if (!$libro) {
    header("Location: libros.php"); // Si no existe el ID
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Libro | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .form-card { background: #1e293b; padding: 40px; border-radius: 12px; border: 1px solid #334155; max-width: 800px; margin: 0 auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 8px; color: #94a3b8; font-size: 0.9rem; font-weight: 600; }
        input[type="text"], input[type="number"], input[type="file"] {
            width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; outline: none;
        }
        input:focus { border-color: #3b82f6; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }
        .current-img { margin-top: 10px; max-width: 100px; border-radius: 8px; border: 1px solid #475569; }
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
        <div class="sidebar-footer"> <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a> </div>
    </aside>

    <main class="admin-content">
        <div style="display:flex; align-items:center; gap:15px; margin-bottom: 20px;">
            <a href="libros.php" style="color:white; font-size:1.5rem;"><i class="ph-bold ph-arrow-left"></i></a>
            <h2 class="section-title" style="margin:0;">EDITAR LIBRO</h2>
        </div>
        
        <?= $mensaje ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    <div class="full-width">
                        <label>Título del Libro</label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required>
                    </div>

                    <div>
                        <label>Autor</label>
                        <input type="text" name="autor" value="<?= htmlspecialchars($libro['autor']) ?>" required>
                    </div>

                    <div>
                        <label>Editorial</label>
                        <input type="text" name="editorial" value="<?= htmlspecialchars($libro['editorial']) ?>">
                    </div>

                    <div>
                        <label>ISBN</label>
                        <input type="text" name="isbn" value="<?= htmlspecialchars($libro['isbn']) ?>">
                    </div>

                    <div>
                        <label>Precio (S/.)</label>
                        <input type="number" step="0.01" name="precio" value="<?= $libro['precio'] ?>" required>
                    </div>

                    <div>
                        <label>Stock Disponible</label>
                        <input type="number" name="stock" value="<?= $libro['stock'] ?>" required>
                    </div>

                    <div class="full-width">
                        <label>Imagen de Portada (Opcional)</label>
                        <input type="hidden" name="imagen_actual" value="<?= $libro['url_imagen'] ?>">
                        <input type="file" name="imagen" accept="image/*">
                        
                        <?php if(!empty($libro['url_imagen'])): ?>
                            <div style="margin-top: 10px; color: #94a3b8; font-size: 0.9rem;">
                                Imagen actual:<br>
                                <img src="../img/<?= $libro['url_imagen'] ?>" class="current-img">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-action" style="width: 100%; margin-top: 30px; font-size: 1.1rem;">
                    <i class="ph-bold ph-floppy-disk"></i> ACTUALIZAR LIBRO
                </button>
            </form>
        </div>
    </main>
</body>
</html>
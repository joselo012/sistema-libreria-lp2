<?php
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Conexion();
$conn = $db->getConexion();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editorial = $_POST['editorial'];
    $isbn = $_POST['isbn'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    // MANEJO DE IMAGEN
    $nombre_imagen = '';
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_original = $_FILES['imagen']['name'];
        $temp = $_FILES['imagen']['tmp_name'];
        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
        
        // Crear un nombre único para evitar que se reemplacen fotos con el mismo nombre
        // Ejemplo: libro_170123456.jpg
        $nombre_imagen = "libro_" . time() . "." . $extension;
        
        // Carpeta destino (Retrocedemos a la raíz y entramos a img)
        $destino = "../" . $nombre_imagen;
        
        if (!move_uploaded_file($temp, $destino)) {
            $mensaje = "<div class='alert-error'>Error al subir la imagen al servidor.</div>";
        }
    }

    if (empty($mensaje)) {
        // Insertar en BD
        $sql = "INSERT INTO libro (titulo, autor, editorial, isbn, precio, stock, url_imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdis", $titulo, $autor, $editorial, $isbn, $precio, $stock, $nombre_imagen);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert-success'>¡Libro registrado con éxito!</div>";
        } else {
            $mensaje = "<div class='alert-error'>Error BD: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Libro | Admin</title>
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
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header"> <i class="ph-fill ph-shield-check"></i> PANEL <span class="highlight">ADMIN</span> </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="pedidos.php" class="nav-item"> <i class="ph-bold ph-list-checks"></i> Pedidos </a>
            <a href="libros.php" class="nav-item"> <i class="ph-bold ph-books"></i> Inventario Libros </a>
            <a href="nuevo_libro.php" class="nav-item active"> <i class="ph-bold ph-plus-circle"></i> Nuevo Libro </a>
        </nav>
        <div class="sidebar-footer"> <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a> </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">REGISTRAR NUEVO LIBRO</h2>
        <?= $mensaje ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="full-width">
                        <label>Título del Libro</label>
                        <input type="text" name="titulo" required placeholder="Ej: Cien años de soledad">
                    </div>

                    <div>
                        <label>Autor</label>
                        <input type="text" name="autor" required placeholder="Ej: Gabriel García Márquez">
                    </div>

                    <div>
                        <label>Editorial</label>
                        <input type="text" name="editorial" placeholder="Ej: Planeta">
                    </div>

                    <div>
                        <label>ISBN</label>
                        <input type="text" name="isbn" placeholder="Código único">
                    </div>

                    <div>
                        <label>Precio (S/.)</label>
                        <input type="number" step="0.01" name="precio" required placeholder="0.00">
                    </div>

                    <div>
                        <label>Stock Disponible</label>
                        <input type="number" name="stock" required placeholder="Cantidad">
                    </div>

                    <div class="full-width">
                        <label>Imagen de Portada</label>
                        <input type="file" name="imagen" accept="image/*">
                        <small style="color: #64748b; margin-top:5px; display:block;">Se guardará en la carpeta /img</small>
                    </div>
                </div>

                <button type="submit" class="btn-action" style="width: 100%; margin-top: 30px; font-size: 1.1rem;">
                    <i class="ph-bold ph-floppy-disk"></i> GUARDAR LIBRO
                </button>
            </form>
        </div>
    </main>
</body>
</html>
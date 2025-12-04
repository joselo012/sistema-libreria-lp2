<?php
session_start();
require_once '../conexion.php';

// --- SEGURIDAD ESTRICTA ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = '';
$lista_libros = [];
$conexion_activa = false;
$db = new Conexion();

try {
    $conn = $db->iniciar();
    if ($conn) {
        $conexion_activa = true;

        // Eliminar
        if (isset($_GET['borrar'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM libro WHERE id_libro = :id");
                $stmt->execute([':id' => $_GET['borrar']]);
                header("Location: Libros.php?msg=ok");
                exit;
            } catch (Exception $e) { $mensaje = "<div class='alert-error'>No se pudo eliminar.</div>"; }
        }

        // Agregar
        if (isset($_POST['crear_libro'])) {
            try {
                $sql = "INSERT INTO libro (titulo, autor, editorial, isbn, precio, stock, url_imagen) VALUES (?,?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $_POST['titulo'], $_POST['autor'], $_POST['editorial'], 
                    $_POST['isbn'], $_POST['precio'], $_POST['stock'], $_POST['url_imagen']
                ]);
                $mensaje = "<div class='alert-success'>Libro agregado correctamente.</div>";
            } catch (Exception $e) { $mensaje = "<div class='alert-error'>Error: " . $e->getMessage() . "</div>"; }
        }

        $lista_libros = $conn->query("SELECT * FROM libro ORDER BY id_libro DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $mensaje = "<div class='alert-warning'>Sin conexión a BD.</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Libros | Admin</title>
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
            <a href="indexAdmin.php" class="nav-item">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            <a href="listaUsuarios.php" class="nav-item">
                <i class="ph-bold ph-users"></i> Usuarios
            </a>
            <a href="Libros.php" class="nav-item active">
                <i class="ph-bold ph-books"></i> Libros
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="nav-item small" style="color: <?= $conexion_activa ? '#10b981' : '#ef4444' ?>">
                <i class="ph-fill ph-circle"></i> <?= $conexion_activa ? 'Online' : 'Offline' ?>
            </div>
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Salir</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">GESTIÓN DE BIBLIOTECA</h2>
        
        <?= $mensaje ?>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3>Nuevo Libro</h3>
                <form method="POST">
                    <input type="hidden" name="crear_libro" value="1">
                    <div class="form-group"><label>Título</label><input type="text" name="titulo" class="tech-input" required></div>
                    <div class="form-group"><label>Autor</label><input type="text" name="autor" class="tech-input" required></div>
                    <div class="form-group"><label>ISBN</label><input type="text" name="isbn" class="tech-input" required></div>
                    <div class="form-row">
                        <div class="form-group"><label>Precio</label><input type="number" step="0.01" name="precio" class="tech-input" required></div>
                        <div class="form-group"><label>Stock</label><input type="number" name="stock" class="tech-input" required></div>
                    </div>
                    <div class="form-group"><label>URL Imagen</label><input type="text" name="url_imagen" class="tech-input" placeholder="http://..." required></div>
                    
                    <button class="btn-action btn-full" <?= !$conexion_activa ? 'disabled' : '' ?>>
                        <?= $conexion_activa ? 'AGREGAR LIBRO' : 'OFFLINE' ?>
                    </button>
                </form>
            </div>

            <div class="admin-card table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Portada</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($lista_libros)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px;">No hay libros registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach($lista_libros as $l): ?>
                            <tr>
                                <td><img src="<?= $l['url_imagen'] ?>" class="mini-img"></td>
                                <td><?= $l['titulo'] ?></td>
                                <td class="muted"><?= $l['autor'] ?></td>
                                <td class="<?= $l['stock']<5 ? 'text-danger':'text-success' ?>"><?= $l['stock'] ?></td>
                                <td>
                                    <a href="Libros.php?borrar=<?= $l['id_libro'] ?>" class="btn-del" onclick="return confirm('¿Eliminar?');">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
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
$id_cliente = $_SESSION['id_usuario'] ?? 0; 
$mensaje = '';

// 2. Lógica: AGREGAR AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $id_libro = $_POST['id_libro'];
    
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    if (isset($_SESSION['carrito'][$id_libro])) {
        $mensaje = "<div class='alert-error'>Este libro ya está en tu carrito.</div>";
    } else {
        $_SESSION['carrito'][$id_libro] = 1;
        header("Location: catalogo.php?agregado=1"); // Limpiamos POST
        exit;
    }
}

if (isset($_GET['agregado'])) {
    $mensaje = "<div class='alert-success'>¡Libro agregado al carrito!</div>";
}

$cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;

// ---------------------------------------------------------
// 3. LÓGICA DEL BUSCADOR
// ---------------------------------------------------------
$busqueda = '';
$sql = "SELECT * FROM libro WHERE stock > 0";

// Verificamos si alguien escribió algo en el buscador
if (isset($_GET['q']) && !empty($_GET['q'])) {
    // Limpiamos la entrada para evitar caracteres raros
    $busqueda = $conn->real_escape_string($_GET['q']);
    
    // Agregamos el filtro a la consulta SQL
    // El operador LIKE con % busca texto parcial
    // Buscamos en TITULO o en AUTOR
    $sql .= " AND (titulo LIKE '%$busqueda%' OR autor LIKE '%$busqueda%')";
}

$sql .= " ORDER BY id_libro DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* --- ESTILOS AUXILIARES --- */
        .badge-count { background-color: #ef4444; color: white; font-size: 0.75rem; padding: 2px 6px; border-radius: 99px; margin-left: auto; }
        .btn-disabled { background: #475569 !important; cursor: not-allowed; opacity: 0.7; color: #cbd5e1 !important; }
        .alert-success { color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2); }
        .alert-error { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); }

        /* --- ESTILOS DEL BUSCADOR --- */
        .search-container {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            background: #1e293b;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #334155;
            align-items: center;
        }

        .search-input {
            flex-grow: 1;
            background: transparent;
            border: none;
            color: white;
            font-size: 1rem;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        .search-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-btn:hover { background: #2563eb; }
        
        .btn-clear {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-clear:hover { color: #ef4444; }

        /* --- GRID DE LIBROS --- */
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #334155;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            border-color: #3b82f6;
        }

        .book-cover-container {
            width: 100%;
            height: 280px; 
            background: #0f172a;
            position: relative;
            border-bottom: 1px solid #334155;
            overflow: hidden;
        }

        .book-cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            object-position: top center;
        }

        .stock-tag {
            position: absolute; top: 10px; right: 10px; background: rgba(0, 0, 0, 0.7);
            color: #10b981; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: bold;
            backdrop-filter: blur(4px); border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .book-info { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }
        .book-title { margin: 0 0 5px 0; color: #f1f5f9; font-size: 1.1rem; line-height: 1.3; }
        .book-author { color: #94a3b8; font-size: 0.9rem; margin-bottom: 15px; }
        .book-footer { margin-top: auto; display: flex; flex-direction: column; gap: 10px; }
        .book-price { font-size: 1.2rem; font-weight: 700; color: #3b82f6; }

        .btn-add {
            width: 100%; padding: 10px; border: none; border-radius: 6px; background: #3b82f6;
            color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: 8px; transition: background 0.2s;
        }
        .btn-add:hover { background: #2563eb; }

        .placeholder-img {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; color: #475569;
        }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> MIEMBRO <span class="highlight">UDH</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="catalogo.php" class="nav-item active"> <i class="ph-bold ph-books"></i> Catálogo </a>
            <a href="carrito.php" class="nav-item" style="justify-content: start;">
                <i class="ph-bold ph-shopping-cart"></i> Mi Carrito
                <?php if($cantidad_carrito > 0): ?>
                    <span class="badge-count"><?= $cantidad_carrito ?></span>
                <?php endif; ?>
            </a>
            <a href="misLibros.php" class="nav-item"> <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">CATÁLOGO DISPONIBLE</h2>
        <?= $mensaje ?>

        <form method="GET" action="catalogo.php" class="search-container">
            <i class="ph-bold ph-magnifying-glass" style="font-size: 1.2rem; color: #64748b;"></i>
            
            <input type="text" name="q" class="search-input" 
                   placeholder="Buscar por título o autor..." 
                   value="<?= htmlspecialchars($busqueda) ?>"> <?php if(!empty($busqueda)): ?>
                <a href="catalogo.php" class="btn-clear"><i class="ph-bold ph-x"></i> Limpiar</a>
            <?php endif; ?>

            <button type="submit" class="search-btn">BUSCAR</button>
        </form>

        <div class="gallery-container">
            <?php 
            if ($resultado && $resultado->num_rows > 0): 
                while($l = $resultado->fetch_assoc()):
                    $en_carrito = isset($_SESSION['carrito'][$l['id_libro']]);
                    
                    // RUTA DE IMAGEN
                    $nombre_archivo = $l['url_imagen']; 
                    $ruta_completa = "../" . $nombre_archivo; 
                    $mostrar_imagen = (!empty($nombre_archivo) && file_exists($ruta_completa));
            ?>
                <div class="book-card">
                    
                    <div class="book-cover-container">
                        <div class="stock-tag">Stock: <?= $l['stock'] ?></div>
                        
                        <?php if($mostrar_imagen): ?>
                            <img src="<?= htmlspecialchars($ruta_completa) ?>" alt="Portada" class="book-cover-img">
                        <?php else: ?>
                            <div class="placeholder-img">
                                <i class="ph-duotone ph-image-broken" style="font-size: 3rem;"></i>
                                <span style="font-size: 0.8rem; margin-top: 5px">Sin imagen</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="book-info">
                        <h3 class="book-title"><?= htmlspecialchars($l['titulo']) ?></h3>
                        <p class="book-author"><?= htmlspecialchars($l['autor']) ?></p>
                        
                        <div class="book-footer">
                            <div class="book-price">S/. <?= number_format($l['precio'], 2) ?></div>
                            
                            <form method="POST">
                                <input type="hidden" name="id_libro" value="<?= $l['id_libro'] ?>">
                                
                                <?php if($en_carrito): ?>
                                    <button type="button" class="btn-add btn-disabled" disabled>
                                        <i class="ph-bold ph-check"></i> En Carrito
                                    </button>
                                <?php else: ?>
                                    <button name="agregar" class="btn-add">
                                        <i class="ph-bold ph-shopping-cart-simple"></i> Agregar
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8; border: 1px dashed #334155; border-radius: 12px;">
                    <i class="ph-duotone ph-magnifying-glass" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h3>No encontramos libros con "<?= htmlspecialchars($busqueda) ?>"</h3>
                    <p>Intenta con otra palabra o verifica la ortografía.</p>
                    <br>
                    <a href="catalogo.php" class="btn-add" style="width: auto; display: inline-block;">Ver todos los libros</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php if(isset($db)) { $db->cerrarConexion(); } ?>
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
$mensaje = '';
$tipo_mensaje = '';

// 2. Lógica: ACTUALIZAR DATOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibir datos del formulario
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $clave_nueva = $_POST['clave_nueva'];
    $clave_confirmar = $_POST['clave_confirmar'];

    // Validaciones básicas
    if (empty($nombre) || empty($correo)) {
        $mensaje = "El nombre y el correo son obligatorios.";
        $tipo_mensaje = "alert-error";
    } else {
        // LÓGICA DE CONTRASEÑA
        // Si el campo de contraseña NO está vacío, quiere decir que el usuario quiere cambiarla
        if (!empty($clave_nueva)) {
            if ($clave_nueva === $clave_confirmar) {
                // Actualizamos TODO incluyendo contraseña
                // NOTA: Recuerda que estamos usando texto plano según tu login.php anterior.
                $sql_update = "UPDATE cliente SET nombre = ?, correo = ?, telefono = ?, pass_cli = ? WHERE id_cliente = ?";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $clave_nueva, $id_cliente);
            } else {
                $mensaje = "Las nuevas contraseñas no coinciden.";
                $tipo_mensaje = "alert-error";
            }
        } else {
            // Si la contraseña está vacía, solo actualizamos los datos personales (mantenemos la clave vieja)
            $sql_update = "UPDATE cliente SET nombre = ?, correo = ?, telefono = ? WHERE id_cliente = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("sssi", $nombre, $correo, $telefono, $id_cliente);
        }

        // Ejecutar la actualización si no hubo error de validación previa
        if (empty($mensaje) && isset($stmt)) {
            if ($stmt->execute()) {
                $mensaje = "¡Perfil actualizado correctamente!";
                $tipo_mensaje = "alert-success";
                
                // Actualizar el nombre en la sesión también para que cambie en el saludo de inmediato
                $_SESSION['nombre_usuario'] = $nombre;
            } else {
                $mensaje = "Error al actualizar: " . $conn->error;
                $tipo_mensaje = "alert-error";
            }
            $stmt->close();
        }
    }
}

// 3. Lógica: OBTENER DATOS ACTUALES (Para mostrarlos en el form)
$sql_datos = "SELECT * FROM cliente WHERE id_cliente = ?";
$stmt_datos = $conn->prepare($sql_datos);
$stmt_datos->bind_param("i", $id_cliente);
$stmt_datos->execute();
$resultado = $stmt_datos->get_result();
$usuario = $resultado->fetch_assoc();
$stmt_datos->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Columna izquierda pequeña, derecha grande */
            gap: 30px;
        }

        @media (max-width: 768px) {
            .profile-container { grid-template-columns: 1fr; }
        }

        .profile-card-side {
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #334155;
            height: fit-content;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 20px auto;
            border: 4px solid #0f172a;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .profile-card-form {
            background: #1e293b;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #334155;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }

        .label {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .input-dark {
            width: 100%;
            padding: 12px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-dark:focus { border-color: #3b82f6; }
        .input-dark:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-save {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.2s;
        }
        .btn-save:hover { opacity: 0.9; }

        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> NEXUS <span class="highlight">MEMBER</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item"> <i class="ph-bold ph-squares-four"></i> Inicio </a>
            <a href="catalogo.php" class="nav-item"> <i class="ph-bold ph-books"></i> Catálogo </a>
            <a href="carrito.php" class="nav-item"> <i class="ph-bold ph-shopping-cart"></i> Mi Carrito </a>
            <a href="misLibros.php" class="nav-item"> <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros </a>
            <a href="perfil.php" class="nav-item active"> <i class="ph-bold ph-user-gear"></i> Mi Perfil </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">CONFIGURACIÓN DE CUENTA</h2>
        
        <?php if(!empty($mensaje)): ?>
            <div class="<?= $tipo_mensaje ?>">
                <?php if($tipo_mensaje == 'alert-success'): ?>
                    <i class="ph-bold ph-check-circle"></i>
                <?php else: ?>
                    <i class="ph-bold ph-warning-circle"></i>
                <?php endif; ?>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            
            <div class="profile-card-side">
                <div class="profile-avatar">
                    <i class="ph-fill ph-user"></i>
                </div>
                <h3 style="margin-bottom: 5px; color: white;"><?= htmlspecialchars($usuario['nombre']) ?></h3>
                <p style="color: #64748b; font-size: 0.9rem;">Cliente Registrado</p>
                <hr style="border: 0; border-top: 1px solid #334155; margin: 20px 0;">
                <div style="text-align: left; color: #94a3b8; font-size: 0.9rem; line-height: 2;">
                    <div><i class="ph-bold ph-user-circle"></i> <?= htmlspecialchars($usuario['usu_cliente']) ?></div>
                    <div><i class="ph-bold ph-calendar"></i> Miembro activo</div>
                </div>
            </div>

            <div class="profile-card-form">
                <h3 style="margin-bottom: 25px; color: #f1f5f9; display: flex; align-items: center; gap: 10px;">
                    <i class="ph-duotone ph-pencil-simple"></i> Editar Información
                </h3>

                <form method="POST">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label class="label">Nombre Completo</label>
                            <input type="text" name="nombre" class="input-dark" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="label">Usuario (No editable)</label>
                            <input type="text" class="input-dark" value="<?= htmlspecialchars($usuario['usu_cliente']) ?>" disabled>
                        </div>

                        <div class="form-group full-width">
                            <label class="label">Correo Electrónico</label>
                            <input type="email" name="correo" class="input-dark" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label class="label">Teléfono</label>
                            <input type="text" name="telefono" class="input-dark" value="<?= htmlspecialchars($usuario['telefono']) ?>">
                        </div>

                        <div class="form-group full-width" style="margin-top: 20px; border-top: 1px solid #334155; padding-top: 20px;">
                            <label class="label" style="color: #3b82f6; margin-bottom: 15px;">
                                <i class="ph-bold ph-lock-key"></i> Cambiar Contraseña (Opcional)
                            </label>
                            <div class="form-grid">
                                <div>
                                    <label class="label">Nueva Contraseña</label>
                                    <input type="password" name="clave_nueva" class="input-dark" placeholder="Dejar en blanco para no cambiar">
                                </div>
                                <div>
                                    <label class="label">Confirmar Contraseña</label>
                                    <input type="password" name="clave_confirmar" class="input-dark" placeholder="Repite la nueva contraseña">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn-save">
                            <i class="ph-bold ph-floppy-disk"></i> GUARDAR CAMBIOS
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</body>
</html>
<?php 
if(isset($db)) {
    $db->cerrarConexion();
}
?>
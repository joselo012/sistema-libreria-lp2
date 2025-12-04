<?php
session_start();
require_once 'conexion.php'; 

// Inicializar variables para rellenar el formulario si hay error
$nombre = '';
$usuario = ''; 
$telefono = '';
$clave = '';
$clave_confirmar = '';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $db = new Conexion();
    $conn = $db->getConexion(); 

    // Limpiar inputs
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $clave_confirmar = $_POST['clave_confirmar'] ?? '';

    $error_icon = "<i class='ph-bold ph-warning-circle'></i>";

    // Validaciones
    if (empty($nombre) || empty($usuario) || empty($clave) || empty($clave_confirmar)) {
        $mensaje = "<div class='alert-error'>$error_icon Todos los campos son obligatorios.</div>";
    } elseif ($clave !== $clave_confirmar) {
        $mensaje = "<div class='alert-error'>$error_icon Las contraseñas no coinciden.</div>";
    } else {
        // 1. Verificar si el usuario ya existe en la tabla 'cliente'
        // Usamos 'usu_cliente' que es el campo que valida tu login.php
        $sql_check = "SELECT id_cliente FROM cliente WHERE usu_cliente = ?";
        $stmt_check = $conn->prepare($sql_check);
        
        if ($stmt_check) {
            $stmt_check->bind_param("s", $usuario);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $mensaje = "<div class='alert-error'>$error_icon El nombre de usuario ya está en uso.</div>";
            } else {
                // 2. Insertar nuevo cliente
                // NOTA: Guardamos la contraseña SIN encriptar para que coincida con tu login.php
                // Columnas: nombre, usu_cliente, telefono, pass_cli
                $sql_insert = "INSERT INTO cliente (nombre, usu_cliente, telefono, pass_cli) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssss", $nombre, $usuario, $telefono, $clave);

                    if ($stmt_insert->execute()) {
                        // 3. Crear sesión automáticamente (Auto-Login)
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id_usuario'] = $conn->insert_id; // ID del nuevo usuario
                        $_SESSION['nombre_usuario'] = $nombre;
                        $_SESSION['rol'] = 'cliente';

                        // 4. Redirección con alerta JS
                        echo "<script>
                            alert('Usuario creado con éxito. Bienvenido.');
                            window.location.href = 'indexCliente.php';
                        </script>";
                        exit;
                    } else {
                        $mensaje = "<div class='alert-error'>$error_icon Error al registrar en BD: " . $conn->error . "</div>";
                    }
                    $stmt_insert->close();
                } else {
                     $mensaje = "<div class='alert-error'>$error_icon Error en la preparación de la consulta.</div>";
                }
            }
            $stmt_check->close();
        } else {
            $mensaje = "<div class='alert-error'>$error_icon Error de conexión con la base de datos.</div>";
        }
    }
    $db->cerrarConexion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | BIBLIOTECA UDH</title>
    
    <link rel="stylesheet" href="styles.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="login-body"> 
    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="brand">
                    <i class="ph-fill ph-user-plus"></i> NUEVA CUENTA
                </div>
                <p class="subtitle">Únete a la biblioteca digital</p>
            </div>

            <?= $mensaje ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                
                <div class="form-group">
                    <label class="label">Nombre Completo</label>
                    <div class="input-container">
                        <i class="ph-bold ph-identification-card input-icon"></i>
                        <input type="text" name="nombre" class="tech-input" required 
                               value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej: Juan Pérez">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Usuario (Para ingresar)</label>
                    <div class="input-container">
                        <i class="ph-bold ph-user input-icon"></i>
                        <input type="text" name="usuario" class="tech-input" required 
                               value="<?= htmlspecialchars($usuario) ?>" placeholder="Ej: juan123">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Teléfono (Opcional)</label>
                    <div class="input-container">
                        <i class="ph-bold ph-phone input-icon"></i>
                        <input type="text" name="telefono" class="tech-input" 
                               value="<?= htmlspecialchars($telefono) ?>" placeholder="Ej: 999-888-777">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Contraseña</label>
                    <div class="input-container">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="clave" class="tech-input" required placeholder="••••••••">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Confirmar Contraseña</label>
                    <div class="input-container">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="clave_confirmar" class="tech-input" required placeholder="••••••••">
                    </div>
                </div>

                <button class="btn-action btn-full">REGISTRARME</button>
                
                <div class="links">
                    <p class="register-text">¿Ya tienes una cuenta?</p>
                    <a href="login.php" class="back-link">
                        <i class="ph-bold ph-sign-in"></i> Iniciar Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
require_once 'conexion.php'; 

session_start(); 

$error = '';

function autenticar_usuario($conn, $identificador, $password_input, $tabla, $campo_identificador, $campo_id, $campo_nombre, $campo_pass, $rol, $redirect) {
    
    $sql = "SELECT $campo_id, $campo_nombre, $campo_pass FROM $tabla WHERE $campo_identificador = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        return ['success' => false, 'error' => "Error BD: " . $conn->error];
    }
    
    $stmt->bind_param("s", $identificador);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $hash_almacenado = $fila[$campo_pass];

        
        $login_exitoso = false;

        if ($password_input === $hash_almacenado) {
             $login_exitoso = true;
        }
      
        if ($login_exitoso) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id_usuario'] = $fila[$campo_id];
            $_SESSION['nombre_usuario'] = $fila[$campo_nombre];
            $_SESSION['rol'] = $rol;

            return ['success' => true, 'redirect' => $redirect];
        }
    }
    
    $stmt->close();
    return ['success' => false];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $db = new Conexion();
    $conn = $db->getConexion();

    // Capturamos los inputs con los 'name' del NUEVO HTML (usuario y clave)
    $identificador = $_POST['usuario'] ?? '';
    $password_input = $_POST['clave'] ?? '';

    if (!empty($identificador) && !empty($password_input)) {
        
        // 1. Intentar loguear como Administrador
        // Nota: Asegúrate que los nombres de tablas y columnas coincidan con tu BD
        $login_result = autenticar_usuario(
            $conn, $identificador, $password_input, 
            'administrador', 'usuario', 'id_admin', 'nombre', 'pass_admin', 
            'administrador', 'indexAdmin.php'
        );

        if (!$login_result['success']) {
            $login_result = autenticar_usuario(
                $conn, $identificador, $password_input, 
                'cliente', 'usu_cliente', 'id_cliente', 'nombre', 'pass_cli', 
                'cliente', 'indexCliente.php'
            );
        }

        if (isset($login_result['success']) && $login_result['success']) {
            header("location: " . $login_result['redirect']); 
            exit;
        } else {
            $error = "Tu suario o contraseña son incorrectas.";
        }

    } else {
        $error = "Por favor, rellena todos los campos.";
    }

    $db->cerrarConexion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | BIBLIOTECA UDH</title>
    
    <link rel="stylesheet" href="styles.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="login-body"> 
    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="brand" >
                      📚 BIBLIOTECA UDH
                </div>
                <p class="subtitle">Ingresa tus credenciales</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert-error">
                    <i class="ph-bold ph-warning-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label class="label">Usuario / Correo</label>
                    <div class="input-container">
                        <i class="ph-bold ph-user input-icon"></i>
                        <input type="text" name="usuario" class="tech-input" placeholder="Ej: admin o usuario" 
                               value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Contraseña</label>
                    <div class="input-container">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="clave" class="tech-input" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-action btn-full">INICIAR SESIÓN</button>
            </form>

            <div class="links">
                <p class="register-text">
                    ¿No tienes cuenta? 
                    <a href="registro.php" class="register-link">Crear cuenta</a><br>
                </p>
                
                <a href="index.html" class="back-link">
                    <i class="ph-bold ph-arrow-left"></i> Volver al Catálogo<br>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
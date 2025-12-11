<?php
session_start();
require_once 'conexion.php'; 

// Inicializar variables
$nombre = '';
$correo = ''; // NUEVO CAMPO
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
    $correo = trim($_POST['correo'] ?? ''); // Capturamos el correo
    $usuario = trim($_POST['usuario'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $clave_confirmar = $_POST['clave_confirmar'] ?? '';

    $error_icon = "<i class='ph-bold ph-warning-circle'></i>";

    // Validaciones (Agregamos correo a la validación)
    if (empty($nombre) || empty($correo) || empty($usuario) || empty($clave) || empty($clave_confirmar)) {
        $mensaje = "<div class='alert-error'>$error_icon Todos los campos obligatorios (incluido el correo) deben llenarse.</div>";
    } elseif ($clave !== $clave_confirmar) {
        $mensaje = "<div class='alert-error'>$error_icon Las contraseñas no coinciden.</div>";
    } else {
        // 1. Verificar si el usuario ya existe
        $sql_check = "SELECT id_cliente FROM cliente WHERE usu_cliente = ?";
        $stmt_check = $conn->prepare($sql_check);
        
        if ($stmt_check) {
            $stmt_check->bind_param("s", $usuario);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $mensaje = "<div class='alert-error'>$error_icon El usuario '$usuario' ya está registrado.</div>";
            } else {
                // 2. Insertar nuevo cliente (AHORA INCLUIMOS 'correo')
                // Asegúrate de que las columnas coincidan con tu BD: nombre, correo, usu_cliente, telefono, pass_cli
                $sql_insert = "INSERT INTO cliente (nombre, correo, usu_cliente, telefono, pass_cli) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                
                if ($stmt_insert) {
                    // "sssss" = 5 strings (nombre, correo, usuario, telefono, clave)
                    $stmt_insert->bind_param("sssss", $nombre, $correo, $usuario, $telefono, $clave);

                    if ($stmt_insert->execute()) {
                        // 3. Auto-Login
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id_usuario'] = $conn->insert_id;
                        $_SESSION['nombre_usuario'] = $nombre;
                        $_SESSION['rol'] = 'cliente';

                        echo "<script>
                            alert('¡Cuenta creada con éxito! Bienvenido.');
                            window.location.href = 'cliente/indexCliente.php';
                        </script>";
                        exit;
                    } else {
                        $mensaje = "<div class='alert-error'>$error_icon Error BD: " . $conn->error . "</div>";
                    }
                    $stmt_insert->close();
                } else {
                     $mensaje = "<div class='alert-error'>$error_icon Error al preparar consulta.</div>";
                }
            }
            $stmt_check->close();
        } else {
            $mensaje = "<div class='alert-error'>$error_icon Error de conexión.</div>";
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
    <link rel="stylesheet" href="style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Estilos CSS integrados para asegurar visualización correcta */
        body.login-body {
            background-color: #0f172a;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #e2e8f0;
        }
        .login-wrapper { width: 100%; max-width: 450px; padding: 20px; }
        .login-card {
            background: #1e293b;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border: 1px solid #334155;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .brand { font-family: 'Oswald', sans-serif; font-size: 2rem; color: #3b82f6; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .subtitle { color: #94a3b8; margin-top: 5px; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 20px; }
        .label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #cbd5e1; }
        .input-container { position: relative; }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.2rem; }
        .tech-input {
            width: 100%;
            padding: 12px 12px 12px 45px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .tech-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
        
        .btn-action {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 10px;
            transition: opacity 0.2s;
        }
        .btn-action:hover { opacity: 0.9; }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .links { text-align: center; margin-top: 25px; border-top: 1px solid #334155; padding-top: 20px; }
        .register-text { color: #94a3b8; font-size: 0.9rem; margin-bottom: 10px; }
        .back-link { color: #3b82f6; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    </style>
</head>
<body class="login-body"> 
    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="brand">
                    <i class="ph-fill ph-user-plus"></i> NUEVA CUENTA
                </div>
                <p class="subtitle">Únete a la Biblioteca Digital UDH</p>
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
                    <label class="label">Correo Electrónico</label>
                    <div class="input-container">
                        <i class="ph-bold ph-envelope input-icon"></i>
                        <input type="email" name="correo" class="tech-input" required 
                               value="<?= htmlspecialchars($correo) ?>" placeholder="Ej: juan@udh.edu.pe">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Usuario</label>
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
                               value="<?= htmlspecialchars($telefono) ?>" placeholder="Ej: 999 888 777">
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
                        <i class="ph-bold ph-sign-in"></i> Volver al Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
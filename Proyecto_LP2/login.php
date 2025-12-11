<?php
require_once 'conexion.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['rol']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: admin/indexAdmin.php");
    } else {
        header("Location: cliente/indexCliente.php");
    }
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $db = new Conexion();
    $conn = $db->getConexion();

    $identificador = trim($_POST['usuario'] ?? ''); 
    $password_input = trim($_POST['clave'] ?? '');

    if (!empty($identificador) && !empty($password_input)) {
        
        // --- BLOQUE ADMIN ---
        $sqlAdmin = "SELECT id_admin, nombre FROM administrador WHERE usuario = ? AND pass_admin = ?";
        $stmt = $conn->prepare($sqlAdmin);
        $stmt->bind_param("ss", $identificador, $password_input);
        $stmt->execute();
        $resAdmin = $stmt->get_result();

        if ($row = $resAdmin->fetch_assoc()) {
            // Para el admin mantenemos 'id' y 'nombre' si tu indexAdmin funciona bien así
            $_SESSION['id'] = $row['id_admin']; 
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['rol'] = 'admin';
            $_SESSION['loggedin'] = true; 
            
            header("Location: admin/indexAdmin.php"); 
            exit;
        }

        // --- BLOQUE CLIENTE (AQUÍ ESTABA EL ERROR) ---
        $sqlCliente = "SELECT id_cliente, nombre FROM cliente WHERE usu_cliente = ? AND pass_cli = ?";
        $stmt2 = $conn->prepare($sqlCliente);
        $stmt2->bind_param("ss", $identificador, $password_input); 
        $stmt2->execute();
        $resCliente = $stmt2->get_result();

        if ($row = $resCliente->fetch_assoc()) {
            $_SESSION['id_usuario'] = $row['id_cliente'];
            $_SESSION['nombre_usuario'] = $row['nombre']; 
            $_SESSION['rol'] = 'cliente';
            $_SESSION['loggedin'] = true; 

            header("Location: cliente/indexCliente.php"); 
            exit;
        }

        $error = "Usuario o contraseña incorrectos.";

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
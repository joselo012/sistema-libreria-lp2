<?php
require_once 'conexion.php'; 

session_start(); 

$mensaje_error = '';

function autenticar_usuario($conn, $identificador, $password_input, $tabla, $campo_identificador, $campo_id, $campo_nombre, $campo_pass, $rol, $redirect) {
    
    $sql = "SELECT $campo_id, $campo_nombre, $campo_pass FROM $tabla WHERE $campo_identificador = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        return ['success' => false, 'error' => "Error al preparar la consulta: " . $conn->error];
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
    
    if ($stmt) {
        $stmt->close();
    }
    return ['success' => false];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $db = new Conexion();
    $conn = $db->getConexion();

    $identificador = $_POST['identificador'] ?? '';
    $password_input = $_POST['password'] ?? '';

    if (!empty($identificador) && !empty($password_input)) {
        
        $login_result = null;

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
            $mensaje_error = "Identificador o Contraseña incorrecta. Por favor, revise sus datos.";
        }

    } else {
        $mensaje_error = "Por favor, completa todos los campos.";
    }

    $db->cerrarConexion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema - Librería A</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 320px; }
        h2 { text-align: center; color: #333; margin-bottom: 25px;}
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { width: 100%; background-color: #3498db; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; }
        input[type="submit"]:hover { background-color: #2980b9; }
        .error { color: #c0392b; background-color: #fbe6e6; border: 1px solid #c0392b; padding: 10px; text-align: center; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Iniciar Sesión - Librería A</h2>
    
    <?php 
    if (!empty($mensaje_error)) {
        echo '<p class="error">' . $mensaje_error . '</p>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <div>
            <label for="identificador">Usuario:</label>
            <input type="text" id="identificador" name="identificador" required value="<?php echo htmlspecialchars($_POST['identificador'] ?? ''); ?>">
        </div>
        
        <div>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div>
            <input type="submit" value="Acceder">
        </div>
    </form>
</div>

</body>
</html>
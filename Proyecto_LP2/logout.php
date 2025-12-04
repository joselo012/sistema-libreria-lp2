<?php
// 1. Iniciar la sesión para poder manipularla
session_start();

// 2. Destruir todas las variables de sesión (limpiar el array $_SESSION)
$_SESSION = [];

// 3. Borrar la cookie de sesión si existe (para limpieza total)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Redirigir al usuario al Login
header("Location: login.php");
exit;
?>
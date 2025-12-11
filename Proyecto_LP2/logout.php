<?php
session_start();
session_destroy(); // Destruye todo rastro de la sesión vieja
header("Location: login.php"); // Te manda al login limpio
exit;
?>
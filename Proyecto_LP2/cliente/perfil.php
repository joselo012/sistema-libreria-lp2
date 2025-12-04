<?php
session_start();
require_once '../conexion.php';
/*
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}*/

$db = new Conexion();
$conn = $db->iniciar();
$usuario = null;

try {
    if($conn){
        $stmt = $conn->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Nexus Member</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> NEXUS <span class="highlight">MEMBER</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexCliente.php" class="nav-item">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            <a href="catalogo.php" class="nav-item">
                <i class="ph-bold ph-books"></i> Catálogo
            </a>
            <a href="misLibros.php" class="nav-item">
                <i class="ph-bold ph-clock-counter-clockwise"></i> Mis Libros
            </a>
            <a href="perfil.php" class="nav-item active"> <i class="ph-bold ph-user-gear"></i> Mi Perfil
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">INFORMACIÓN DE CUENTA</h2>

        <div class="admin-card" style="max-width: 600px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <i class="ph-duotone ph-user-circle" style="font-size: 5rem; color: var(--accent);"></i>
                <h3 style="margin-top: 10px;"><?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?></h3>
                <p style="color: var(--text-muted);">Cliente Registrado</p>
            </div>

            <div class="form-group">
                <label class="label">Correo Electrónico</label>
                <div class="tech-input" style="background: rgba(255,255,255,0.05); border:none;">
                    <?= htmlspecialchars($usuario['correo'] ?? '') ?>
                </div>
            </div>

            <div class="form-group">
                <label class="label">Teléfono de Contacto</label>
                <div class="tech-input" style="background: rgba(255,255,255,0.05); border:none;">
                    <?= htmlspecialchars($usuario['telefono'] ?? 'No registrado') ?>
                </div>
            </div>

            <div class="form-group">
                <label class="label">Fecha de Registro</label>
                <div class="tech-input" style="background: rgba(255,255,255,0.05); border:none;">
                    <?= date('d/m/Y H:i', strtotime($usuario['fecha_registro'] ?? 'now')) ?>
                </div>
            </div>
            
            <div class="alert-success" style="margin-top: 20px; text-align: center;">
                <i class="ph-bold ph-shield-check"></i> Tu cuenta está activa y segura.
            </div>
        </div>
    </main>
</body>
</html>
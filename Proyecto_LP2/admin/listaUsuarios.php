<?php
session_start();
require_once '../conexion.php';

// --- SEGURIDAD ESTRICTA ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$lista_users = [];
$conexion_activa = false;
$db = new Conexion();

try {
    $conn = $db->iniciar();
    if ($conn) {
        $conexion_activa = true;
        $lista_users = $conn->query("SELECT * FROM cliente ORDER BY id_cliente DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios | Nexus Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="ph-fill ph-hexagon"></i> INDEX <span class="highlight">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="indexAdmin.php" class="nav-item">
                <i class="ph-bold ph-squares-four"></i> Inicio
            </a>
            <a href="listaUsuarios.php" class="nav-item active">
                <i class="ph-bold ph-users"></i> Usuarios
            </a>
            <a href="Libros.php" class="nav-item">
                <i class="ph-bold ph-books"></i> Libros
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="nav-item small" style="color: <?= $conexion_activa ? '#10b981' : '#ef4444' ?>">
                <i class="ph-fill ph-circle"></i> <?= $conexion_activa ? 'Online' : 'Offline' ?>
            </div>
            <a href="../logout.php" class="nav-item logout"><i class="ph-bold ph-sign-out"></i> Salir</a>
        </div>
    </aside>

    <main class="admin-content">
        <h2 class="section-title">BASE DE DATOS DE CLIENTES</h2>
        <div class="admin-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($lista_users)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:20px;">Sin datos disponibles.</td></tr>
                    <?php else: ?>
                        <?php foreach($lista_users as $u): ?>
                        <tr>
                            <td>#<?= $u['id_cliente'] ?></td>
                            <td class="highlight"><?= $u['nombre'] ?></td>
                            <td><?= $u['correo'] ?></td>
                            <td><?= $u['telefono'] ?></td>
                            <td class="muted"><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
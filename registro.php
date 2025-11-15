<?php
require_once 'conexion.php';
require_once 'cliente.php';

$ok  = null;
$err = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST["nombre"] ?? "");
    $correo   = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $clave    = $_POST["clave"] ?? "";

    if ($nombre === "") {
        $err["nombre"] = "requerido";
    }

    if ($correo === "") {
        $err["correo"] = "requerido";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $err["correo"] = "correo no válido";
    }

    if ($telefono !== "" && !preg_match('/^[0-9+\s-]{6,20}$/', $telefono)) {
        $err["telefono"] = "teléfono no válido";
    }

    if (strlen($clave) < 8) {
        $err["clave"] = "mínimo 8 caracteres";
    }

    if (empty($err)) {
        try {
            $nuevo_cliente = new cliente($nombre, $correo, $telefono, $clave);
            $nuevo_cliente->guardar($pdo);

            $ok = "cliente registrado correctamente";

            $_POST["nombre"]   = "";
            $_POST["correo"]   = "";
            $_POST["telefono"] = "";

        } catch (exception $e) {
            $err["general"] = "error al registrar: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>registro de cliente</title>
</head>
<body>
  <h1>registro de cliente</h1>

  <?php if ($ok): ?>
    <p style="color:green;"><?php echo htmlspecialchars($ok); ?></p>
  <?php endif; ?>

  <?php if (!empty($err["general"])): ?>
    <p style="color:red;"><?php echo htmlspecialchars($err["general"]); ?></p>
  <?php endif; ?>

  <form method="post">

    <label>nombre completo<br>
      <input
        name="nombre"
        required
        value="<?php echo htmlspecialchars($_POST["nombre"] ?? ""); ?>"
      >
      <?php if (!empty($err["nombre"])): ?>
        <br><small style="color:red;"><?php echo $err["nombre"]; ?></small>
      <?php endif; ?>
    </label>
    <br><br>

    <label>correo electrónico<br>
      <input
        name="correo"
        type="email"
        required
        value="<?php echo htmlspecialchars($_POST["correo"] ?? ""); ?>"
      >
      <?php if (!empty($err["correo"])): ?>
        <br><small style="color:red;"><?php echo $err["correo"]; ?></small>
      <?php endif; ?>
    </label>
    <br><br>

    <label>teléfono (opcional)<br>
      <input
        name="telefono"
        value="<?php echo htmlspecialchars($_POST["telefono"] ?? ""); ?>"
      >
      <?php if (!empty($err["telefono"])): ?>
        <br><small style="color:red;"><?php echo $err["telefono"]; ?></small>
      <?php endif; ?>
    </label>
    <br><br>

    <label>contraseña<br>
      <input name="clave" type="password" required>
      <?php if (!empty($err["clave"])): ?>
        <br><small style="color:red;"><?php echo $err["clave"]; ?></small>
      <?php endif; ?>
    </label>
    <br><br>

    <button type="submit">registrar cliente</button>
  </form>
</body>
</html>

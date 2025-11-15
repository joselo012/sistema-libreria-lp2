<?php
class cliente {

    private $id_cliente;
    private $nombre;
    private $correo;
    private $telefono;
    private $password_hash;

    public function __construct($nombre, $correo, $telefono, $password_plano) {
        $this->nombre        = $nombre;
        $this->correo        = $correo;
        $this->telefono      = $telefono;
        $this->password_hash = password_hash($password_plano, PASSWORD_DEFAULT);
    }

    public function guardar($pdo) {
        $sql = "insert into cliente (nombre, correo, telefono, password_hash)
                values (:nombre, :correo, :telefono, :password_hash)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'        => $this->nombre,
            ':correo'        => $this->correo,
            ':telefono'      => $this->telefono,
            ':password_hash' => $this->password_hash
        ]);

        // guardar el id generado por la bd
        $this->id_cliente = $pdo->lastInsertId();
    }

    public function get_id_cliente() {
        return $this->id_cliente;
    }

    public function get_nombre() {
        return $this->nombre;
    }

    public function get_correo() {
        return $this->correo;
    }

    public function get_telefono() {
        return $this->telefono;
    }
}

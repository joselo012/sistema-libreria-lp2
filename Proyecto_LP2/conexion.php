<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $password = "";
    private $nombre_bd = "bdlibreria";
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->usuario, $this->password, $this->nombre_bd);

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
    }

    public function getConexion() {
        return $this->conn;
    }

    public function cerrarConexion() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
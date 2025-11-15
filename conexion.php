<?php

class Conexion {
    private $dsn;
    private $username;
    private $password;
    private $options;
    private $conexion;

    public function __construct(
        string $host = "localhost",
        string $dbname = "BDlibreria",
        string $username = "root",
        string $password = ""
    ) {
        $this->dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $this->username = $username;
        $this->password = $password;
    }

    public function iniciar(): ?PDO {
        try {
            if ($this->conexion === null) {
                $this->conexion = new PDO(
                    $this->dsn,
                    $this->username,
                    $this->password,
                    $this->options
                );
            }
            return $this->conexion;
        } catch (PDOException $e) {
            throw new Exception("Error en la conexión a la base de datos: " . $e->getMessage());
        }
    }

    public function terminar(): void {
        $this->conexion = null;
    }
}

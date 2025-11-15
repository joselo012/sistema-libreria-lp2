<?php
// require_once 'Conexion.php';

class Administrador {
    private $pdo;

    public function __construct() {
        try {
            $db = new Conexion();
            $this->pdo = $db->iniciar();
        } catch (Exception $e) {
            die("Error al inicializar la conexión en Administrador: " . $e->getMessage());
        }
    }

    // FUNCIÓN 1: Autenticar al administrador
    public function autenticarAdmin($usuario, $password) {
        $query = "select id_admin, nombre, password_hash from administrador where usuario = :usuario";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':usuario' => $usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && password_verify($password, $row['password_hash'])) {
                return $row; 
            }
            return false;
        } catch (PDOException $e) {
            // echo "Error en autenticación de administrador: " . $e->getMessage();
            return false;
        }
    }

    // FUNCIÓN 2: Generar un reporte simple de ventas (pedidos entregados)
    public function generarReporteVentas() {

        $query = "SELECT p.fecha_pedido, c.nombre AS cliente, p.total 
                  FROM pedido p
                  join cliente c on p.id_cliente = c.id_cliente
                  where p.estado = 'entregado'
                  order by p.fecha_pedido desc";

        try {
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // echo "Error al generar reporte: " . $e->getMessage();
            return [];
        }
    }
}
?>
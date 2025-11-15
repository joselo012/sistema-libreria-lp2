<?php
class Pedido{
    private $pdo;

    public function __construct(){
        try{
            $db = new Conexion();
            $this->pdo = $db->iniciar();
        }
        catch (Exception $e){
            die("ERROR al inicializar la conexion en pedido: " . $e->getMessage());
        }
    }

    public function registrarPedido($id_cliente, $total, $estado = 'pendiente'){

        $query = "INSERT INTO pedido (id_cliente, fecha_pedido, total, estado) values (:id_cliente, NOW(), :total, :estado)";

        try{
            $stmt = $this->pdo->prepare($query);
            if ($stmt->execute([
                ':id_cliente' => $id_cliente,
                ':total' => $total,
                ':estado' => $estado
            ])) {
                return $this->pdo->lastInsertId();
            }
            return false;
        }

        catch (PDOException $e){
            echo "ERROR al registrar pedido: " . $e->getMessage();
            return false;
        }

    }

    public function actualizarEstado($id_pedido, $nuevo_estado){
        $query = "UPDATE pedido SET estado = :nuevo_estado WHERE id_pedido = :id_pedido";

        try {
            $stmt = $ this->pdo->prepare($query);
            return $stmt->execute([
                ':nuevo_estado' => $nuevo_estado,
                ':id_pedido' => $id_pedido
            ]);
        }
        catch (PDOException $e){
            echo "ERROR al actualizar estado: " . $e->getMessage();
            return false;
        }
    }
}
?>
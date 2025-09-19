<?php
class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT o.*, c.name as customer_name 
                  FROM " . $this->table . " o 
                  LEFT JOIN customer c ON o.customer_number = c.customer_number 
                  ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT o.*, c.name as customer_name, c.email as customer_email
                  FROM " . $this->table . " o 
                  LEFT JOIN customer c ON o.customer_number = c.customer_number 
                  WHERE o.order_number = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($customer_number, $required_date, $order_items) {
        $isOuterTransaction = false;

        try {
            // Verificar si ya hay una transacción activa
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
                $isOuterTransaction = true; // Marcamos que esta función inició la transacción
            }

            // Crear orden
            $query = "INSERT INTO " . $this->table . " (order_date, customer_number, required) 
                  VALUES (CURDATE(), :customer_number, :required)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_number', $customer_number, PDO::PARAM_INT);
            $stmt->bindParam(':required', $required_date);
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // Crear detalles de orden y actualizar stock
            loadClass('OrderDetail');
            loadClass('Product');

            $orderDetail = new OrderDetail($this->conn);
            $product = new Product($this->conn);

            foreach ($order_items as $item) {
                // Actualizar stock
                $product->updateStock($item['product_code'], $item['quantity']);

                // Crear detalle
                $orderDetail->create($order_id, $item);
            }

            // Solo hacer commit si esta función inició la transacción
            if ($isOuterTransaction) {
                $this->conn->commit();
            }

            return $order_id;

        } catch (Exception $e) {
            if ($isOuterTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new Exception("Error al crear el pedido: " . $e->getMessage());
        }
    }

}
?>
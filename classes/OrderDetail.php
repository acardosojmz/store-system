<?php
class OrderDetail {
    private $conn;
    private $table = 'orderdetail';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByOrderId($order_id) {
        $query = "SELECT od.*, p.product, p.description 
                  FROM " . $this->table . " od
                  LEFT JOIN product p ON od.product_code = p.product_code
                  WHERE od.order_number = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($order_id, $item) {
        $query = "INSERT INTO " . $this->table . " (order_number, product_code, quantity, unit_price) 
              VALUES (:order_id, :product_code, :quantity, :unit_price)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_code', $item['product_code'], PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(':unit_price', $item['unit_price']);
        return $stmt->execute();
    }

}
?>

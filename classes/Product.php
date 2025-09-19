<?php
class Product {
    private $conn;
    private $table = 'product';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY product";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE product_code = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getStock($product_code) {
        $query = "SELECT stock, product, unit_price FROM " . $this->table . " WHERE product_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $product_code, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateStock($product_code, $quantity) {
        $query = "UPDATE product SET stock = stock - :quantity WHERE product_code = :product_code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':product_code', $product_code, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (product, description, stock, unit_price) 
                  VALUES (:product, :description, :stock, :unit_price)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product', $data['product']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':stock', $data['stock'], PDO::PARAM_INT);
        $stmt->bindParam(':unit_price', $data['unit_price']);
        return $stmt->execute();
    }
}
?>

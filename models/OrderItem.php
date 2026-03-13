<?php
class OrderItem {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function create($orderId, $productId, $qty, $price) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price)
             VALUES (:order_id, :product_id, :quantity, :price)"
        );
        $stmt->execute([
            ':order_id'   => $orderId,
            ':product_id' => $productId,
            ':quantity'   => $qty,
            ':price'      => $price,
        ]);
    }

    public function getByOrder($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name AS product_name, p.image AS product_image
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    public function deleteByOrder($orderId) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM order_items WHERE order_id = ?"
        );
        $stmt->execute([$orderId]);
    }
}
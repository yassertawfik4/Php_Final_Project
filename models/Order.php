<?php


class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function create($userId, $room, $notes, $total) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (user_id, room, notes, total_price, status)
             VALUES (:user_id, :room, :notes, :total_price, 'processing')"
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':room'        => $room,
            ':notes'       => $notes,
            ':total_price' => $total,
        ]);
        return $this->pdo->lastInsertId();
    }
    public function getAll($status = 'processing') {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.name AS user_name, u.room AS user_room, u.ext
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.status = :status
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function getByUser($userId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($dateFrom && $dateTo) {
            $sql .= " AND DATE(created_at) BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $dateFrom;
            $params[':date_to']   = $dateTo;
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function getLatestByUser($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orders
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $id]);
    }
    public function cancel($id, $userId) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM orders
             WHERE id = ? AND user_id = ? AND status = 'processing'"
        );
        $stmt->execute([$id, $userId]);
    }
    public function getAllWithUsers($dateFrom, $dateTo, $userId = null) {
        $sql = "
            SELECT
                u.id,
                u.name,
                COUNT(o.id)         AS orders_count,
                SUM(o.total_price)  AS total
            FROM users u
            JOIN orders o ON o.user_id = u.id
            WHERE o.status = 'done'
            AND DATE(o.created_at) BETWEEN :date_from AND :date_to
        ";
        $params = [
            ':date_from' => $dateFrom,
            ':date_to'   => $dateTo,
        ];
        if ($userId) {
            $sql .= " AND u.id = :user_id";
            $params[':user_id'] = $userId;
        }
        $sql .= " GROUP BY u.id, u.name ORDER BY total DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDashboardStats() {
        $sql = "
            SELECT
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
                SUM(CASE WHEN status = 'out_for_delivery' THEN 1 ELSE 0 END) AS out_for_delivery,
                SUM(CASE WHEN status = 'done' AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS done_today,
                SUM(CASE WHEN status = 'done' AND DATE(created_at) = CURDATE() THEN total_price ELSE 0 END) AS today_revenue
            FROM orders
        ";

        $stmt = $this->pdo->query($sql);
        $stats = $stmt->fetch() ?: [];

        return [
            'processing' => (int)($stats['processing'] ?? 0),
            'out_for_delivery' => (int)($stats['out_for_delivery'] ?? 0),
            'done_today' => (int)($stats['done_today'] ?? 0),
            'today_revenue' => (float)($stats['today_revenue'] ?? 0),
        ];
    }

    public function getCurrentOrders() {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.name AS user_name, u.ext
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.status IN ('processing', 'out_for_delivery')
             ORDER BY o.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserAndDate($userId, $dateFrom, $dateTo) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM orders
             WHERE user_id = ?
             AND status = 'done'
             AND DATE(created_at) BETWEEN ? AND ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$userId, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
}

 <?php


class Product {

    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllAvailable() {
        $stmt = $this->pdo->query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_available = 1
             ORDER BY c.name, p.name"
        );
        return $stmt->fetchAll();
    }

  public function getAll($limit = 10, $offset = 0) {
    $stmt = $this->pdo->prepare(
        "SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         ORDER BY p.id DESC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll();
}
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }
    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, price, category_id, image, is_available)
             VALUES (:name, :price, :category_id, :image, :is_available)"
        );
        $stmt->execute([
            ':name'         => $data['name'],
            ':price'        => $data['price'],
            ':category_id'  => $data['category_id'],
            ':image'        => $data['image']        ?? null, 
            ':is_available' => $data['is_available'] ?? 1,   
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $fields = "name=:name, price=:price, category_id=:category_id, is_available=:is_available";
        $params = [
            ':name'         => $data['name'],
            ':price'        => $data['price'],
            ':category_id'  => $data['category_id'],
            ':is_available' => $data['is_available'] ?? 1,
            ':id'           => $id,
        ];
        if (!empty($data['image'])) {
            $fields .= ", image=:image";
            $params[':image'] = $data['image'];
        }
        $stmt = $this->pdo->prepare("UPDATE products SET $fields WHERE id = :id");
        $stmt->execute($params);
    }

    public function toggleAvailability($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET is_available = IF(is_available=1, 0, 1) WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

public function delete($id) {
    $stmt = $this->pdo->prepare("DELETE FROM order_items WHERE product_id = ?");
    $stmt->execute([$id]);

    $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}
}
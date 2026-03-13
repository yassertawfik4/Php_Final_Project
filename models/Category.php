 <?php
// ============================================================
//  models/Category.php
// ============================================================

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->pdo->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    }
}
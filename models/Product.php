 <?php
// ============================================================
//  models/Product.php
//  الموديل ده هو الوحيد المسموح له يكلم الـ Database
//  أي ملف تاني عايز بيانات → بيكلم الموديل ده بس
// ============================================================

class Product {

    // $pdo ده الـ connection بتاع الـ Database
    // private = محدش يقدر يوصله من برا الكلاس
    private $pdo;

    // Constructor: بيتنفذ أوتوماتيك لما نعمل new Product($pdo)
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ============================================================
    // getAllAvailable()
    // بتجيب المنتجات المتاحة بس
    // بتتستخدم في صفحة الطلب للـ User
    // ============================================================
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

    // ============================================================
    // getAll($limit, $offset)
    // بتجيب كل المنتجات مع Pagination - بتتستخدم في صفحة الأدمن
    // $limit  = كام منتج في كل صفحة (افتراضي 10)
    // $offset = من أنهي record نبدأ (صفحة 1 = 0, صفحة 2 = 10)
    // ============================================================
  public function getAll($limit = 10, $offset = 0) {
    $stmt = $this->pdo->prepare(
        "SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         ORDER BY p.id DESC
         LIMIT :limit OFFSET :offset"
    );

    // bindValue مع نوع INT
    $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll();
}
    // ============================================================
    // countAll()
    // بتعد كام منتج - محتاجينها عشان نحسب عدد صفحات الـ Pagination
    // مثال: 25 منتج / 10 لكل صفحة = ceil(2.5) = 3 صفحات
    // ============================================================
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }

    // ============================================================
    // findById($id)
    // بتجيب منتج واحد بالـ ID - بتتستخدم في صفحة التعديل
    // ============================================================
    public function findById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        // fetch() ترجع row واحدة - مش array من rows
        return $stmt->fetch();
    }

    // ============================================================
    // create($data)
    // بتضيف منتج جديد في الـ Database
    // $data = array: ['name'=>'Tea', 'price'=>5, 'category_id'=>1]
    // ============================================================
    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, price, category_id, image, is_available)
             VALUES (:name, :price, :category_id, :image, :is_available)"
        );
        $stmt->execute([
            ':name'         => $data['name'],
            ':price'        => $data['price'],
            ':category_id'  => $data['category_id'],
            ':image'        => $data['image']        ?? null, // لو مفيش صورة = null
            ':is_available' => $data['is_available'] ?? 1,    // افتراضي: متاح
        ]);
        // lastInsertId() = الـ ID اللي اتعمل للـ row الجديدة
        return $this->pdo->lastInsertId();
    }

    // ============================================================
    // update($id, $data)
    // بتعدل منتج موجود
    // لو مفيش صورة جديدة - مبنعدلش الصورة القديمة
    // ============================================================
    public function update($id, $data) {
        // بنبني الـ SQL ديناميكي عشان الصورة اختيارية
        $fields = "name=:name, price=:price, category_id=:category_id, is_available=:is_available";
        $params = [
            ':name'         => $data['name'],
            ':price'        => $data['price'],
            ':category_id'  => $data['category_id'],
            ':is_available' => $data['is_available'] ?? 1,
            ':id'           => $id,
        ];
        // لو في صورة جديدة - نضيفها للـ SQL
        if (!empty($data['image'])) {
            $fields .= ", image=:image";
            $params[':image'] = $data['image'];
        }
        $stmt = $this->pdo->prepare("UPDATE products SET $fields WHERE id = :id");
        $stmt->execute($params);
    }

    // ============================================================
    // toggleAvailability($id)
    // بتقلب الـ availability:
    // لو 1 (متاح)    → يبقى 0 (مش متاح)
    // لو 0 (مش متاح) → يبقى 1 (متاح)
    // IF(is_available=1, 0, 1) ده زي if/else في SQL
    // ============================================================
    public function toggleAvailability($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE products SET is_available = IF(is_available=1, 0, 1) WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    // ============================================================
    // delete($id)
    // بتمسح منتج نهائياً - مفيش undo!
    // ============================================================
public function delete($id) {
    // أولاً امسح الـ order_items المرتبطة بالمنتج
    $stmt = $this->pdo->prepare("DELETE FROM order_items WHERE product_id = ?");
    $stmt->execute([$id]);

    // بعدين امسح المنتج نفسه
    $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}
}
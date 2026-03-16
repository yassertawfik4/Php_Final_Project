<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/auth_check.php';

class ProductController {

    private $productModel;
    private $categoryModel;

    public function __construct($pdo = null) {
        $pdo = $pdo ?: getDB();
        $this->productModel  = new Product($pdo);
        $this->categoryModel = new Category($pdo);
    }

    // ── All Products (Screen 5) ──
    public function products() {
        require_role('admin');
        $page     = max(1, (int)($_GET['p'] ?? 1));
        $limit    = 10;
        $offset   = ($page - 1) * $limit;
        $products = $this->productModel->getAll($limit, $offset);
        $total    = $this->productModel->countAll();
        $pages    = max(1, (int)ceil($total / $limit));
        require __DIR__ . '/../views/admin/products/index.php';
    }

    // ── Add/Edit Product Page ──
    public function addProductPage() {
        require_role('admin');
        $categories = $this->categoryModel->getAll();
        $product    = null;
        $id         = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $product = $this->productModel->findById($id);
            if (!$product) {
                setFlash('error', 'Product not found.');
                header('Location: ' . BASE_URL . '/?page=admin.products');
                exit;
            }
            require __DIR__ . '/../views/admin/products/edit.php';
            return;
        }
        require __DIR__ . '/../views/admin/products/add.php';
    }

    // ── Handle Add/Edit POST ──
    public function addProduct() {
        require_role('admin');
        $name        = trim($_POST['name']         ?? '');
        $price       = (float)($_POST['price']     ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $product_id  = (int)($_POST['product_id']  ?? 0);

        if (!$name || $price <= 0 || !$category_id) {
            setFlash('error', 'Please fill all required fields with valid values.');
            $redirect = $product_id
                ? BASE_URL . "?page=admin.product.add&id=$product_id"
                : BASE_URL . "?page=admin.product.add";
            header("Location: $redirect");
            exit;
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = $this->uploadFile($_FILES['image'], 'uploads/products/');
        }

        $data = [
            'name'         => $name,
            'price'        => $price,
            'category_id'  => $category_id,
            'is_available' => (int)($_POST['is_available'] ?? 1),
        ];
        if ($image) {
            $data['image'] = $image;
        }

        if ($product_id > 0) {
            $this->productModel->update($product_id, $data);
            setFlash('success', 'Product updated successfully.');
        } else {
            $this->productModel->create($data);
            setFlash('success', 'Product added successfully.');
        }

        header('Location: ' . BASE_URL . '/?page=admin.products');
        exit;
    }

    // ── Toggle Available/Unavailable ──
    public function toggleProduct() {
        require_role('admin');
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->toggleAvailability($id);
        }
        $page = $_GET['p'] ?? 1;
        header('Location: ' . BASE_URL . '/?page=admin.products&p=' . $page);
        exit;
    }

    // ── Delete Product ──
    public function deleteProduct() {
        require_role('admin');
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $product = $this->productModel->findById($id);
            if (!$product) {
                setFlash('error', 'Product not found.');
                header('Location: ' . BASE_URL . '/?page=admin.products');
                exit;
            }

            $pdo = getDB();
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM order_items WHERE product_id = ?'
            );
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                $this->productModel->toggleAvailability($id);
                setFlash('error', 'Product has orders — marked unavailable instead.');
            } else {
                $this->productModel->delete($id);
                setFlash('success', '"' . $product['name'] . '" deleted.');
            }
        }

        header('Location: ' . BASE_URL . '/?page=admin.products');
        exit;
    }

    // ── Add Category Page ──
    public function addCategoryPage() {
        require_role('admin');
        require __DIR__ . '/../views/admin/categories/add.php';
    }

    // ── Handle Add Category POST ──
    public function addCategory() {
        require_role('admin');
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $this->categoryModel->create($name);
            setFlash('success', 'Category "' . $name . '" added.');
        } else {
            setFlash('error', 'Category name is required.');
        }
        header('Location: ' . BASE_URL . '/?page=admin.category.add');
        exit;
    }

    // ── Helper: Upload File ──
    private function uploadFile($file, $folder) {
        $target = __DIR__ . '/../public/' . $folder;
        if (!is_dir($target)) mkdir($target, 0755, true);
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp','jfif'];
        if (!in_array($ext, $allowed, true)) {
            setFlash('error', 'Invalid image type. Allowed: jpg, png, webp, jfif.');
            return null;
        }
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $target . $filename);
        return $folder . $filename;
    }
}
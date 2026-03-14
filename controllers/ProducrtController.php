<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helper/session.php';

class ProductController {

    private $pdo;
    private $productModel;
    private $categoryModel;

    public function __construct($pdo) {
        $this->pdo           = $pdo;
        $this->productModel  = new Product($pdo);
        $this->categoryModel = new Category($pdo);
    }

    // ── All Products (Screen 5) ──
    public function products() {
        requireAdmin();
        $page     = max(1, (int)($_GET['p'] ?? 1));
        $limit    = 10;
        $offset   = ($page - 1) * $limit;
        $products = $this->productModel->getAll($limit, $offset);
        $total    = $this->productModel->countAll();
        $pages    = ceil($total / $limit);
        require __DIR__ . '/../views/admin/products.php';
    }

    // ── Add/Edit Product Page ──
    public function addProductPage() {
        requireAdmin();
        $categories = $this->categoryModel->getAll();
        $product    = null;
        $id         = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $product = $this->productModel->findById($id);
            if (!$product) {
                setFlash('error', 'Product not found.');
                header('Location: index.php?page=products');
                exit;
            }
        }
        require __DIR__ . '/../views/admin/addProduct.php';
    }

    // ── Handle Add/Edit POST ──
    public function addProduct() {
        requireAdmin();
        $name        = trim($_POST['name']         ?? '');
        $price       = (float)($_POST['price']     ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $product_id  = (int)($_POST['product_id']  ?? 0);

        if (!$name || !$price || !$category_id) {
            setFlash('error', 'Please fill all required fields.');
            $redirect = $product_id
                ? "index.php?page=add_product&id=$product_id"
                : "index.php?page=add_product";
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
        if ($image) $data['image'] = $image;

        if ($product_id > 0) {
            $this->productModel->update($product_id, $data);
            setFlash('success', 'Product updated successfully.');
        } else {
            $this->productModel->create($data);
            setFlash('success', 'Product added successfully.');
        }

        header('Location: index.php?page=products');
        exit;
    }

    // ── Toggle Available/Unavailable ──
    public function toggleProduct() {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) $this->productModel->toggleAvailability($id);
        $page = $_GET['p'] ?? 1;
        header("Location: index.php?page=products&p=$page");
        exit;
    }

    // ── Delete Product ──
    public function deleteProduct() {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $product = $this->productModel->findById($id);
            if (!$product) {
                setFlash('error', 'Product not found.');
                header('Location: index.php?page=products');
                exit;
            }

            // لو عنده orders → disable بس مش نمسح
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM order_items WHERE product_id = ?"
            );
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                $this->productModel->toggleAvailability($id);
                setFlash('error', 'Product has orders — marked as unavailable instead.');
            } else {
                $this->productModel->delete($id);
                setFlash('success', '"' . $product['name'] . '" deleted.');
            }
        }

        header('Location: index.php?page=products');
        exit;
    }

    // ── Add Category Page ──
    public function addCategoryPage() {
        requireAdmin();
        require __DIR__ . '/../views/admin/addCategory.php';
    }

    // ── Handle Add Category POST ──
    public function addCategory() {
        requireAdmin();
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $this->categoryModel->create($name);
            setFlash('success', 'Category "' . $name . '" added.');
        }
        header('Location: index.php?page=add_product');
        exit;
    }

    // ── Helper: Upload File ──
    private function uploadFile($file, $folder) {
        $target = __DIR__ . '/../public/' . $folder;
        if (!is_dir($target)) mkdir($target, 0755, true);
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $target . $filename);
        return $folder . $filename;
    }
}
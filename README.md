# Cafeteria Ordering Portal

Lightweight PHP/MySQL app for managing a cafeteria: users place orders, admins manage menu, users, and fulfillment. Designed to run on a plain PHP server (e.g., XAMPP) with minimal setup.

## Features
- Role-based auth (user/admin) with session handling and password reset.
- User flow: browse products, place/cancel orders, view order history and details.
- Admin flow: dashboard, manual orders, order status updates, checks view, user CRUD with image upload, product/category CRUD with availability toggle.
- Shared UI components (header/navbar/footer) and simple bootstrap styling.

## Tech Stack
- PHP 8+ (plain PHP, no framework) with PDO
- MySQL/MariaDB
- Bootstrap 5, Vanilla JS/CSS

## Project Structure
- `index.php` — entry point; boots routing and session.
- `routes/` — query-param based router (`?page=...`).
- `controllers/` — auth, orders, products, users, order-item status.
- `models/` — PDO data access for users, products, categories, orders, order items.
- `views/` — PHP templates for admin, user, and auth pages.
- `includes/` — layout pieces and auth guard.
- `public/` — assets (css/js) and uploaded images.
- `config/database.php` — DB connection settings.
- `cafeteria.sql` — schema dump to bootstrap the database.

## Prerequisites
- PHP 8+ with PDO MySQL enabled
- MySQL/MariaDB server
- Web server pointing to the project root (Apache via XAMPP works out of the box)

## Setup
1) Clone or copy the project into your web root (e.g., `htdocs/Php_Final_Project`).
2) Create a database named `cafeteria` (or any name you prefer).
3) Import the schema: `cafeteria.sql` via phpMyAdmin or `mysql -u root -p cafeteria < cafeteria.sql`.
4) Configure DB creds in `config/database.php` (`$host`, `$dbName`, `$userName`, `$password`).
5) If you serve from a different folder name, update `BASE_URL` in `index.php` to match your virtual host/path.
6) Start Apache/MySQL (e.g., from XAMPP), then open `http://localhost/Php_Final_Project/?page=login`.

## Creating an Admin User (first-time)
The app has no signup; seed an admin manually so you can access the dashboard:

1) Generate a bcrypt hash for your password (replace `admin123` as needed):
	- `php -r "echo password_hash('admin123', PASSWORD_BCRYPT), PHP_EOL;"`
2) Insert the user (adjust email/name/hash):
	```sql
	INSERT INTO users (name, email, password, room, ext, role) 
	VALUES ('Admin', 'admin@example.com', '$2y$...', '1', '100', 'admin');
	```

Log in with that email/password, then you can create more users from the admin area.

## Key Routes (query param `page`)
- Public/auth: `login`, `logout`, `forgot`, `reset_password`
- User: `home`, `orders`, `order.details`, `order.place` (POST), `order.cancel` (POST)
- Admin: `admin.dashboard`, `admin.checks`, `admin.manual_order`, `admin.update_order_status` (POST)
- Admin CRUD: `admin.users`, `admin.add_user`, `admin.create_user`, `admin.show_update_user`, `admin.update_user`, `admin.delete_user`, `admin.products`, `admin.product.add`, `admin.product.save`, `admin.product.toggle`, `admin.product.delete`, `admin.category.add`, `admin.category.save`

## Screens/Flows
- User: home (menu), order placement dialog, order history, order detail view.
- Admin: dashboard metrics, checks list, manual order builder, orders board with status update, CRUD for products/categories/users with image upload and availability toggle.

## Notes
- File uploads (user/product images) are stored under `public/uploads/`; ensure the directory is writable by the web server.
- Passwords use PHP `password_hash`/`password_verify` (bcrypt by default).
- Customize UI styles in `public/css/style.css` and scripts in `public/js/app.js`.
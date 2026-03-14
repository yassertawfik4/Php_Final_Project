 <?php
// ============================================================
//  models/User.php
// ============================================================

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Find user by email (used for login)
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Find user by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get all users (admin)
public function getAll($limit = 10, $offset = 0) {

    $stmt = $this->pdo->prepare(
<<<<<<< HEAD
        "SELECT id, name, email, room_no, ext, profile_picture, role, created_at
=======
        "SELECT id, name, email, room, ext, image, role, created_at
>>>>>>> 2778911c5efb493db20df0c91c596801f36711db
         FROM users 
         WHERE role = 'user'
         LIMIT :limit OFFSET :offset"
    );

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    // Count all users
    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    }

    // Create a new user (admin adds user)
    public function create($data) {
        $stmt = $this->pdo->prepare(
<<<<<<< HEAD
            "INSERT INTO users (name, email, password, room_no, ext, profile_picture, role)
             VALUES (:name, :email, :password, :room_no, :ext, :profile_picture, :role)"
=======
            "INSERT INTO users (name, email, password, room, ext, image, role)
             VALUES (:name, :email, :password, :room, :ext, :image, :role)"
>>>>>>> 2778911c5efb493db20df0c91c596801f36711db
        );
        $stmt->execute([
            ':name'            => $data['name'],
            ':email'           => $data['email'],
            ':password'        => password_hash($data['password'], PASSWORD_BCRYPT),
<<<<<<< HEAD
            ':room_no'         => $data['room_no']         ?? null,
            ':ext'             => $data['ext']             ?? null,
            ':profile_picture' => $data['profile_picture'] ?? null,
=======
            ':room'         => $data['room']         ?? null,
            ':ext'             => $data['ext']             ?? null,
            ':image' => $data['image'] ?? null,
>>>>>>> 2778911c5efb493db20df0c91c596801f36711db
            ':role'            => $data['role']            ?? 'user',
        ]);
        return $this->pdo->lastInsertId();
    }

    // Update user
    public function update($id, $data) {
<<<<<<< HEAD
        $fields = "name=:name, email=:email, room_no=:room_no, ext=:ext";
        $params = [
            ':name'    => $data['name'],
            ':email'   => $data['email'],
            ':room_no' => $data['room_no'] ?? null,
=======
        $fields = "name=:name, email=:email, room=:room, ext=:ext";
        $params = [
            ':name'    => $data['name'],
            ':email'   => $data['email'],
            ':room' => $data['room'] ?? null,
>>>>>>> 2778911c5efb493db20df0c91c596801f36711db
            ':ext'     => $data['ext']     ?? null,
            ':id'      => $id,
        ];

        if (!empty($data['password'])) {
            $fields .= ", password=:password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
<<<<<<< HEAD
        if (!empty($data['profile_picture'])) {
            $fields .= ", profile_picture=:profile_picture";
            $params[':profile_picture'] = $data['profile_picture'];
=======
        if (!empty($data['image'])) {
            $fields .= ", image=:image";
            $params[':image'] = $data['image'];
>>>>>>> 2778911c5efb493db20df0c91c596801f36711db
        }

        $stmt = $this->pdo->prepare("UPDATE users SET $fields WHERE id = :id");
        $stmt->execute($params);
    }

    // Delete user
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Store reset token for forgot password
    public function setResetToken($email, $token) {
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->pdo->prepare(
            "UPDATE users SET reset_token=?, reset_expires=? WHERE email=?"
        );
        $stmt->execute([$token, $expires, $email]);
    }

    // Find user by reset token
    public function findByResetToken($token) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE reset_token=? AND reset_expires > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // Clear reset token after use
    public function clearResetToken($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET reset_token=NULL, reset_expires=NULL WHERE id=?"
        );
        $stmt->execute([$id]);
    }

    // Get all users as dropdown list (for admin manual order / checks)
    public function getDropdownList() {
        $stmt = $this->pdo->query(
            "SELECT id, name FROM users WHERE role='user' ORDER BY name"
        );
        return $stmt->fetchAll();
    }

    public function deleteById($id) {
    $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}
}
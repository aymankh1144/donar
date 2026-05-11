<?php
// ═══════════════════════════════════════════════════════════════
// Authentication & Authorization
// ═══════════════════════════════════════════════════════════════

require_once __DIR__ . '/database.php';

class Auth {
    private static $user = null;
    private static $store = null;

    public static function init() {
        session_start();
        if (!empty($_SESSION['user_id'])) {
            self::loadUser($_SESSION['user_id']);
        }
    }

    public static function login(string $username, string $password): array {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'بيانات الدخول غير صحيحة'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'الحساب غير نشط'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['store_id'] = $user['store_id'];
        $_SESSION['role'] = $user['role'];

        $db->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?')->execute([$user['id']]);

        self::loadUser($user['id']);
        return ['success' => true, 'user' => self::$user];
    }

    public static function logout() {
        session_destroy();
        self::$user = null;
        self::$store = null;
    }

    public static function register(string $email, string $phone, string $username, string $password, string $store_name_ar, string $store_name_en, string $store_slug): array {
        $db = getDB();

        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'البريد الإلكتروني غير صحيح'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'];
        }

        // Check if email or username exists
        $check = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'البريد الإلكتروني أو اسم المستخدم مستخدم بالفعل'];
        }

        try {
            // Create store
            $storeStmt = $db->prepare("
                INSERT INTO stores (store_slug, store_name_ar, store_name_en, owner_id, subscription_plan)
                VALUES (?, ?, ?, 0, 'free')
            ");
            $storeStmt->execute([$store_slug, $store_name_ar, $store_name_en]);
            $storeId = $db->lastInsertId();

            // Create user
            $userStmt = $db->prepare("
                INSERT INTO users (store_id, username, email, phone, password_hash, role, full_name_ar)
                VALUES (?, ?, ?, ?, ?, 'owner', ?)
            ");
            $userStmt->execute([
                $storeId,
                $username,
                $email,
                $phone,
                password_hash($password, PASSWORD_BCRYPT),
                $store_name_ar
            ]);
            $userId = $db->lastInsertId();

            // Update store owner_id
            $db->prepare('UPDATE stores SET owner_id = ? WHERE id = ?')->execute([$userId, $storeId]);

            return ['success' => true, 'message' => 'تم التسجيل بنجاح', 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'خطأ في التسجيل: ' . $e->getMessage()];
        }
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public static function getUser() {
        return self::$user;
    }

    public static function getStore() {
        return self::$store;
    }

    private static function loadUser(int $userId) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        self::$user = $stmt->fetch();

        if (self::$user) {
            $storeStmt = $db->prepare('SELECT * FROM stores WHERE id = ? LIMIT 1');
            $storeStmt->execute([self::$user['store_id']]);
            self::$store = $storeStmt->fetch();
        }
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function getStoreId(): int {
        return $_SESSION['store_id'] ?? 0;
    }
}

Auth::init();

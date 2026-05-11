<?php
// ═══════════════════════════════════════════════════════════════
// SaaS Central Database Manager (Multi-Tenant)
// ═══════════════════════════════════════════════════════════════

require_once __DIR__ . '/config.php';

class SaaSDB {
    private static $instance = null;
    private $db = null;

    private function __construct() {
        try {
            $this->db = new PDO('sqlite:' . CENTRAL_DB_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
            $this->initDatabase();
        } catch (Exception $e) {
            die('Database Error: ' . $e->getMessage());
        }
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            new self();
        }
        return self::$instance->db;
    }

    private function initDatabase() {
        // Table: Stores (المتاجر)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS stores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_slug TEXT NOT NULL UNIQUE,
                store_name_ar TEXT NOT NULL,
                store_name_en TEXT NOT NULL,
                owner_id INTEGER NOT NULL,
                description_ar TEXT DEFAULT '',
                description_en TEXT DEFAULT '',
                logo_url TEXT DEFAULT '',
                banner_url TEXT DEFAULT '',
                color_primary TEXT DEFAULT '#D4AF37',
                color_secondary TEXT DEFAULT '#C8C8C8',
                color_accent TEXT DEFAULT '#A67C00',
                pattern_url TEXT DEFAULT '',
                subscription_plan TEXT DEFAULT 'free',
                subscription_expires_at DATETIME,
                is_active INTEGER DEFAULT 1,
                is_verified INTEGER DEFAULT 0,
                lang_primary TEXT DEFAULT 'ar',
                lang_secondary TEXT DEFAULT 'en',
                contact_phone TEXT DEFAULT '',
                contact_whatsapp TEXT DEFAULT '',
                contact_email TEXT DEFAULT '',
                contact_instagram TEXT DEFAULT '',
                contact_facebook TEXT DEFAULT '',
                contact_tiktok TEXT DEFAULT '',
                location_city TEXT DEFAULT '',
                location_address TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Table: Users (المستخدمين)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                phone TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT DEFAULT 'owner',
                full_name_ar TEXT,
                full_name_en TEXT,
                is_active INTEGER DEFAULT 1,
                last_login DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
            );
        ");

        // Table: Categories (الفئات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                name_ar TEXT NOT NULL,
                name_en TEXT NOT NULL,
                description_ar TEXT DEFAULT '',
                description_en TEXT DEFAULT '',
                icon TEXT DEFAULT '📦',
                image_url TEXT,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
            );
        ");

        // Table: Products (المنتجات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL,
                name_ar TEXT NOT NULL,
                name_en TEXT NOT NULL,
                description_ar TEXT DEFAULT '',
                description_en TEXT DEFAULT '',
                sku TEXT UNIQUE,
                price REAL NOT NULL DEFAULT 0,
                original_price REAL,
                discount_percent INTEGER DEFAULT 0,
                image_url TEXT,
                gallery JSON,
                is_active INTEGER DEFAULT 1,
                is_featured INTEGER DEFAULT 0,
                stock_quantity INTEGER DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            );
        ");

        // Table: Orders (الطلبات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                order_number TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL,
                customer_email TEXT,
                customer_phone TEXT NOT NULL,
                customer_address TEXT,
                items JSON NOT NULL,
                total_amount REAL NOT NULL,
                discount_amount REAL DEFAULT 0,
                notes TEXT DEFAULT '',
                status TEXT DEFAULT 'pending',
                payment_method TEXT,
                payment_status TEXT DEFAULT 'unpaid',
                shipping_method TEXT,
                shipping_address TEXT,
                estimated_delivery DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
            );
        ");

        // Table: Customers (العملاء)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                email TEXT NOT NULL,
                phone TEXT NOT NULL,
                name_ar TEXT NOT NULL,
                name_en TEXT,
                address TEXT,
                city TEXT,
                total_orders INTEGER DEFAULT 0,
                total_spent REAL DEFAULT 0,
                is_subscriber INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
                UNIQUE(store_id, email)
            );
        ");

        // Table: Settings (الإعدادات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER,
                setting_key TEXT NOT NULL,
                setting_value TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
                UNIQUE(store_id, setting_key)
            );
        ");

        // Table: Analytics (التحليلات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS analytics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                date DATE NOT NULL,
                views INTEGER DEFAULT 0,
                visitors INTEGER DEFAULT 0,
                orders INTEGER DEFAULT 0,
                revenue REAL DEFAULT 0,
                conversion_rate REAL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
                UNIQUE(store_id, date)
            );
        ");

        // Table: Coupons (الكوبونات)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                code TEXT NOT NULL UNIQUE,
                name_ar TEXT NOT NULL,
                name_en TEXT,
                discount_type TEXT DEFAULT 'percentage',
                discount_value REAL NOT NULL,
                max_uses INTEGER DEFAULT -1,
                used_count INTEGER DEFAULT 0,
                min_order_amount REAL DEFAULT 0,
                valid_from DATETIME,
                valid_until DATETIME,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
            );
        ");
    }

    public static function getDB(): PDO {
        return self::getInstance();
    }
}

// Helper function to get database
function getDB(): PDO {
    return SaaSDB::getDB();
}

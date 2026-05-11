<?php
// ═══════════════════════════════════════════════════════════════
// SaaS Platform Configuration - Syria Market
// Platform: متجري (Online Store Builder for Syria)
// ═══════════════════════════════════════════════════════════════

// Core Paths
define('SAAS_ROOT', __DIR__ . '/../');
define('CORE_PATH', __DIR__);
define('UPLOADS_BASE', SAAS_ROOT . 'uploads/');
define('STORES_DATA_PATH', SAAS_ROOT . 'stores_data/');
define('CENTRAL_DB_PATH', SAAS_ROOT . 'core/database.db');

// App Settings
define('APP_NAME', 'متجري');
define('APP_DOMAIN', 'matajari.sy');
define('APP_URL', 'https://' . APP_DOMAIN);
define('IS_LOCAL', strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);

// Environment
define('ENVIRONMENT', IS_LOCAL ? 'development' : 'production');
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Currency & Market
define('DEFAULT_CURRENCY', 'SYP');      // Syrian Pound
define('CURRENCY_SYMBOL', 'ل.س');
define('COUNTRY_CODE', 'SY');
define('COUNTRY_NAME_AR', 'سوريا');
define('COUNTRY_NAME_EN', 'Syria');

// Supported Languages
define('SUPPORTED_LANGS', ['ar', 'en']);
define('DEFAULT_LANG', 'ar');

// Timezone
date_default_timezone_set('Asia/Damascus');

// Pricing Plans (في المستقبل)
define('PLANS', [
    'free' => ['name_ar' => 'مجاني', 'name_en' => 'Free', 'price' => 0, 'products' => 20, 'bandwidth' => '500MB'],
    'starter' => ['name_ar' => 'البداية', 'name_en' => 'Starter', 'price' => 99, 'products' => 100, 'bandwidth' => '5GB'],
    'growth' => ['name_ar' => 'النمو', 'name_en' => 'Growth', 'price' => 299, 'products' => 1000, 'bandwidth' => '50GB'],
    'pro' => ['name_ar' => 'احترافي', 'name_en' => 'Pro', 'price' => 599, 'products' => -1, 'bandwidth' => 'Unlimited'],
]);

// Create necessary directories
if (!is_dir(UPLOADS_BASE)) mkdir(UPLOADS_BASE, 0755, true);
if (!is_dir(STORES_DATA_PATH)) mkdir(STORES_DATA_PATH, 0755, true);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

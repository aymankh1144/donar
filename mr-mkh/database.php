<?php
define('DB_PATH', __DIR__ . '/data/database.db');
define('UPLOADS_PATH', __DIR__ . '/assets/uploads/');
define('ICONS_PATH', __DIR__ . '/assets/icons/');

function getDB() {
    static $db = null;
    if ($db === null) {
        if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
        if (!is_dir(UPLOADS_PATH)) mkdir(UPLOADS_PATH, 0755, true);
        if (!is_dir(ICONS_PATH)) mkdir(ICONS_PATH, 0755, true);
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
        initDB($db);
    }
    // ✅ حذف الطلبات الأقدم من 7 أيام تلقائياً
    $db->exec("DELETE FROM orders WHERE created_at < datetime('now', '-7 days')");
    return $db;
}

function initDB($db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name_ar TEXT NOT NULL,
            name_en TEXT NOT NULL,
            description_ar TEXT DEFAULT '',
            description_en TEXT DEFAULT '',
            icon TEXT DEFAULT '🍽️',
            sort_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            name_ar TEXT NOT NULL,
            name_en TEXT NOT NULL,
            description_ar TEXT DEFAULT '',
            description_en TEXT DEFAULT '',
            ingredients_ar TEXT DEFAULT '',
            ingredients_en TEXT DEFAULT '',
            features_ar TEXT DEFAULT '',
            features_en TEXT DEFAULT '',
            price REAL NOT NULL DEFAULT 0,
            image TEXT DEFAULT '',
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_id INTEGER,
            title_ar TEXT NOT NULL,
            title_en TEXT NOT NULL,
            original_price REAL NOT NULL,
            offer_price REAL NOT NULL,
            image TEXT DEFAULT '',
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        );

        CREATE TABLE IF NOT EXISTS social_links (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            label TEXT NOT NULL DEFAULT '',
            url TEXT NOT NULL DEFAULT '',
            icon_type TEXT NOT NULL DEFAULT 'svg',
            icon_value TEXT NOT NULL DEFAULT '',
            color TEXT NOT NULL DEFAULT '#ffffff',
            sort_order INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS orders (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            order_num   TEXT NOT NULL UNIQUE,
            customer_name TEXT NOT NULL DEFAULT '',
            customer_phone TEXT NOT NULL DEFAULT '',
            items       TEXT NOT NULL DEFAULT '[]',
            notes       TEXT NOT NULL DEFAULT '',
            total       REAL NOT NULL DEFAULT 0,
            status      TEXT NOT NULL DEFAULT 'pending',
            order_type  TEXT NOT NULL DEFAULT 'pickup',
            table_number TEXT NOT NULL DEFAULT '',
            delivery_address TEXT NOT NULL DEFAULT '',
            telegram_message_id INTEGER DEFAULT NULL,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $existingCols = array_column($db->query("PRAGMA table_info(orders)")->fetchAll(), 'name');
    $newCols = [
        'order_type'       => "ALTER TABLE orders ADD COLUMN order_type TEXT NOT NULL DEFAULT 'pickup'",
        'table_number'     => "ALTER TABLE orders ADD COLUMN table_number TEXT NOT NULL DEFAULT ''",
        'delivery_address' => "ALTER TABLE orders ADD COLUMN delivery_address TEXT NOT NULL DEFAULT ''",
    ];
    foreach ($newCols as $col => $sql) {
        if (!in_array($col, $existingCols, true)) {
            $db->exec($sql);
        }
    }
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
    $defaults = [
        ['admin_username', 'Rami'],
        ['admin_password', password_hash('rraammii11', PASSWORD_DEFAULT)],
        ['restaurant_name_ar', 'مستر دونار'],
        ['restaurant_name_en', 'MR. DONAR'],
        ['slogan_ar', 'مذاق الطبيعة في قلب الجبال'],
        ['slogan_en', 'The Taste of Nature in the Heart of the Mountains'],
        ['contact_phone', ''],
        ['contact_whatsapp', ''],
        ['contact_facebook', ''],
        ['contact_instagram', ''],
        ['contact_show', '1'],
        ['telegram_bot_token', ''],
        ['telegram_chat_id', ''],
        ['telegram_webhook_secret', bin2hex(random_bytes(16))],
    ];
    foreach ($defaults as $d) $stmt->execute($d);

    // Seed categories if empty
    $count = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($count == 0) {
        $cats = [
            ['الفطور','Breakfast','أطباق صباحية طازجة','Fresh morning dishes','🍳',0],
            ['المقبلات','Appetizers','مقبلات شهية','Delicious starters','🥗',1],
            ['الدونر','Donar','دونر طازج بأفضل المكونات','Fresh donar with finest ingredients','🥙',2],
            ['المشاوي','Grills','مشاوي فاخرة على الفحم','Premium charcoal grills','🔥',3],
            ['الحلويات','Desserts','حلويات شرقية أصيلة','Authentic oriental sweets','🍮',4],
            ['المشروبات','Drinks','مشروبات باردة وساخنة','Hot and cold beverages','☕',5],
        ];
        $s = $db->prepare("INSERT INTO categories (name_ar,name_en,description_ar,description_en,icon,sort_order) VALUES (?,?,?,?,?,?)");
        foreach ($cats as $c) $s->execute($c);

        $items = [
            [1,'بيض بفطر وجبنة','Eggs with Mushroom & Cheese','بيض طازج مع فطر وجبنة موزاريلا','Fresh eggs with mushrooms and mozzarella','فطر، جبنة موزاريلا، بيض، زبدة','Mushrooms, Mozzarella, Eggs, Butter','غني بالبروتين','High in protein',4500,'',1,0],
            [1,'أومليت خضار','Veggie Omelette','أومليت بالخضار الطازجة','Omelette with fresh vegetables','بيض، فلفل، طماطم، بصل','Eggs, Peppers, Tomatoes, Onions','خفيف ومغذي','Light and nutritious',3500,'',1,1],
            [2,'سلطة فتوش','Fattoush Salad','سلطة فتوش بالخضار الطازجة','Fresh fattoush salad','خبز محمص، خس، طماطم، فجل، دبس رمان','Toasted bread, Lettuce, Tomatoes, Radish, Pomegranate molasses','غني بالفيتامينات','Rich in vitamins',4000,'',1,0],
            [3,'دونر دجاج','Chicken Donar','دونر دجاج طازج مع صوص خاص','Fresh chicken donar with special sauce','دجاج، خبز، طماطم، بصل، صوص ثوم','Chicken, Bread, Tomatoes, Onions, Garlic sauce','وجبة متكاملة','Complete meal',8000,'',1,0],
            [3,'دونر لحم','Meat Donar','دونر لحم فاخر','Premium meat donar','لحم عجل، خبز، طماطم، بصل','Veal, Bread, Tomatoes, Onions','فاخر وشهي','Luxurious and delicious',10000,'',1,1],
            [4,'شيش طاووق','Shish Tawook','شيش طاووق مشوي على الفحم','Charcoal grilled shish tawook','دجاج، ثوم، ليمون، زيت زيتون','Chicken, Garlic, Lemon, Olive oil','مشوي طازج','Freshly grilled',12000,'',1,0],
            [5,'كنافة نابلسية','Nablusi Kunafa','كنافة نابلسية أصيلة بالقطر','Authentic Nablusi kunafa with syrup','عجينة كنافة، جبنة نابلسية، قطر','Kunafa dough, Nablus cheese, Syrup','حلوة الطعم','Sweet taste',5000,'',1,0],
            [6,'عصير ليمون بالنعنع','Lemon Mint Juice','عصير ليمون طازج مع النعنع','Fresh lemon juice with mint','ليمون، نعنع، سكر، ماء','Lemon, Mint, Sugar, Water','منعش ومبرد','Refreshing and cooling',3500,'',1,0],
        ];
        $s = $db->prepare("INSERT INTO items (category_id,name_ar,name_en,description_ar,description_en,ingredients_ar,ingredients_en,features_ar,features_en,price,image,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($items as $it) $s->execute($it);

        // Sample offer
        $db->exec("INSERT INTO offers (item_id,title_ar,title_en,original_price,offer_price,is_active) VALUES (4,'عرض الدونر الذهبي','Golden Donar Deal',10000,7500,1)");
    }

    // Seed social_links if empty
    $slCount = $db->query("SELECT COUNT(*) FROM social_links")->fetchColumn();
    if ($slCount == 0) {
        $menuSvg = '<svg viewBox="0 0 448 512" width="20" height="20"><path fill="currentColor" d="M416 0C400 0 288 32 288 176V288c0 35.3 28.7 64 64 64h32V480c0 17.7 14.3 32 32 32s32-14.3 32-32V352 24 0H416zM64 16C64 7.8 57.9 1 49.7 .1S34.2 4.6 32.4 12.5L2.1 148.8C.7 155.1 0 161.5 0 167.9c0 45.9 35.1 83.6 80 87.7V480c0 17.7 14.3 32 32 32s32-14.3 32-32V255.6c44.9-4.1 80-41.8 80-87.7c0-6.4-.7-12.8-2.1-19.1L191.6 12.5c-1.8-8-9.3-13.3-17.4-12.4S160 7.8 160 16V150.2c0 5.4-4.4 9.8-9.8 9.8c-5.1 0-9.3-3.9-9.8-9L127.9 14.6C127.2 6.3 120.3 0 112 0s-15.2 6.3-15.9 14.6L83.7 151c-.5 5.1-4.7 9-9.8 9c-5.4 0-9.8-4.4-9.8-9.8V16z"/></svg>';
        $waSvg = '<svg viewBox="0 0 448 512" width="22" height="22"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zM223.9 403.4c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';
        $phoneSvg = '<svg viewBox="0 0 512 512" width="20" height="20"><path fill="currentColor" d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/></svg>';
        $fbSvg = '<svg viewBox="0 0 320 512" width="20" height="20"><path fill="currentColor" d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>';
        $igSvg = '<svg viewBox="0 0 448 512" width="22" height="22"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>';
        $sl = $db->prepare("INSERT INTO social_links (label,url,icon_type,icon_value,color,sort_order,is_active) VALUES (?,?,?,?,?,?,?)");
        $sl->execute(['المنيو','https://Menu-r.Mr-donar.com','svg',$menuSvg,'#d4af37',0,1]);
        $sl->execute(['واتساب','https://wa.me/963968800030','svg',$waSvg,'#25d366',1,1]);
        $sl->execute(['اتصال','tel:0968800030','svg',$phoneSvg,'#007bff',2,1]);
        $sl->execute(['فيسبوك','https://www.facebook.com/share/18wLXpFRWv/','svg',$fbSvg,'#1877f2',3,1]);
        $sl->execute(['انستغرام','https://www.instagram.com/mister_donar?igsh=aWN5MjU3b2xtOHE5','svg',$igSvg,'#E1306C',4,1]);
    }
}

// توليد رقم طلب فريد مثل: ORD-20250505-0001
function generateOrderNum(PDO $db): string {
    $date   = date('Ymd');
    $prefix = 'ORD-' . $date . '-';
    // ✅ إصلاح: Prepared Statement بدلاً من إدراج المتغير مباشرة
    $stmt = $db->prepare('SELECT order_num FROM orders WHERE order_num LIKE ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $seq = $last ? (int)substr($last, -4) + 1 : 1;
    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
}

// جلب قيمة إعداد واحد من جدول settings
function getSetting(PDO $db, string $key, string $default = ''): string {
    $row = $db->prepare("SELECT value FROM settings WHERE key = ?");
    $row->execute([$key]);
    $val = $row->fetchColumn();
    return $val !== false ? $val : $default;
}

function uploadImage($file, $prefix = 'item') {
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];

    // Validate MIME type from actual file content, not just the header
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, $allowed)) return ['success'=>false,'message'=>'نوع الملف غير مدعوم'];

    if ($file['size'] > 5 * 1024 * 1024) return ['success'=>false,'message'=>'حجم الملف كبير جداً (max 5MB)'];

    // Always save as JPEG for uniform compression
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $path = UPLOADS_PATH . $filename;

    // Load image into GD based on real MIME type
    if ($realMime === 'image/png') {
        $src = @imagecreatefrompng($file['tmp_name']);
        if ($src) {
            // Flatten transparency onto white background
            $w = imagesx($src); $h = imagesy($src);
            $flat = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($flat, 255, 255, 255);
            imagefill($flat, 0, 0, $white);
            imagecopy($flat, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);
            $src = $flat;
        }
    } elseif ($realMime === 'image/webp') {
        $src = @imagecreatefromwebp($file['tmp_name']);
    } else {
        $src = @imagecreatefromjpeg($file['tmp_name']);
    }

    if (!$src) {
        // Fallback: move file as-is if GD fails
        if (!move_uploaded_file($file['tmp_name'], $path)) return ['success'=>false,'message'=>'فشل رفع الملف'];
        return ['success'=>true,'filename'=>$filename];
    }

    // Resize if larger than 1200px wide while keeping aspect ratio
    $origW = imagesx($src); $origH = imagesy($src);
    $maxW = 1200;
    if ($origW > $maxW) {
        $newH = (int)round($origH * $maxW / $origW);
        $resized = imagecreatetruecolor($maxW, $newH);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $maxW, $newH, $origW, $origH);
        imagedestroy($src);
        $src = $resized;
    }

    // Save as JPEG with quality 82
    if (!imagejpeg($src, $path, 82)) {
        imagedestroy($src);
        return ['success'=>false,'message'=>'فشل ضغط الصورة'];
    }
    imagedestroy($src);

    return ['success'=>true,'filename'=>$filename];
}

function uploadIcon($file) {
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/svg+xml'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (!in_array($realMime, $allowed)) return ['success'=>false,'message'=>'نوع الملف غير مدعوم (PNG/SVG/WEBP/JPG)'];
    if ($file['size'] > 512 * 1024) return ['success'=>false,'message'=>'حجم الأيقونة كبير جداً (max 512KB)'];

    $ext = $realMime === 'image/svg+xml' ? 'svg' : 'png';
    $filename = 'icon_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $path = ICONS_PATH . $filename;

    if ($realMime === 'image/svg+xml') {
        if (!move_uploaded_file($file['tmp_name'], $path)) return ['success'=>false,'message'=>'فشل رفع الأيقونة'];
        return ['success'=>true,'filename'=>$filename];
    }

    // For raster images: resize to 64x64 PNG
    if ($realMime === 'image/png') $src = @imagecreatefrompng($file['tmp_name']);
    elseif ($realMime === 'image/webp') $src = @imagecreatefromwebp($file['tmp_name']);
    else $src = @imagecreatefromjpeg($file['tmp_name']);

    if (!$src) { move_uploaded_file($file['tmp_name'], $path); return ['success'=>true,'filename'=>$filename]; }

    $resized = imagecreatetruecolor(64, 64);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $trans = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefill($resized, 0, 0, $trans);
    imagecopyresampled($resized, $src, 0, 0, 0, 0, 64, 64, imagesx($src), imagesy($src));
    imagedestroy($src);

    $filename = 'icon_' . time() . '_' . bin2hex(random_bytes(3)) . '.png';
    $path = ICONS_PATH . $filename;
    imagepng($resized, $path);
    imagedestroy($resized);
    return ['success'=>true,'filename'=>$filename];
}

<?php
// ═══════════════════════════════════════════════════════════════
// Helper Functions
// ═══════════════════════════════════════════════════════════════

// Price formatting
function formatPrice(float $price, string $currency = CURRENCY_SYMBOL): string {
    return number_format($price, 2, '.', ',') . ' ' . $currency;
}

// Date formatting
function formatDate(string $date, string $format = 'Y-m-d H:i'): string {
    return date($format, strtotime($date));
}

// Generate slug
function generateSlug(string $text): string {
    $slug = trim($text);
    if (DEFAULT_LANG === 'ar') {
        $slug = preg_replace('/\s+/', '-', $slug);
    } else {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
    }
    return $slug;
}

// Unique slug generator
function generateUniqueSlug(string $text, callable $checkExists): string {
    $slug = generateSlug($text);
    $original = $slug;
    $counter = 1;

    while ($checkExists($slug)) {
        $slug = $original . '-' . $counter;
        $counter++;
    }

    return $slug;
}

// Image upload handler
function uploadImage(array $file, string $destination, int $maxSize = 5242880): array {
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'لا يوجد ملف'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'حجم الملف كبير جداً'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed)) {
        return ['success' => false, 'message' => 'نوع الملف غير مدعوم'];
    }

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $filepath = $destination . $filename;

    // Image processing
    $image = null;
    if ($mime === 'image/png') $image = @imagecreatefrompng($file['tmp_name']);
    elseif ($mime === 'image/webp') $image = @imagecreatefromwebp($file['tmp_name']);
    else $image = @imagecreatefromjpeg($file['tmp_name']);

    if (!$image) {
        return ['success' => false, 'message' => 'فشل معالجة الصورة'];
    }

    // Resize if needed
    $origW = imagesx($image);
    $origH = imagesy($image);
    if ($origW > 1200) {
        $newH = (int)round($origH * 1200 / $origW);
        $resized = imagecreatetruecolor(1200, $newH);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 1200, $newH, $origW, $origH);
        imagedestroy($image);
        $image = $resized;
    }

    if (!imagejpeg($image, $filepath, 82)) {
        imagedestroy($image);
        return ['success' => false, 'message' => 'فشل حفظ الصورة'];
    }

    imagedestroy($image);
    return ['success' => true, 'filename' => $filename, 'path' => $filepath];
}

// JSON response
function jsonResponse(array $data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Generate order number
function generateOrderNumber(int $storeId): string {
    $db = getDB();
    $date = date('Ymd');
    $prefix = 'ORD-' . $date . '-';
    
    $stmt = $db->prepare('SELECT order_number FROM orders WHERE store_id = ? AND order_number LIKE ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$storeId, $prefix . '%']);
    $last = $stmt->fetchColumn();
    
    $seq = $last ? (int)substr($last, -4) + 1 : 1;
    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
}

// Validate email
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone (Syrian format)
function isValidPhoneSY(string $phone): bool {
    return preg_match('/^(\+?963|0)?9\d{8}$/', str_replace([' ', '-'], '', $phone));
}

// Translate key-value
function trans(string $key, string $lang = DEFAULT_LANG): string {
    $translations = [
        'ar' => [
            'store' => 'المتجر',
            'product' => 'المنتج',
            'order' => 'الطلب',
            'customer' => 'العميل',
            'category' => 'الفئة',
            'settings' => 'الإعدادات',
            'dashboard' => 'لوحة التحكم',
            'logout' => 'تسجيل الخروج',
            'login' => 'تسجيل الدخول',
            'register' => 'إنشاء حساب',
        ],
        'en' => [
            'store' => 'Store',
            'product' => 'Product',
            'order' => 'Order',
            'customer' => 'Customer',
            'category' => 'Category',
            'settings' => 'Settings',
            'dashboard' => 'Dashboard',
            'logout' => 'Logout',
            'login' => 'Login',
            'register' => 'Register',
        ]
    ];

    return $translations[$lang][$key] ?? $key;
}

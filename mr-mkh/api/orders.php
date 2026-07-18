<?php
/**
 * API الطلبات — api/orders.php
 */

require_once __DIR__ . '/../database.php';
header('Content-Type: application/json; charset=utf-8');

// ✅ إصلاح: CORS محدود بدلاً من wildcard
$allowedOrigins = [
    'https://mr-donar.com',
    'https://www.mr-donar.com',
    'http://localhost',
    'http://127.0.0.1',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins) || preg_match('/^https?:\/\/.*\.ngrok(-free)?\.app$/', $origin) || preg_match('/^https?:\/\/.*\.ngrok\.io$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: https://mr-donar.com');
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'place';

// ── GET: استعلام عن حالة طلب ──────────────────────────────
if ($method === 'GET' && $action === 'status') {
    $orderNum = trim($_GET['order_num'] ?? '');
    if (!$orderNum || !preg_match('/^ORD-\d{8}-\d{4}$/', $orderNum)) {
        echo json_encode(['success'=>false,'message'=>'رقم الطلب غير صالح']); exit;
    }

    $stmt = $db->prepare("SELECT order_num, status, created_at FROM orders WHERE order_num = ?");
    $stmt->execute([$orderNum]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) { echo json_encode(['success'=>false,'message'=>'الطلب غير موجود']); exit; }

    $labels = [
        'pending'   => ['ar'=>'بانتظار رد المطبخ',       'en'=>'Waiting for kitchen response'],
        'accepted'  => ['ar'=>'تم قبول طلبك',            'en'=>'Your order has been accepted'],
        'rejected'  => ['ar'=>'عذراً، تم رفض طلبك',     'en'=>'Sorry, your order was rejected'],
        'preparing' => ['ar'=>'طلبك قيد التحضير',        'en'=>'Your order is being prepared'],
        'ready'     => ['ar'=>'طلبك جاهز للتسليم',       'en'=>'Your order is ready for delivery'],
        'delivered' => ['ar'=>'تم تسليم الطلب',          'en'=>'Order delivered'],
    ];

    echo json_encode([
        'success'      => true,
        'order_num'    => $order['order_num'],
        'status'       => $order['status'],
        'status_label' => $labels[$order['status']] ?? ['ar'=>$order['status'],'en'=>$order['status']],
        'created_at'   => $order['created_at'],
    ]);
    exit;
}

// ── POST: استقبال طلب جديد ────────────────────────────────
if ($method !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'الطريقة غير مدعومة']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    echo json_encode(['success'=>false,'message'=>'البيانات غير صالحة']);
    exit;
}

// ── التحقق من البيانات ─────────────────────────────────────
$customerName  = trim($body['customer_name']  ?? '');
$customerPhone = trim($body['customer_phone'] ?? '');
$notes         = trim($body['notes']          ?? '');
$items         = $body['items']               ?? [];

// ── نوع الطلب: dine_in (طاولة) / pickup (استلام مباشر) / delivery (توصيل) ──
$orderType       = trim($body['order_type'] ?? 'pickup');
$tableNumber     = trim($body['table_number'] ?? '');
$deliveryAddress = trim($body['delivery_address'] ?? '');

if (!in_array($orderType, ['dine_in', 'pickup', 'delivery'], true)) {
    echo json_encode(['success'=>false,'message'=>'نوع الطلب غير صالح']); exit;
}
if ($orderType === 'dine_in') {
    if (!$tableNumber || strlen($tableNumber) > 10) {
        echo json_encode(['success'=>false,'message'=>'رقم الطاولة مطلوب']); exit;
    }
    $deliveryAddress = '';
} elseif ($orderType === 'delivery') {
    if (!$deliveryAddress || strlen($deliveryAddress) > 300) {
        echo json_encode(['success'=>false,'message'=>'عنوان التوصيل مطلوب']); exit;
    }
    $tableNumber = '';
} else {
    $tableNumber = '';
    $deliveryAddress = '';
}

if (!$customerName) {
    echo json_encode(['success'=>false,'message'=>'اسم الزبون مطلوب']); exit;
}
if (strlen($customerName) > 80) {
    echo json_encode(['success'=>false,'message'=>'الاسم طويل جداً']); exit;
}
if (empty($items) || !is_array($items)) {
    echo json_encode(['success'=>false,'message'=>'السلة فارغة']); exit;
}
if (count($items) > 50) {
    echo json_encode(['success'=>false,'message'=>'عدد العناصر كبير جداً']); exit;
}

// ✅ إصلاح رئيسي: جلب الأسعار من قاعدة البيانات بدلاً من الاعتماد على بيانات العميل
$cleanItems = [];
$serverTotal = 0;

foreach ($items as $item) {
    if (empty($item['id']) || empty($item['qty'])) continue;
    $itemId = (int)$item['id'];
    $qty    = max(1, (int)$item['qty']);
    if ($qty > 20) $qty = 20; // حد أقصى للكمية

    // جلب السعر الحقيقي من DB
    $stmt = $db->prepare("SELECT id, name_ar, name_en, price FROM items WHERE id = ? AND is_active = 1");
    $stmt->execute([$itemId]);
    $dbItem = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dbItem) continue; // تجاهل العناصر غير الموجودة

    $cleanItems[] = [
        'id'    => $dbItem['id'],
        'name'  => $dbItem['name_ar'],
        'name_en' => $dbItem['name_en'],
        'price' => (float)$dbItem['price'],
        'qty'   => $qty,
    ];
    $serverTotal += $dbItem['price'] * $qty;
}

if (empty($cleanItems)) {
    echo json_encode(['success'=>false,'message'=>'عناصر السلة غير صالحة']); exit;
}

// ── حفظ الطلب ─────────────────────────────────────────────
try {
    $orderNum = generateOrderNum($db);
    $stmt = $db->prepare("
        INSERT INTO orders (order_num, customer_name, customer_phone, items, notes, total, status, order_type, table_number, delivery_address, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        $orderNum,
        $customerName,
        $customerPhone,
        json_encode($cleanItems, JSON_UNESCAPED_UNICODE),
        $notes,
        $serverTotal,
        $orderType,
        $tableNumber,
        $deliveryAddress,
    ]);
    $orderId = $db->lastInsertId();
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'خطأ في قاعدة البيانات']); exit;
}

// ── إرسال تيليغرام ────────────────────────────────────────
$botToken = getSetting($db, 'telegram_bot_token');
$chatId   = getSetting($db, 'telegram_chat_id');
if ($botToken && $chatId) {
    sendTelegramOrder($botToken, $chatId, $orderId, $orderNum, $customerName, $customerPhone, $cleanItems, $serverTotal, $notes, $db, $orderType, $tableNumber, $deliveryAddress);
}

echo json_encode([
    'success'   => true,
    'order_num' => $orderNum,
    'message'   => 'تم استلام طلبك بنجاح',
]);

// ── دالة إرسال تيليغرام ───────────────────────────────────
function sendTelegramOrder(string $token, string $chatId, int $orderId, string $orderNum,
    string $name, string $phone, array $items, float $total, string $notes, PDO $db,
    string $orderType = 'pickup', string $tableNumber = '', string $deliveryAddress = ''): void
{
    $itemsText = implode("\n", array_map(
        fn($i) => "  • {$i['name']} × {$i['qty']} — " . number_format($i['price'] * $i['qty'], 0) . ' ل.س',
        $items
    ));
    $notesLine = $notes ? "\n📝 *ملاحظات:* " . escTg($notes) : '';
    $phoneLine = $phone ? "\n📞 *الهاتف:* " . escTg($phone) : '';

    $typeLabels = [
        'dine_in'  => '🍽️ في المطعم — طاولة رقم ' . escTg($tableNumber),
        'pickup'   => '🏃 استلام مباشر من المطعم',
        'delivery' => '🛵 توصيل — ' . escTg($deliveryAddress),
    ];
    $typeLine = "\n📍 *نوع الطلب:* " . ($typeLabels[$orderType] ?? $orderType);

    $text = "🟢 *طلب جديد {$orderNum}*\n"
          . "─────────────────\n"
          . $itemsText . "\n"
          . "─────────────────\n"
          . "👤 *الاسم:* " . escTg($name)
          . $phoneLine
          . $typeLine
          . $notesLine . "\n"
          . "💰 *الإجمالي:* " . number_format($total, 0) . " ل.س";

    $keyboard = ['inline_keyboard' => [[
        ['text' => '✅ قبول الطلب',  'callback_data' => "accept_{$orderId}"],
        ['text' => '❌ رفض الطلب',   'callback_data' => "reject_{$orderId}"],
    ]]];

    $payload = [
        'chat_id'      => $chatId,
        'text'         => $text,
        'parse_mode'   => 'Markdown',
        'reply_markup' => json_encode($keyboard),
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $resp = json_decode($response, true);
    if (!empty($resp['ok']) && !empty($resp['result']['message_id'])) {
        $db->prepare("UPDATE orders SET telegram_message_id = ? WHERE id = ?")
           ->execute([$resp['result']['message_id'], $orderId]);
    }
}

function escTg(string $text): string {
    return str_replace(['_','*','[',']','`'], ['\\_','\\*','\\[','\\]','\\`'], $text);
}

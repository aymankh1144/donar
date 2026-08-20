<?php
/**
 * Webhook تيليغرام — api/telegram.php
 * يستقبل ضغطات الأزرار من تيليغرام ويحدّث حالة الطلب
 */

require_once __DIR__ . '/../database.php';
header('Content-Type: application/json; charset=utf-8');

$db          = getDB();
$botToken    = getSetting($db, 'telegram_bot_token');
$chatId      = getSetting($db, 'telegram_chat_id');
$webhookSecret = getSetting($db, 'telegram_webhook_secret');

// ✅ التحقق من Secret Token — فقط إذا كان مُعيَّناً في الإعدادات
// إذا كان فارغاً (قاعدة بيانات قديمة أو لم يُسجَّل بعد) نقبل الطلب
if ($webhookSecret) {
    $incoming = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    if (!hash_equals($webhookSecret, $incoming)) {
        // نتجاهل بصمت بدلاً من رفض (لتجنب مشكلة عدم تطابق السر)
        http_response_code(200);
        echo json_encode(['ok' => true]);
        exit;
    }
}

$update = json_decode(file_get_contents('php://input'), true);
if (!$update) { echo json_encode(['ok' => true]); exit; }

if (empty($update['callback_query'])) { echo json_encode(['ok' => true]); exit; }

$cb    = $update['callback_query'];
$cbId  = $cb['id'];
$msgId = $cb['message']['message_id'] ?? null;
$data  = $cb['data'] ?? '';

if (!preg_match('/^(accept|reject|preparing|ready|delivered)_(\d+)$/', $data, $m)) {
    answerCallback($botToken, $cbId, '');
    echo json_encode(['ok' => true]);
    exit;
}

$action  = $m[1];
$orderId = (int)$m[2];

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    answerCallback($botToken, $cbId, 'الطلب غير موجود');
    echo json_encode(['ok' => true]);
    exit;
}

$statusMap = [
    'accept'    => 'accepted',
    'reject'    => 'rejected',
    'preparing' => 'preparing',
    'ready'     => 'ready',
    'delivered' => 'delivered',
];
$newStatus = $statusMap[$action];

$db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
   ->execute([$newStatus, $orderId]);

$keyboard = buildKeyboard($newStatus, $orderId);

$statusLabels = [
    'accepted'  => '✅ مقبول — لم يبدأ التحضير بعد',
    'rejected'  => '❌ مرفوض',
    'preparing' => '🟠 قيد التحضير',
    'ready'     => '🟢 جاهز للتسليم',
    'delivered' => '✔️ تم التسليم',
];

$items     = json_decode($order['items'], true);
$itemsText = implode("\n", array_map(
    fn($i) => "  • {$i['name']} × {$i['qty']} — " . number_format($i['price'] * $i['qty'], 0) . ' ل.س',
    $items
));

$notesLine = $order['notes'] ? "\n📝 *ملاحظات:* " . escTg($order['notes']) : '';
$phoneLine = $order['customer_phone'] ? "\n📞 *الهاتف:* " . escTg($order['customer_phone']) : '';

// ✅ إضافة سطر نوع الطلب (كان مفقوداً هنا وهو سبب اختفاء العنوان)
$typeLabels = [
    'dine_in'  => '🍽️ في المطعم — طاولة رقم ' . escTg($order['table_number']),
    'pickup'   => '🏃 استلام مباشر من المطعم',
    'delivery' => '🛵 توصيل — ' . escTg($order['delivery_address']),
];
$typeLine = "\n📍 *نوع الطلب:* " . ($typeLabels[$order['order_type']] ?? $order['order_type']);

$newText = "🟢 *طلب {$order['order_num']}*\n"
         . "─────────────────\n"
         . $itemsText . "\n"
         . "─────────────────\n"
         . "👤 *الاسم:* " . escTg($order['customer_name'])
         . $phoneLine
         . $typeLine
         . $notesLine . "\n"
         . "💰 *الإجمالي:* " . number_format($order['total'], 0) . " ل.س\n"
         . "─────────────────\n"
         . "الحالة: " . ($statusLabels[$newStatus] ?? $newStatus);
editMessage($botToken, $chatId, $msgId, $newText, $keyboard);




$cbLabels = [
    'accept'    => '✅ تم القبول',
    'reject'    => '❌ تم الرفض',
    'preparing' => '🟠 بدأ التحضير',
    'ready'     => '🟢 جاهز',
    'delivered' => '✔️ تم التسليم',
];
answerCallback($botToken, $cbId, $cbLabels[$action] ?? '');

echo json_encode(['ok' => true]);

// ── الدوال ──────────────────────────────────────────────────

function buildKeyboard(string $status, int $orderId): array {
    if (in_array($status, ['rejected', 'delivered'])) return ['inline_keyboard' => []];
    if ($status === 'accepted') return ['inline_keyboard' => [[
        ['text' => '🟠 قيد التحضير',   'callback_data' => "preparing_{$orderId}"],
        ['text' => '🟢 جاهز للتسليم',  'callback_data' => "ready_{$orderId}"],
        ['text' => '✔️ تم التسليم',    'callback_data' => "delivered_{$orderId}"],
    ]]];
    if ($status === 'preparing') return ['inline_keyboard' => [[
        ['text' => '🟢 جاهز للتسليم', 'callback_data' => "ready_{$orderId}"],
        ['text' => '✔️ تم التسليم',   'callback_data' => "delivered_{$orderId}"],
    ]]];
    if ($status === 'ready') return ['inline_keyboard' => [[
        ['text' => '✔️ تم التسليم', 'callback_data' => "delivered_{$orderId}"],
    ]]];
    return ['inline_keyboard' => []];
}

function editMessage(string $token, string $chatId, int $msgId, string $text, array $keyboard): void {
    $ch = curl_init("https://api.telegram.org/bot{$token}/editMessageText");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'chat_id'      => $chatId,
            'message_id'   => $msgId,
            'text'         => $text,
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode($keyboard),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function answerCallback(string $token, string $cbId, string $text): void {
    $ch = curl_init("https://api.telegram.org/bot{$token}/answerCallbackQuery");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => ['callback_query_id' => $cbId, 'text' => $text],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function escTg(string $text): string {
    return str_replace(['_', '*', '[', ']', '`'], ['\\_', '\\*', '\\[', '\\]', '\\`'], $text);
}

<?php
// ══════════════════════════════════════════════════════════════════════════════
// Authentication API
// ══════════════════════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'login') {
        $result = Auth::login($data['username'] ?? '', $data['password'] ?? '');
        jsonResponse($result);
    }

    if ($action === 'register') {
        $result = Auth::register(
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['username'] ?? '',
            $data['password'] ?? '',
            $data['store_name_ar'] ?? '',
            $data['store_name_en'] ?? '',
            generateSlug($data['store_slug'] ?? $data['store_name_ar'] ?? '')
        );
        jsonResponse($result);
    }

    if ($action === 'logout') {
        Auth::logout();
        jsonResponse(['success' => true, 'message' => 'تم تسجيل الخروج']);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'طريقة الطلب غير مدعومة'], 405);
}

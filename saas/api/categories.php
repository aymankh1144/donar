<?php
// ══════════════════════════════════════════════════════════════════════════════
// Categories Management API
// ══════════════════════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/helpers.php';

Auth::requireLogin();
$storeId = Auth::getStoreId();
$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'list') {
        $stmt = $db->prepare('SELECT * FROM categories WHERE store_id = ? ORDER BY sort_order ASC');
        $stmt->execute([$storeId]);
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'create') {
        try {
            $stmt = $db->prepare("
                INSERT INTO categories (store_id, name_ar, name_en, description_ar, description_en, icon, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $storeId,
                $data['name_ar'] ?? '',
                $data['name_en'] ?? '',
                $data['description_ar'] ?? '',
                $data['description_en'] ?? '',
                $data['icon'] ?? '📦',
                $data['is_active'] ?? 1
            ]);

            jsonResponse(['success' => true, 'message' => 'تم إنشاء الفئة', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    if ($action === 'update') {
        $id = $data['id'] ?? 0;
        try {
            $stmt = $db->prepare("
                UPDATE categories 
                SET name_ar = ?, name_en = ?, description_ar = ?, description_en = ?, icon = ?, is_active = ?
                WHERE id = ? AND store_id = ?
            ");
            $stmt->execute([
                $data['name_ar'] ?? '',
                $data['name_en'] ?? '',
                $data['description_ar'] ?? '',
                $data['description_en'] ?? '',
                $data['icon'] ?? '📦',
                $data['is_active'] ?? 1,
                $id,
                $storeId
            ]);

            jsonResponse(['success' => true, 'message' => 'تم التحديث']);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    if ($action === 'delete') {
        $id = $data['id'] ?? 0;
        $db->prepare('DELETE FROM categories WHERE id = ? AND store_id = ?')->execute([$id, $storeId]);
        jsonResponse(['success' => true, 'message' => 'تم الحذف']);
    }
}

jsonResponse(['success' => false, 'message' => 'طريقة غير صحيحة'], 400);

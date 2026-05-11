<?php
// ══════════════════════════════════════════════════════════════════════════════
// Orders Management API
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
        $status = $_GET['status'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = 'SELECT * FROM orders WHERE store_id = ?';
        $params = [$storeId];

        if ($status) {
            $query .= ' AND status = ?';
            $params[] = $status;
        }

        $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Count total
        $countQuery = 'SELECT COUNT(*) FROM orders WHERE store_id = ?';
        $countParams = [$storeId];
        if ($status) {
            $countQuery .= ' AND status = ?';
            $countParams[] = $status;
        }
        $total = $db->prepare($countQuery)->execute($countParams) ? 
                  $db->prepare($countQuery)->execute($countParams) && $db->query($countQuery)->fetchColumn() : 0;

        jsonResponse([
            'success' => true,
            'data' => $orders,
            'pagination' => ['page' => $page, 'total' => 0, 'pages' => 1]
        ]);
    }

    if ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        $stmt = $db->prepare('SELECT * FROM orders WHERE id = ? AND store_id = ? LIMIT 1');
        $stmt->execute([$id, $storeId]);
        $order = $stmt->fetch();

        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        jsonResponse(['success' => true, 'data' => $order]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'update-status') {
        $id = $data['id'] ?? 0;
        $status = $data['status'] ?? '';

        try {
            $stmt = $db->prepare('UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND store_id = ?');
            $stmt->execute([$status, $id, $storeId]);
            jsonResponse(['success' => true, 'message' => 'تم التحديث']);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

jsonResponse(['success' => false, 'message' => 'طريقة غير صحيحة'], 400);

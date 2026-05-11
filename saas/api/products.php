<?php
// ══════════════════════════════════════════════════════════════════════════════
// Products Management API
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
        $categoryId = $_GET['category_id'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $query = 'SELECT * FROM products WHERE store_id = ?';
        $params = [$storeId];

        if ($categoryId) {
            $query .= ' AND category_id = ?';
            $params[] = $categoryId;
        }

        $query .= ' ORDER BY sort_order ASC, created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Count total
        $countQuery = 'SELECT COUNT(*) FROM products WHERE store_id = ?';
        $countParams = [$storeId];
        if ($categoryId) {
            $countQuery .= ' AND category_id = ?';
            $countParams[] = $categoryId;
        }
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();

        jsonResponse([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => $page,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    if ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ? AND store_id = ? LIMIT 1');
        $stmt->execute([$id, $storeId]);
        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        jsonResponse(['success' => true, 'data' => $product]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'create') {
        try {
            $stmt = $db->prepare("
                INSERT INTO products (
                    store_id, category_id, name_ar, name_en, description_ar, description_en,
                    sku, price, original_price, discount_percent, is_active, stock_quantity
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $storeId,
                $data['category_id'] ?? 1,
                $data['name_ar'] ?? '',
                $data['name_en'] ?? '',
                $data['description_ar'] ?? '',
                $data['description_en'] ?? '',
                $data['sku'] ?? null,
                $data['price'] ?? 0,
                $data['original_price'] ?? null,
                $data['discount_percent'] ?? 0,
                $data['is_active'] ?? 1,
                $data['stock_quantity'] ?? 0
            ]);

            $productId = $db->lastInsertId();
            jsonResponse(['success' => true, 'message' => 'تم إنشاء المنتج', 'product_id' => $productId]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    if ($action === 'update') {
        try {
            $id = $data['id'] ?? 0;
            $stmt = $db->prepare('SELECT id FROM products WHERE id = ? AND store_id = ? LIMIT 1');
            $stmt->execute([$id, $storeId]);
            if (!$stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'المنتج غير موجود'], 404);
            }

            $updates = ['name_ar', 'name_en', 'description_ar', 'description_en', 'price', 'original_price', 'discount_percent', 'stock_quantity', 'is_active'];
            $setParts = [];
            $values = [];

            foreach ($updates as $field) {
                if (isset($data[$field])) {
                    $setParts[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            $values[] = $id;
            $values[] = $storeId;

            $sql = 'UPDATE products SET ' . implode(', ', $setParts) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND store_id = ?';
            $db->prepare($sql)->execute($values);

            jsonResponse(['success' => true, 'message' => 'تم التحديث بنجاح']);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    if ($action === 'delete') {
        $id = $data['id'] ?? 0;
        $db->prepare('DELETE FROM products WHERE id = ? AND store_id = ?')->execute([$id, $storeId]);
        jsonResponse(['success' => true, 'message' => 'تم الحذف بنجاح']);
    }

    if ($action === 'upload-image') {
        $productId = $data['product_id'] ?? 0;
        
        // Verify product ownership
        $stmt = $db->prepare('SELECT id FROM products WHERE id = ? AND store_id = ?');
        $stmt->execute([$productId, $storeId]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }

        if (!isset($_FILES['image'])) {
            jsonResponse(['success' => false, 'message' => 'لا يوجد ملف'], 400);
        }

        $destination = UPLOADS_BASE . $storeId . '/products/';
        $result = uploadImage($_FILES['image'], $destination);

        if ($result['success']) {
            $imageUrl = '/uploads/' . $storeId . '/products/' . $result['filename'];
            $db->prepare('UPDATE products SET image_url = ? WHERE id = ?')
                ->execute([$imageUrl, $productId]);
            jsonResponse(['success' => true, 'url' => $imageUrl]);
        }

        jsonResponse($result, 400);
    }
}

jsonResponse(['success' => false, 'message' => 'طريقة غير صحيحة'], 400);

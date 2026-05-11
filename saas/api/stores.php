<?php
// ══════════════════════════════════════════════════════════════════════════════
// Stores Management API
// ══════════════════════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/helpers.php';

Auth::requireLogin();
$store = Auth::getStore();
$storeId = Auth::getStoreId();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($method === 'GET') {
    if ($action === 'info') {
        jsonResponse(['success' => true, 'data' => $store]);
    }

    if ($action === 'dashboard') {
        $stmt = $db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM products WHERE store_id = ?) as total_products,
                (SELECT COUNT(*) FROM orders WHERE store_id = ?) as total_orders,
                (SELECT COUNT(*) FROM customers WHERE store_id = ?) as total_customers,
                (SELECT SUM(total_amount) FROM orders WHERE store_id = ? AND status = 'completed') as total_revenue
        ");
        $stmt->execute([$storeId, $storeId, $storeId, $storeId]);
        $stats = $stmt->fetch();
        jsonResponse(['success' => true, 'data' => $stats]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'update-settings') {
        try {
            $updates = [
                'store_name_ar' => $data['store_name_ar'] ?? null,
                'store_name_en' => $data['store_name_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'color_primary' => $data['color_primary'] ?? null,
                'color_secondary' => $data['color_secondary'] ?? null,
                'color_accent' => $data['color_accent'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_instagram' => $data['contact_instagram'] ?? null,
                'contact_facebook' => $data['contact_facebook'] ?? null,
                'location_city' => $data['location_city'] ?? null,
                'location_address' => $data['location_address'] ?? null,
            ];

            $setParts = [];
            $values = [];
            foreach ($updates as $key => $value) {
                if ($value !== null) {
                    $setParts[] = "$key = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                jsonResponse(['success' => false, 'message' => 'لا توجد بيانات للتحديث']);
            }

            $values[] = $storeId;
            $sql = 'UPDATE stores SET ' . implode(', ', $setParts) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            jsonResponse(['success' => true, 'message' => 'تم التحديث بنجاح']);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    if ($action === 'upload-logo') {
        if (!isset($_FILES['logo'])) {
            jsonResponse(['success' => false, 'message' => 'لا يوجد ملف'], 400);
        }

        $destination = UPLOADS_BASE . $storeId . '/logo/';
        $result = uploadImage($_FILES['logo'], $destination);

        if ($result['success']) {
            $logoUrl = '/uploads/' . $storeId . '/logo/' . $result['filename'];
            $db->prepare('UPDATE stores SET logo_url = ? WHERE id = ?')
                ->execute([$logoUrl, $storeId]);
            jsonResponse(['success' => true, 'url' => $logoUrl]);
        }

        jsonResponse($result, 400);
    }

    if ($action === 'upload-banner') {
        if (!isset($_FILES['banner'])) {
            jsonResponse(['success' => false, 'message' => 'لا يوجد ملف'], 400);
        }

        $destination = UPLOADS_BASE . $storeId . '/banner/';
        $result = uploadImage($_FILES['banner'], $destination);

        if ($result['success']) {
            $bannerUrl = '/uploads/' . $storeId . '/banner/' . $result['filename'];
            $db->prepare('UPDATE stores SET banner_url = ? WHERE id = ?')
                ->execute([$bannerUrl, $storeId]);
            jsonResponse(['success' => true, 'url' => $bannerUrl]);
        }

        jsonResponse($result, 400);
    }
}

jsonResponse(['success' => false, 'message' => 'طريقة غير صحيحة'], 400);

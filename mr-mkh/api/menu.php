<?php
require_once __DIR__ . '/../database.php';
header('Content-Type: application/json; charset=utf-8');
$db = getDB();
$action = $_GET['action'] ?? 'categories';

switch ($action) {
    case 'categories':
        $rows = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
        $countStmt = $db->prepare("SELECT COUNT(*) FROM items WHERE category_id=? AND is_active=1");
        foreach ($rows as &$row) {
            $countStmt->execute([$row['id']]);
            $row['item_count'] = $countStmt->fetchColumn();
        }
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;

    case 'items':
        $cid = (int)($_GET['category_id'] ?? 0);
        $cat = $db->prepare("SELECT * FROM categories WHERE id=? AND is_active=1");
        $cat->execute([$cid]); $category = $cat->fetch();
        if (!$category) { echo json_encode(['success'=>false,'message'=>'Not found']); exit; }
        $stmt = $db->prepare("SELECT * FROM items WHERE category_id=? AND is_active=1 ORDER BY sort_order,id");
        $stmt->execute([$cid]);
        $items = $stmt->fetchAll();
        foreach ($items as &$item)
            $item['image_url'] = $item['image'] ? 'assets/uploads/' . $item['image'] : '';
        echo json_encode(['success'=>true,'category'=>$category,'data'=>$items]);
        break;

    case 'item':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT i.*, c.name_ar as cat_name_ar, c.name_en as cat_name_en FROM items i JOIN categories c ON i.category_id=c.id WHERE i.id=? AND i.is_active=1");
        $stmt->execute([$id]); $item = $stmt->fetch();
        if (!$item) { echo json_encode(['success'=>false,'message'=>'Not found']); exit; }
        $item['image_url'] = $item['image'] ? 'assets/uploads/' . $item['image'] : '';
        echo json_encode(['success'=>true,'data'=>$item]);
        break;

    case 'offers':
        $rows = $db->query("SELECT o.*, i.image as item_image FROM offers o LEFT JOIN items i ON o.item_id=i.id WHERE o.is_active=1 ORDER BY o.sort_order,o.id")->fetchAll();
        foreach ($rows as &$row) {
            if ($row['image']) $row['image_url'] = 'assets/uploads/' . $row['image'];
            elseif ($row['item_image']) $row['image_url'] = 'assets/uploads/' . $row['item_image'];
            else $row['image_url'] = '';
            $row['discount'] = round((1 - $row['offer_price'] / $row['original_price']) * 100);
        }
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;

    case 'settings':
        $rows = $db->query("SELECT key,value FROM settings")->fetchAll();
        $s = [];
        foreach ($rows as $r) $s[$r['key']] = $r['value'];
        unset($s['admin_password'],$s['admin_username']);
        echo json_encode(['success'=>true,'data'=>$s]);
        break;

    case 'social_links':
        $rows = $db->query("SELECT id,label,url,icon_type,icon_value,color,sort_order FROM social_links WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
        foreach ($rows as &$r) {
            $r['icon_url'] = ($r['icon_type']==='upload' && $r['icon_value']) ? 'assets/icons/'.$r['icon_value'] : '';
        }
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;

    case 'all_items':
        $rows = $db->query("SELECT i.*,c.name_ar as cat_ar FROM items i JOIN categories c ON i.category_id=c.id WHERE i.is_active=1 ORDER BY i.sort_order,i.id")->fetchAll();
        foreach ($rows as &$r) {
            $r['image_url'] = $r['image'] ? 'assets/uploads/'.$r['image'] : '';
        }
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;
}

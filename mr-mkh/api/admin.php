<?php
session_start();
require_once __DIR__ . '/../database.php';
header('Content-Type: application/json; charset=utf-8');
$db = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action !== 'login' && empty($_SESSION['admin_logged_in'])) {
    http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

switch ($action) {
    case 'login':
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        $su = $db->query("SELECT value FROM settings WHERE key='admin_username'")->fetchColumn();
        $sh = $db->query("SELECT value FROM settings WHERE key='admin_password'")->fetchColumn();
        if ($u === $su && password_verify($p, $sh)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $u;
            echo json_encode(['success'=>true]);
        } else echo json_encode(['success'=>false,'message'=>'بيانات الدخول غير صحيحة']);
        break;

    case 'logout': session_destroy(); echo json_encode(['success'=>true]); break;
    case 'check_auth': echo json_encode(['success'=>true,'username'=>$_SESSION['admin_username']??'']); break;

    // ── CATEGORIES
    case 'get_categories':
        $rows = $db->query("SELECT * FROM categories ORDER BY sort_order,id")->fetchAll();
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM items WHERE category_id=?");
        foreach ($rows as &$r) { $cntStmt->execute([$r['id']]); $r['item_count'] = $cntStmt->fetchColumn(); }
        echo json_encode(['success'=>true,'data'=>$rows]); break;

    case 'add_category':
        $s = $db->prepare("INSERT INTO categories (name_ar,name_en,description_ar,description_en,icon,sort_order) VALUES (?,?,?,?,?,?)");
        $max = $db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM categories")->fetchColumn();
        $s->execute([trim($_POST['name_ar']??''),trim($_POST['name_en']??''),trim($_POST['description_ar']??''),trim($_POST['description_en']??''),trim($_POST['icon']??'🍽️'),$max]);
        echo json_encode(['success'=>true,'id'=>$db->lastInsertId()]); break;

    case 'update_category':
        $s = $db->prepare("UPDATE categories SET name_ar=?,name_en=?,description_ar=?,description_en=?,icon=?,is_active=? WHERE id=?");
        $s->execute([trim($_POST['name_ar']??''),trim($_POST['name_en']??''),trim($_POST['description_ar']??''),trim($_POST['description_en']??''),trim($_POST['icon']??'🍽️'),(int)($_POST['is_active']??1),(int)($_POST['id']??0)]);
        echo json_encode(['success'=>true]); break;

    case 'delete_category':
        // Delete item images first
        $items = $db->prepare("SELECT image FROM items WHERE category_id=?");
        $items->execute([(int)($_POST['id']??0)]);
        foreach ($items->fetchAll() as $it) if ($it['image']) @unlink(UPLOADS_PATH.$it['image']);
        $db->prepare("DELETE FROM categories WHERE id=?")->execute([(int)($_POST['id']??0)]);
        echo json_encode(['success'=>true]); break;

    // ── ITEMS
    case 'get_items':
        $cid = (int)($_GET['category_id']??0);
        if ($cid) { $s=$db->prepare("SELECT i.*,c.name_ar as cat_name_ar FROM items i JOIN categories c ON i.category_id=c.id WHERE i.category_id=? ORDER BY i.sort_order,i.id"); $s->execute([$cid]); }
        else { $s=$db->query("SELECT i.*,c.name_ar as cat_name_ar FROM items i JOIN categories c ON i.category_id=c.id ORDER BY i.category_id,i.sort_order,i.id"); }
        $items = $s->fetchAll();
        foreach ($items as &$it) $it['image_url'] = $it['image'] ? 'assets/uploads/'.$it['image'] : '';
        echo json_encode(['success'=>true,'data'=>$items]); break;

    case 'get_item':
        $id = (int)($_GET['id']??0);
        $s=$db->prepare("SELECT * FROM items WHERE id=?"); $s->execute([$id]);
        $item = $s->fetch();
        if ($item) $item['image_url'] = $item['image'] ? 'assets/uploads/'.$item['image'] : '';
        echo json_encode(['success'=>true,'data'=>$item]); break;

    case 'add_item':
    case 'update_item':
        $isUpdate = $action === 'update_item';
        $imageFile = '';
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $res = uploadImage($_FILES['image'], 'item');
            if (!$res['success']) { echo json_encode($res); exit; }
            $imageFile = $res['filename'];
            // Delete old image if update
            if ($isUpdate) {
                $old = $db->prepare("SELECT image FROM items WHERE id=?");
                $old->execute([(int)($_POST['id']??0)]);
                $oldImg = $old->fetchColumn();
                if ($oldImg && $imageFile) @unlink(UPLOADS_PATH.$oldImg);
            }
        }
        $fields = [
            trim($_POST['name_ar']??''), trim($_POST['name_en']??''),
            trim($_POST['description_ar']??''), trim($_POST['description_en']??''),
            trim($_POST['ingredients_ar']??''), trim($_POST['ingredients_en']??''),
            trim($_POST['features_ar']??''), trim($_POST['features_en']??''),
            (float)($_POST['price']??0), (int)($_POST['is_active']??1), (int)($_POST['category_id']??0)
        ];
        if ($isUpdate) {
            $id = (int)($_POST['id']??0);
            if ($imageFile) {
                $s=$db->prepare("UPDATE items SET name_ar=?,name_en=?,description_ar=?,description_en=?,ingredients_ar=?,ingredients_en=?,features_ar=?,features_en=?,price=?,is_active=?,category_id=?,image=? WHERE id=?");
                $s->execute(array_merge($fields,[$imageFile,$id]));
            } else {
                $s=$db->prepare("UPDATE items SET name_ar=?,name_en=?,description_ar=?,description_en=?,ingredients_ar=?,ingredients_en=?,features_ar=?,features_en=?,price=?,is_active=?,category_id=? WHERE id=?");
                $s->execute(array_merge($fields,[$id]));
            }
        } else {
            $max=$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM items WHERE category_id=".(int)($_POST['category_id']??0))->fetchColumn();
            $s=$db->prepare("INSERT INTO items (name_ar,name_en,description_ar,description_en,ingredients_ar,ingredients_en,features_ar,features_en,price,is_active,category_id,image,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $s->execute(array_merge($fields,[$imageFile,$max]));
        }
        echo json_encode(['success'=>true,'id'=>$isUpdate?(int)($_POST['id']??0):$db->lastInsertId()]); break;

    case 'delete_item':
        $id=(int)($_POST['id']??0);
        $img=$db->prepare("SELECT image FROM items WHERE id=?"); $img->execute([$id]);
        $imgFile=$img->fetchColumn(); if($imgFile) @unlink(UPLOADS_PATH.$imgFile);
        $db->prepare("DELETE FROM items WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]); break;

    // ── OFFERS
    case 'get_offers':
        $rows=$db->query("SELECT o.*,i.name_ar as item_name FROM offers o LEFT JOIN items i ON o.item_id=i.id ORDER BY o.sort_order,o.id")->fetchAll();
        foreach ($rows as &$r) { $r['image_url']=$r['image']?'assets/uploads/'.$r['image']:''; $r['discount']=round((1-$r['offer_price']/$r['original_price'])*100); }
        echo json_encode(['success'=>true,'data'=>$rows]); break;

    case 'add_offer':
    case 'update_offer':
        $isUpdate = $action==='update_offer';
        $imageFile='';
        if (!empty($_FILES['image']['name'])) {
            $res=uploadImage($_FILES['image'],'offer');
            if (!$res['success']){echo json_encode($res);exit;}
            $imageFile=$res['filename'];
            if ($isUpdate) { $old=$db->prepare("SELECT image FROM offers WHERE id=?"); $old->execute([(int)($_POST['id']??0)]); $oldImg=$old->fetchColumn(); if($oldImg&&$imageFile) @unlink(UPLOADS_PATH.$oldImg); }
        }
        $fields=[trim($_POST['title_ar']??''),trim($_POST['title_en']??''),(float)($_POST['original_price']??0),(float)($_POST['offer_price']??0),(int)($_POST['item_id']??0)??null,(int)($_POST['is_active']??1)];
        if ($isUpdate) {
            $id=(int)($_POST['id']??0);
            if ($imageFile) { $s=$db->prepare("UPDATE offers SET title_ar=?,title_en=?,original_price=?,offer_price=?,item_id=?,is_active=?,image=? WHERE id=?"); $s->execute(array_merge($fields,[$imageFile,$id])); }
            else { $s=$db->prepare("UPDATE offers SET title_ar=?,title_en=?,original_price=?,offer_price=?,item_id=?,is_active=? WHERE id=?"); $s->execute(array_merge($fields,[$id])); }
        } else {
            $max=$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM offers")->fetchColumn();
            $s=$db->prepare("INSERT INTO offers (title_ar,title_en,original_price,offer_price,item_id,is_active,image,sort_order) VALUES (?,?,?,?,?,?,?,?)");
            $s->execute(array_merge($fields,[$imageFile,$max]));
        }
        echo json_encode(['success'=>true]); break;

    case 'delete_offer':
        $id=(int)($_POST['id']??0);
        $img=$db->prepare("SELECT image FROM offers WHERE id=?"); $img->execute([$id]);
        $imgFile=$img->fetchColumn(); if($imgFile) @unlink(UPLOADS_PATH.$imgFile);
        $db->prepare("DELETE FROM offers WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]); break;

    // ── SETTINGS
    case 'update_settings':
        $allowed=['restaurant_name_ar','restaurant_name_en','slogan_ar','slogan_en','contact_phone','contact_whatsapp','contact_facebook','contact_instagram','contact_show','telegram_bot_token','telegram_chat_id'];
        $s=$db->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES (?,?)");
        foreach ($allowed as $k) if (isset($_POST[$k])) $s->execute([$k,trim($_POST[$k])]);
        if (!empty($_POST['new_password'])&&!empty($_POST['current_password'])) {
            $sh=$db->query("SELECT value FROM settings WHERE key='admin_password'")->fetchColumn();
            if (password_verify($_POST['current_password'],$sh)) $db->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES ('admin_password',?)")->execute([password_hash($_POST['new_password'],PASSWORD_DEFAULT)]);
            else { echo json_encode(['success'=>false,'message'=>'كلمة المرور الحالية غير صحيحة']); exit; }
        }
        echo json_encode(['success'=>true]); break;

    // ── ORDERS
    case 'get_orders':
        $status = trim($_GET['status'] ?? 'all');
        if ($status === 'all') {
            $rows = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 200")->fetchAll();
        } else {
            $s = $db->prepare("SELECT * FROM orders WHERE status=? ORDER BY created_at DESC LIMIT 200");
            $s->execute([$status]);
            $rows = $s->fetchAll();
        }
        echo json_encode(['success'=>true,'data'=>$rows]); break;

    // ── TELEGRAM TEST
    case 'test_telegram':
        $token  = getSetting($db,'telegram_bot_token');
        $chatId = getSetting($db,'telegram_chat_id');
        if (!$token || !$chatId) { echo json_encode(['success'=>false,'message'=>'يرجى إدخال التوكن والـ Chat ID أولاً']); break; }
        $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,
            CURLOPT_POSTFIELDS=>['chat_id'=>$chatId,'text'=>'✅ البوت يعمل بشكل صحيح — Mr. Donar']]);
        $r = json_decode(curl_exec($ch),true); curl_close($ch);
        echo json_encode(['success'=>!empty($r['ok']),'message'=>$r['description']??'']); break;

    // ── REGISTER WEBHOOK
    case 'check_webhook':
        $token = getSetting($db,'telegram_bot_token');
        if (!$token) { echo json_encode(['success'=>false,'message'=>'لم يتم إدخال التوكن']); break; }
        $ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8]);
        $r = json_decode(curl_exec($ch),true); curl_close($ch);
        echo json_encode(['success'=>!empty($r['ok']),'webhook_info'=>$r['result']??null,'message'=>$r['description']??'']); break;

    case 'register_webhook':
        $token = getSetting($db,'telegram_bot_token');
        if (!$token) { echo json_encode(['success'=>false,'message'=>'يرجى إدخال التوكن أولاً']); break; }
        // ✅ استخدام الرابط المُرسَل من لوحة التحكم
        $webhookUrl = trim($_POST['webhook_url'] ?? '');
        if (!$webhookUrl || !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(['success'=>false,'message'=>'يرجى إدخال رابط الـ Webhook (ngrok URL)']); break;
        }
        $webhookUrl = rtrim($webhookUrl,'/') . '/api/telegram.php';
        $secret = getSetting($db,'telegram_webhook_secret');
        $ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
        $payload = ['url'=>$webhookUrl];
        if ($secret) $payload['secret_token'] = $secret;
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,
            CURLOPT_POSTFIELDS=>$payload]);
        $r = json_decode(curl_exec($ch),true); curl_close($ch);
        echo json_encode(['success'=>!empty($r['ok']),'url'=>$webhookUrl,'message'=>$r['description']??'']); break;

    // ── SOCIAL LINKS
    case 'get_social_links':
        $rows = $db->query("SELECT * FROM social_links ORDER BY sort_order,id")->fetchAll();
        foreach ($rows as &$r) {
            $r['icon_url'] = ($r['icon_type']==='upload' && $r['icon_value']) ? 'assets/icons/'.$r['icon_value'] : '';
        }
        echo json_encode(['success'=>true,'data'=>$rows]); break;

    case 'add_social_link':
    case 'update_social_link':
        $isUpdate = $action === 'update_social_link';
        $iconValue = trim($_POST['icon_value'] ?? '');
        $iconType  = trim($_POST['icon_type'] ?? 'svg');
        if (!empty($_FILES['icon_file']['name'])) {
            $res = uploadIcon($_FILES['icon_file']);
            if (!$res['success']) { echo json_encode($res); exit; }
            if ($isUpdate && $iconType === 'upload') {
                $old = $db->prepare("SELECT icon_value FROM social_links WHERE id=?");
                $old->execute([(int)($_POST['id']??0)]);
                $oldIcon = $old->fetchColumn();
                if ($oldIcon) @unlink(ICONS_PATH.$oldIcon);
            }
            $iconValue = $res['filename'];
            $iconType  = 'upload';
        }
        $fields = [
            trim($_POST['label'] ?? ''),
            trim($_POST['url'] ?? ''),
            $iconType,
            $iconValue,
            trim($_POST['color'] ?? '#ffffff'),
            (int)($_POST['is_active'] ?? 1),
        ];
        if ($isUpdate) {
            $id = (int)($_POST['id']??0);
            $s = $db->prepare("UPDATE social_links SET label=?,url=?,icon_type=?,icon_value=?,color=?,is_active=? WHERE id=?");
            $s->execute(array_merge($fields,[$id]));
        } else {
            $max = $db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM social_links")->fetchColumn();
            $s = $db->prepare("INSERT INTO social_links (label,url,icon_type,icon_value,color,is_active,sort_order) VALUES (?,?,?,?,?,?,?)");
            $s->execute(array_merge($fields,[$max]));
        }
        echo json_encode(['success'=>true]); break;

    case 'delete_social_link':
        $id = (int)($_POST['id']??0);
        $row = $db->prepare("SELECT icon_type,icon_value FROM social_links WHERE id=?");
        $row->execute([$id]);
        $r = $row->fetch();
        if ($r && $r['icon_type']==='upload' && $r['icon_value']) @unlink(ICONS_PATH.$r['icon_value']);
        $db->prepare("DELETE FROM social_links WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]); break;

    case 'reorder_social_links':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $s = $db->prepare("UPDATE social_links SET sort_order=? WHERE id=?");
        foreach ($ids as $i => $id) $s->execute([$i, (int)$id]);
        echo json_encode(['success'=>true]); break;

    default: echo json_encode(['success'=>false,'message'=>'Unknown action']);
}

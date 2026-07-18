<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../database.php';
$db = getDB();
$settings = [];
foreach ($db->query("SELECT key, value FROM settings")->fetchAll() as $row)
    $settings[$row['key']] = $row['value'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>لوحة التحكم - MR. DONAR</title>
<link rel="icon" href="../assets/images/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
:root{--gold:#C9A84C;--gold2:#E8C97A;--card:linear-gradient(145deg,#1e1b16,#141210);--border:rgba(201,168,76,.2);--radius:14px;--tr:.25s ease}
body{background:#0d0d0d;color:#D3C9B5;font-family:'Cairo',sans-serif;min-height:100vh;display:flex;flex-direction:column}

/* HEADER */
.adm-header{background:#111;border-bottom:1px solid var(--border);padding:0 1.25rem;position:sticky;top:0;z-index:100}
.adm-header-inner{max-width:1200px;margin:0 auto;height:58px;display:flex;align-items:center;justify-content:space-between;gap:1rem}
.adm-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.adm-logo img{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--gold)}
.adm-logo span{font-size:15px;font-weight:700;color:var(--gold2);letter-spacing:1px}
.adm-nav{display:flex;gap:4px;overflow-x:auto;padding:4px 0}
.adm-nav::-webkit-scrollbar{display:none}
.nav-tab{background:transparent;border:1px solid transparent;color:rgba(200,170,100,.5);border-radius:8px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Cairo',sans-serif;white-space:nowrap;transition:all var(--tr)}
.nav-tab:hover{background:rgba(201,168,76,.1);color:var(--gold2)}
.nav-tab.active{background:rgba(201,168,76,.15);border-color:rgba(201,168,76,.35);color:var(--gold2)}
.logout-btn{background:rgba(200,80,80,.12);border:1px solid rgba(200,80,80,.2);color:#e57373;border-radius:8px;padding:6px 12px;font-size:13px;cursor:pointer;font-family:'Cairo',sans-serif;white-space:nowrap;transition:all var(--tr)}
.logout-btn:hover{background:rgba(200,80,80,.22)}

/* MAIN */
.adm-main{flex:1;max-width:1200px;width:100%;margin:0 auto;padding:1.5rem 1.25rem 3rem}
.section{display:none}.section.active{display:block}
.sec-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem}
.sec-title{font-size:1.2rem;font-weight:700;color:var(--gold2)}
.btn-add{background:var(--gold);color:#111;border:none;border-radius:9px;padding:8px 18px;font-size:13px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:opacity var(--tr)}
.btn-add:hover{opacity:.85}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:1.5rem}
.stat{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem;text-align:center}
.stat-n{font-size:2rem;font-weight:700;color:var(--gold2);line-height:1}
.stat-l{font-size:12px;color:rgba(200,170,100,.5);margin-top:5px}

/* ═══ CARDS GRID — mobile-first ═══ */
.cards-grid{display:grid;grid-template-columns:1fr;gap:12px}
@media(min-width:640px){.cards-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:960px){.cards-grid{grid-template-columns:repeat(3,1fr)}}

/* generic card */
.mgmt-card{background:linear-gradient(145deg,#1e1b16,#141210);border:1px solid var(--border);border-radius:14px;padding:14px 16px;display:flex;flex-direction:column;gap:8px;transition:border-color .2s}
.mgmt-card:hover{border-color:rgba(201,168,76,.4)}
.mgmt-card-head{display:flex;align-items:center;gap:10px}
.mgmt-card-thumb{width:48px;height:48px;border-radius:9px;object-fit:cover;border:1px solid var(--border);flex-shrink:0}
.mgmt-card-thumb-placeholder{width:48px;height:48px;border-radius:9px;background:rgba(201,168,76,.09);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.mgmt-card-title{font-size:14px;font-weight:700;color:#E8C97A;line-height:1.3}
.mgmt-card-sub{font-size:12px;color:rgba(200,170,100,.5);margin-top:2px}
.mgmt-card-body{font-size:13px;color:rgba(200,170,100,.65);line-height:1.7}
.mgmt-card-body span{color:var(--gold);font-weight:600}
.mgmt-card-foot{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-top:2px}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-on{background:rgba(99,180,80,.15);color:#6ab44a}
.badge-off{background:rgba(200,80,80,.12);color:#c05050}
.act{display:flex;gap:6px;flex-wrap:wrap}
.btn-e,.btn-d{border:none;border-radius:7px;padding:6px 14px;font-size:12px;cursor:pointer;font-family:'Cairo',sans-serif;font-weight:600;transition:opacity .2s}
.btn-e{background:rgba(201,168,76,.15);color:var(--gold)}
.btn-d{background:rgba(200,80,80,.12);color:#e57373}
.btn-e:hover,.btn-d:hover{opacity:.75}

/* order cards */
.order-card{background:linear-gradient(145deg,rgba(212,175,55,.06),rgba(212,175,55,.02));border:1px solid rgba(212,175,55,.18);border-radius:14px;padding:14px 16px;margin-bottom:12px;transition:border-color .2s}
.order-card:hover{border-color:rgba(212,175,55,.4)}
.order-card-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;gap:8px;flex-wrap:wrap}
.order-num{font-weight:800;color:var(--gold);font-size:14px;letter-spacing:.5px}
.order-badge{border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;white-space:nowrap}
.order-customer{font-size:14px;font-weight:600;margin-bottom:4px}
.order-phone{font-size:12px;color:rgba(200,170,100,.5)}
.order-type-badge{font-size:12px;color:var(--gold);background:rgba(212,175,55,.1);display:inline-block;padding:3px 10px;border-radius:20px;margin-top:5px}
.order-items{background:rgba(0,0,0,.2);border-radius:8px;padding:8px 12px;margin:8px 0;font-size:13px;color:rgba(200,170,100,.7);line-height:1.8}
.order-footer{display:flex;justify-content:space-between;align-items:center;margin-top:10px;flex-wrap:wrap;gap:6px}
.order-total{font-weight:800;color:var(--gold);font-size:15px}
.order-time{font-size:11px;color:rgba(200,170,100,.4)}
.order-notes{font-size:12px;color:rgba(200,170,100,.45);margin-top:4px;font-style:italic}
.orders-empty{text-align:center;padding:3rem 1rem;color:rgba(200,170,100,.3);font-size:14px}

/* thumbnail */
.thumb{width:42px;height:42px;border-radius:8px;object-fit:cover;border:1px solid var(--border)}
.thumb-placeholder{width:42px;height:42px;border-radius:8px;background:rgba(201,168,76,.1);display:flex;align-items:center;justify-content:center;font-size:18px}
table{width:100%;border-collapse:collapse;background:linear-gradient(145deg,#1a1713,#111)}
th{background:#111;color:rgba(200,170,100,.6);padding:11px 14px;text-align:right;font-size:13px;font-weight:500;white-space:nowrap}
td{padding:11px 14px;border-bottom:1px solid rgba(201,168,76,.08);font-size:13px;color:#C9B89A;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(201,168,76,.04)}
.thumb{width:42px;height:42px;border-radius:8px;object-fit:cover;border:1px solid var(--border)}
.thumb-placeholder{width:42px;height:42px;border-radius:8px;background:rgba(201,168,76,.1);display:flex;align-items:center;justify-content:center;font-size:18px}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-on{background:rgba(99,180,80,.15);color:#6ab44a}
.badge-off{background:rgba(200,80,80,.12);color:#c05050}
.act{display:flex;gap:6px;flex-wrap:wrap}
.btn-e,.btn-d{border:none;border-radius:7px;padding:5px 13px;font-size:12px;cursor:pointer;font-family:'Cairo',sans-serif;font-weight:600;transition:opacity var(--tr)}
.btn-e{background:rgba(201,168,76,.15);color:var(--gold)}
.btn-d{background:rgba(200,80,80,.12);color:#e57373}
.btn-e:hover,.btn-d:hover{opacity:.75}

/* MODAL */
.modal-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:500;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(4px)}
.modal-ov.open{display:flex}
.modal{background:linear-gradient(145deg,#1e1b16,#141210);border:1px solid var(--border);border-radius:20px;padding:1.75rem;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.6)}
.modal-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem}
.modal-title{font-size:1.1rem;font-weight:700;color:var(--gold2)}
.modal-close{background:rgba(201,168,76,.1);border:1px solid var(--border);color:var(--gold);width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;transition:all var(--tr)}
.modal-close:hover{background:rgba(201,168,76,.22)}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
@media(max-width:480px){.grid2{grid-template-columns:1fr}}
.fg{margin-bottom:.9rem}
.fg label{display:block;margin-bottom:5px;font-size:12px;color:rgba(200,170,100,.6);font-weight:500}
.fg input,.fg select,.fg textarea{width:100%;background:rgba(201,168,76,.06);border:1px solid rgba(201,168,76,.18);border-radius:9px;padding:9px 13px;color:#E8C97A;font-size:13px;font-family:'Cairo',sans-serif;outline:none;transition:border-color var(--tr)}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:rgba(201,168,76,.42)}
.fg input::placeholder,.fg textarea::placeholder{color:rgba(201,168,76,.22)}
.fg select option{background:#1e1b16;color:#E8C97A}
.fg textarea{resize:vertical;min-height:68px;line-height:1.5}
.img-preview{width:100%;height:150px;object-fit:cover;border-radius:10px;border:1px solid var(--border);margin-bottom:.75rem;display:none}
.img-prev-placeholder{width:100%;height:100px;border-radius:10px;border:1px dashed rgba(201,168,76,.25);display:flex;align-items:center;justify-content:center;color:rgba(201,168,76,.35);font-size:13px;margin-bottom:.75rem;cursor:pointer}
.img-prev-placeholder:hover{border-color:rgba(201,168,76,.5);color:rgba(201,168,76,.6)}
.save-btn{width:100%;padding:11px;background:linear-gradient(135deg,#C9A84C,#8B6914);border:none;border-radius:10px;color:#111;font-size:15px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:opacity var(--tr),transform .1s;margin-top:.5rem}
.save-btn:hover{opacity:.88}
.save-btn:active{transform:scale(.98)}
.save-btn:disabled{opacity:.45;cursor:not-allowed}

/* SETTINGS */
.settings-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.25rem}
.settings-title{font-size:14px;font-weight:700;color:rgba(201,168,76,.7);margin-bottom:1rem;padding-bottom:.6rem;border-bottom:1px solid rgba(201,168,76,.1)}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);background:#1e1b16;color:#E8C97A;border:1px solid rgba(201,168,76,.35);border-radius:24px;padding:10px 24px;font-size:13px;z-index:9999;transition:transform .35s cubic-bezier(.34,1.56,.64,1);white-space:nowrap;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast.err{color:#e57373;border-color:rgba(200,80,80,.3)}

/* FILTER */
.filter-bar{display:flex;gap:8px;margin-bottom:1rem;flex-wrap:wrap}
.filter-bar select{background:rgba(201,168,76,.07);border:1px solid rgba(201,168,76,.2);border-radius:9px;color:var(--gold2);padding:8px 12px;font-size:13px;font-family:'Cairo',sans-serif;cursor:pointer;outline:none}

/* VIEW SITE LINK */
.view-site{display:inline-flex;align-items:center;gap:6px;color:rgba(200,170,100,.5);font-size:13px;text-decoration:none;padding:6px 12px;border:1px solid rgba(201,168,76,.15);border-radius:8px;transition:all var(--tr)}
.view-site:hover{color:var(--gold);border-color:rgba(201,168,76,.35)}
</style>
</head>
<body>

<!-- HEADER -->
<header class="adm-header">
  <div class="adm-header-inner">
    <a class="adm-logo" href="#">
      <img src="../assets/images/logo.jpg" alt="logo">
      <span>MR. DONAR</span>
    </a>
    <nav class="adm-nav">
      <button class="nav-tab active" onclick="showSec('dashboard',this)">📊 الرئيسية</button>
      <button class="nav-tab" onclick="showSec('cats',this)">📂 الفئات</button>
      <button class="nav-tab" onclick="showSec('items',this)">🍽️ الوجبات</button>
      <button class="nav-tab" onclick="showSec('offers',this)">🔥 العروض</button>
      <button class="nav-tab" onclick="showSec('orders',this)">📋 الطلبات</button>
      <button class="nav-tab" onclick="showSec('links',this)">🔗 روابط الرئيسية</button>
      <button class="nav-tab" onclick="showSec('settings',this)">⚙️ الإعدادات</button>
    </nav>
    <div style="display:flex;gap:8px;align-items:center;flex-shrink:0">
      <a class="view-site" href="../index.php" target="_blank">🌐 الموقع</a>
      <button class="logout-btn" onclick="doLogout()">خروج</button>
    </div>
  </div>
</header>

<!-- MAIN -->
<main class="adm-main">

  <!-- DASHBOARD -->
  <div id="sec-dashboard" class="section active">
    <div class="sec-header"><span class="sec-title">لوحة التحكم الرئيسية</span></div>
    <div class="stats" id="stats"></div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem">
      <p style="color:rgba(200,170,100,.6);font-size:14px">مرحباً، <strong style="color:var(--gold2)"><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></strong> 👋</p>
      <p style="color:rgba(200,170,100,.4);font-size:13px;margin-top:6px">استخدم القائمة أعلى لإدارة محتوى المطعم.</p>
    </div>
  </div>

  <!-- CATEGORIES -->
  <div id="sec-cats" class="section">
    <div class="sec-header">
      <span class="sec-title">إدارة الفئات</span>
      <button class="btn-add" onclick="openCatModal()">+ إضافة فئة</button>
    </div>
    <div id="cats-cards" class="cards-grid"></div>
  </div>

  <!-- ITEMS -->
  <div id="sec-items" class="section">
    <div class="sec-header">
      <span class="sec-title">إدارة الوجبات</span>
      <button class="btn-add" onclick="openItemModal()">+ إضافة وجبة</button>
    </div>
    <div class="filter-bar">
      <select id="item-filter" onchange="loadItems()">
        <option value="">كل الفئات</option>
      </select>
    </div>
    <div id="items-cards" class="cards-grid"></div>
  </div>

  <!-- OFFERS -->
  <div id="sec-offers" class="section">
    <div class="sec-header">
      <span class="sec-title">إدارة العروض</span>
      <button class="btn-add" onclick="openOfferModal()">+ إضافة عرض</button>
    </div>
    <div id="offers-cards" class="cards-grid"></div>
  </div>

  <!-- SOCIAL LINKS -->
  <div id="sec-links" class="section">
    <div class="sec-header">
      <span class="sec-title">روابط الصفحة الرئيسية</span>
      <button class="btn-add" onclick="openLinkModal()">+ إضافة رابط</button>
    </div>
    <p style="color:rgba(200,170,100,.45);font-size:13px;margin-bottom:1rem">
      هذه الروابط تظهر كأيقونات دائرية في الصفحة الرئيسية (mr-donar.com). الترتيب يتحكم بموضع الأيقونة.
    </p>
    <div id="links-cards" class="cards-grid"></div>
  </div>

  <!-- SETTINGS -->
  <div id="sec-settings" class="section">
    <div class="sec-header"><span class="sec-title">الإعدادات</span></div>
    <div style="max-width:640px">
      <div class="settings-card">
        <div class="settings-title">معلومات المطعم</div>
        <div class="grid2">
          <div class="fg"><label>اسم المطعم (عربي)</label><input type="text" id="s-name-ar" value="<?= htmlspecialchars($settings['restaurant_name_ar']??'') ?>"></div>
          <div class="fg"><label>Restaurant Name (English)</label><input type="text" id="s-name-en" value="<?= htmlspecialchars($settings['restaurant_name_en']??'') ?>"></div>
          <div class="fg"><label>الشعار (عربي)</label><input type="text" id="s-slogan-ar" value="<?= htmlspecialchars($settings['slogan_ar']??'') ?>"></div>
          <div class="fg"><label>Slogan (English)</label><input type="text" id="s-slogan-en" value="<?= htmlspecialchars($settings['slogan_en']??'') ?>"></div>
        </div>
      </div>
      <div class="settings-card">
        <div class="settings-title">معلومات التواصل</div>
        <div class="fg"><label>رقم الواتساب (مع رمز الدولة مثال: 963991234567)</label><input type="text" id="s-wa" value="<?= htmlspecialchars($settings['contact_whatsapp']??'') ?>" placeholder="963991234567"></div>
        <div class="fg"><label>رقم الهاتف للاتصال</label><input type="text" id="s-phone" value="<?= htmlspecialchars($settings['contact_phone']??'') ?>" placeholder="+963 99 123 4567"></div>
        <div class="fg"><label>رابط صفحة فيسبوك</label><input type="text" id="s-fb" value="<?= htmlspecialchars($settings['contact_facebook']??'') ?>" placeholder="https://facebook.com/..."></div>
        <div class="fg"><label>رابط حساب انستغرام</label><input type="text" id="s-ig" value="<?= htmlspecialchars($settings['contact_instagram']??'') ?>" placeholder="https://instagram.com/..."></div>
        <div class="fg"><label>إظهار شريط التواصل</label>
          <select id="s-contact-show">
            <option value="1" <?= ($settings['contact_show']??'1')==='1'?'selected':'' ?>>نعم - إظهار</option>
            <option value="0" <?= ($settings['contact_show']??'1')==='0'?'selected':'' ?>>لا - إخفاء</option>
          </select>
        </div>
      </div>
      <div class="settings-card">
        <div class="settings-title">تغيير كلمة المرور</div>
        <div class="grid2">
          <div class="fg"><label>كلمة المرور الحالية</label><input type="password" id="s-cur-pass" placeholder="اتركها فارغة إذا لا تريد التغيير"></div>
          <div class="fg"><label>كلمة المرور الجديدة</label><input type="password" id="s-new-pass" placeholder="كلمة المرور الجديدة"></div>
        </div>
      </div>

      <!-- بوت تيليغرام -->
      <div class="settings-card">
        <div class="settings-title">🤖 بوت تيليغرام</div>
        <p style="color:rgba(200,170,100,.45);font-size:13px;margin-bottom:1rem;line-height:1.7">
          أنشئ بوتاً من <b>@BotFather</b> على تيليغرام واحصل على التوكن، ثم أرسل رسالة للبوت أو أضفه لمجموعة واحصل على الـ Chat ID.
        </p>
        <div class="fg">
          <label>Bot Token</label>
          <input type="text" id="s-bot-token" value="<?= htmlspecialchars($settings['telegram_bot_token']??'') ?>" placeholder="123456789:AAF...">
        </div>
        <div class="fg">
          <label>Chat ID (معرّف المحادثة أو المجموعة)</label>
          <input type="text" id="s-chat-id" value="<?= htmlspecialchars($settings['telegram_chat_id']??'') ?>" placeholder="-100123456789">
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px">
          <button class="save-btn" onclick="saveBotSettings()" style="max-width:200px">💾 حفظ إعدادات البوت</button>
          <button class="save-btn" onclick="testBot()" style="max-width:180px;background:rgba(37,211,102,.15);color:#25d366;border:1px solid rgba(37,211,102,.3)">🧪 اختبار البوت</button>
        </div>
        <div class="fg" style="margin-top:14px">
          <label style="margin-bottom:6px;display:block">رابط الموقع / ngrok لتسجيل الـ Webhook</label>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="text" id="s-webhook-url" placeholder="https://xxxx.ngrok-free.app  أو  https://mr-donar.com" style="direction:ltr;flex:1;min-width:200px">
            <button class="save-btn" onclick="registerWebhook()" style="max-width:180px;background:rgba(33,150,243,.15);color:#64b5f6;border:1px solid rgba(33,150,243,.3);flex-shrink:0">🔗 تسجيل Webhook</button>
          <button class="save-btn" onclick="checkWebhook()" style="max-width:160px;background:rgba(255,193,7,.1);color:#ffc107;border:1px solid rgba(255,193,7,.3);flex-shrink:0">🔍 فحص الحالة</button>
          </div>
          <span style="font-size:11px;color:rgba(200,170,100,.35);display:block;margin-top:5px">سيُضاف /api/telegram.php للرابط تلقائياً</span>
        </div>
        <div id="bot-test-result" style="margin-top:10px;font-size:13px;display:none"></div>
      </div>

      <button class="save-btn" onclick="saveSettings()" style="max-width:220px">💾 حفظ الإعدادات</button>
    </div>
  </div>

  <!-- قسم الطلبات -->
  <div id="sec-orders" class="section">
    <div class="sec-header">
      <span class="sec-title">الطلبات <span id="orders-count-badge" style="background:rgba(212,175,55,.15);border:1px solid rgba(212,175,55,.3);border-radius:20px;padding:2px 10px;font-size:12px;margin-right:6px"></span></span>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <select id="orders-filter" onchange="loadOrders()" style="background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);color:var(--gold);border-radius:8px;padding:6px 12px;font-family:'Cairo',sans-serif;font-size:13px">
          <option value="all">جميع الطلبات</option>
          <option value="pending">⏳ بانتظار الرد</option>
          <option value="accepted">✅ مقبولة</option>
          <option value="preparing">🟠 قيد التحضير</option>
          <option value="ready">🟢 جاهزة</option>
          <option value="delivered">✔️ مُسلَّمة</option>
          <option value="rejected">❌ مرفوضة</option>
        </select>
        <button class="btn-add" onclick="loadOrders()" style="padding:6px 14px">🔄</button>
      </div>
    </div>
    <p style="font-size:12px;color:rgba(200,170,100,.4);margin-bottom:12px">⏱ تُحذف الطلبات تلقائياً بعد 7 أيام</p>
    <div id="orders-cards"></div>
  </div>

</main>

<!-- CAT MODAL -->
<div class="modal-ov" id="cat-modal" onclick="if(event.target===this)closeMod('cat-modal')">
<div class="modal">
  <div class="modal-hd"><span class="modal-title" id="cat-mod-title">إضافة فئة</span><button class="modal-close" onclick="closeMod('cat-modal')">✕</button></div>
  <input type="hidden" id="cm-id">
  <div class="grid2">
    <div class="fg"><label>الاسم (عربي) *</label><input type="text" id="cm-ar" placeholder="مثال: الفطور"></div>
    <div class="fg"><label>Name (English) *</label><input type="text" id="cm-en" placeholder="e.g. Breakfast"></div>
    <div class="fg"><label>وصف (عربي)</label><input type="text" id="cm-dar" placeholder="وصف مختصر"></div>
    <div class="fg"><label>Description (English)</label><input type="text" id="cm-den" placeholder="Short description"></div>
    <div class="fg"><label>أيقونة Emoji</label><input type="text" id="cm-icon" placeholder="🍽️" maxlength="4"></div>
    <div class="fg"><label>الحالة</label><select id="cm-active"><option value="1">نشط ✓</option><option value="0">مخفي</option></select></div>
  </div>
  <button class="save-btn" id="cm-save" onclick="saveCat()">حفظ الفئة</button>
</div></div>

<!-- ITEM MODAL -->
<div class="modal-ov" id="item-modal" onclick="if(event.target===this)closeMod('item-modal')">
<div class="modal">
  <div class="modal-hd"><span class="modal-title" id="im-title">إضافة وجبة</span><button class="modal-close" onclick="closeMod('item-modal')">✕</button></div>
  <input type="hidden" id="im-id">
  <div class="fg"><label>الفئة *</label><select id="im-cat"></select></div>
  <div class="grid2">
    <div class="fg"><label>الاسم (عربي) *</label><input type="text" id="im-ar" placeholder="مثال: بيض بفطر وجبنة"></div>
    <div class="fg"><label>Name (English) *</label><input type="text" id="im-en" placeholder="e.g. Eggs with Mushroom"></div>
  </div>
  <div class="fg"><label>السعر (ل.س) *</label><input type="number" id="im-price" placeholder="مثال: 4500" min="0" step="50"></div>
  <div class="fg"><label>وصف (عربي)</label><textarea id="im-dar" placeholder="وصف الوجبة بالعربية"></textarea></div>
  <div class="fg"><label>Description (English)</label><textarea id="im-den" placeholder="Item description in English"></textarea></div>
  <div class="fg"><label>المكونات (عربي) - افصل بفاصلة</label><input type="text" id="im-ingr-ar" placeholder="فطر، جبنة، بيض، زبدة"></div>
  <div class="fg"><label>Ingredients (English) - separate by comma</label><input type="text" id="im-ingr-en" placeholder="Mushrooms, Cheese, Eggs, Butter"></div>
  <div class="fg"><label>المميزات (عربي) - افصل بفاصلة</label><input type="text" id="im-feat-ar" placeholder="غني بالبروتين، صحي، طازج"></div>
  <div class="fg"><label>Features (English)</label><input type="text" id="im-feat-en" placeholder="High protein, Healthy, Fresh"></div>
  <div class="fg">
    <label>صورة الوجبة</label>
    <img id="im-preview" class="img-preview" alt="preview">
    <div class="img-prev-placeholder" id="im-placeholder" onclick="document.getElementById('im-file').click()">📷 اضغط لاختيار صورة</div>
    <input type="file" id="im-file" accept="image/*" style="display:none" onchange="previewImg(this,'im-preview','im-placeholder')">
  </div>
  <div class="fg"><label>الحالة</label><select id="im-active"><option value="1">نشط ✓</option><option value="0">مخفي</option></select></div>
  <button class="save-btn" id="im-save" onclick="saveItem()">حفظ الوجبة</button>
</div></div>

<!-- OFFER MODAL -->
<div class="modal-ov" id="offer-modal" onclick="if(event.target===this)closeMod('offer-modal')">
<div class="modal">
  <div class="modal-hd"><span class="modal-title" id="om-title">إضافة عرض</span><button class="modal-close" onclick="closeMod('offer-modal')">✕</button></div>
  <input type="hidden" id="om-id">
  <div class="grid2">
    <div class="fg"><label>عنوان العرض (عربي) *</label><input type="text" id="om-ar" placeholder="مثال: عرض الدونر الذهبي"></div>
    <div class="fg"><label>Offer Title (English) *</label><input type="text" id="om-en" placeholder="e.g. Golden Donar Deal"></div>
    <div class="fg"><label>السعر الأصلي (ل.س) *</label><input type="number" id="om-orig" placeholder="10000" min="0" step="50" oninput="calcDiscount()"></div>
    <div class="fg"><label>سعر العرض (ل.س) *</label><input type="number" id="om-offer" placeholder="7500" min="0" step="50" oninput="calcDiscount()"></div>
  </div>
  <div class="fg" id="discount-preview" style="display:none">
    <div style="background:rgba(201,168,76,.1);border:1px solid rgba(201,168,76,.2);border-radius:9px;padding:10px 14px;font-size:14px;color:var(--gold2)">
      نسبة الخصم: <strong id="discount-val"></strong>
    </div>
  </div>
  <div class="fg"><label>الوجبة المرتبطة (اختياري)</label><select id="om-item"><option value="">-- اختر وجبة --</option></select></div>
  <div class="fg">
    <label>صورة العرض (اختياري - إذا تركته سيستخدم صورة الوجبة)</label>
    <img id="om-preview" class="img-preview" alt="preview">
    <div class="img-prev-placeholder" id="om-placeholder" onclick="document.getElementById('om-file').click()">📷 اضغط لاختيار صورة</div>
    <input type="file" id="om-file" accept="image/*" style="display:none" onchange="previewImg(this,'om-preview','om-placeholder')">
  </div>
  <div class="fg"><label>الحالة</label><select id="om-active"><option value="1">نشط ✓</option><option value="0">مخفي</option></select></div>
  <button class="save-btn" id="om-save" onclick="saveOffer()">حفظ العرض</button>
</div></div>

<!-- LINK MODAL -->
<div class="modal-ov" id="link-modal" onclick="if(event.target===this)closeMod('link-modal')">
<div class="modal">
  <div class="modal-hd"><span class="modal-title" id="lm-title">إضافة رابط</span><button class="modal-close" onclick="closeMod('link-modal')">✕</button></div>
  <input type="hidden" id="lm-id">
  <div class="grid2">
    <div class="fg"><label>التسمية (مثال: واتساب) *</label><input type="text" id="lm-label" placeholder="واتساب"></div>
    <div class="fg"><label>الرابط *</label><input type="text" id="lm-url" placeholder="https://wa.me/963..."></div>
    <div class="fg"><label>لون الأيقونة</label><input type="color" id="lm-color" value="#25d366" style="height:42px;padding:4px 8px;cursor:pointer"></div>
    <div class="fg"><label>الترتيب (رقم أصغر = أول)</label><input type="number" id="lm-order" value="0" min="0"></div>
  </div>
  <div class="fg">
    <label>نوع الأيقونة</label>
    <select id="lm-icon-type" onchange="toggleIconInput()">
      <option value="svg">كود SVG (للمطورين)</option>
      <option value="upload">رفع صورة/أيقونة (PNG/SVG)</option>
    </select>
  </div>
  <div id="lm-svg-wrap" class="fg">
    <label>كود SVG للأيقونة</label>
    <textarea id="lm-svg" rows="4" placeholder='<svg viewBox="0 0 24 24" width="20" height="20">...</svg>'></textarea>
    <p style="font-size:11px;color:rgba(201,168,76,.4);margin-top:4px">ابحث عن الأيقونة في fontawesome.com أو simpleicons.org وانسخ كود SVG</p>
  </div>
  <div id="lm-upload-wrap" class="fg" style="display:none">
    <label>ملف الأيقونة (PNG أو SVG — max 512KB)</label>
    <img id="lm-icon-preview" class="img-preview" alt="icon preview" style="height:80px;width:80px;border-radius:50%">
    <div class="img-prev-placeholder" id="lm-icon-placeholder" onclick="document.getElementById('lm-icon-file').click()">📷 اختر أيقونة</div>
    <input type="file" id="lm-icon-file" accept="image/png,image/svg+xml,image/webp,image/jpeg" style="display:none" onchange="previewImg(this,'lm-icon-preview','lm-icon-placeholder')">
  </div>
  <div class="fg"><label>الحالة</label><select id="lm-active"><option value="1">نشط ✓</option><option value="0">مخفي</option></select></div>
  <button class="save-btn" id="lm-save" onclick="saveLink()">حفظ الرابط</button>
</div></div>

<div class="toast" id="toast"></div>

<script>
let STATE = { cats: [], items: [], section: 'dashboard' };

// ── INIT
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
});

// ── NAV
function showSec(name, btn) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('sec-' + name).classList.add('active');
  btn.classList.add('active');
  STATE.section = name;
  if (name === 'dashboard') loadStats();
  else if (name === 'cats') loadCats();
  else if (name === 'items') loadItems();
  else if (name === 'offers') loadOffers();
  else if (name === 'orders') loadOrders();
  else if (name === 'links') loadLinks();
}

// ── ADMIN API
async function adm(params, files = null) {
  const fd = new FormData();
  Object.entries(params).forEach(([k, v]) => fd.append(k, v));
  if (files) Object.entries(files).forEach(([k, f]) => { if (f) fd.append(k, f); });
  const r = await fetch('../api/admin.php', { method: 'POST', body: fd });
  return r.json();
}
async function admGet(params) {
  const r = await fetch('../api/admin.php?' + new URLSearchParams(params));
  return r.json();
}

// ── TOAST
function toast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast ' + type;
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ── MODAL
function openMod(id) { document.getElementById(id).classList.add('open'); }
function closeMod(id) { document.getElementById(id).classList.remove('open'); }

// ── IMG PREVIEW
function previewImg(input, previewId, placeholderId) {
  if (!input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  const prev = document.getElementById(previewId);
  prev.src = url; prev.style.display = 'block';
  document.getElementById(placeholderId).style.display = 'none';
}
function resetImgPreview(previewId, placeholderId) {
  document.getElementById(previewId).style.display = 'none';
  document.getElementById(previewId).src = '';
  document.getElementById(placeholderId).style.display = 'flex';
}

// ── STATS
async function loadStats() {
  const [cres, ires, ores] = await Promise.all([admGet({action:'get_categories'}), admGet({action:'get_items'}), admGet({action:'get_offers'})]);
  const catCount = cres.data?.length || 0;
  const itemCount = ires.data?.length || 0;
  const offerCount = ores.data?.length || 0;
  const activeOffers = ores.data?.filter(o => o.is_active == 1).length || 0;
  document.getElementById('stats').innerHTML = `
    <div class="stat"><div class="stat-n">${catCount}</div><div class="stat-l">الفئات</div></div>
    <div class="stat"><div class="stat-n">${itemCount}</div><div class="stat-l">الوجبات</div></div>
    <div class="stat"><div class="stat-n">${offerCount}</div><div class="stat-l">العروض</div></div>
    <div class="stat"><div class="stat-n">${activeOffers}</div><div class="stat-l">عروض نشطة</div></div>
  `;
}

// ── CATEGORIES
async function loadCats() {
  const grid = document.getElementById('cats-cards');
  grid.innerHTML = `<div class="orders-empty">⏳ جاري التحميل...</div>`;
  const res = await admGet({ action: 'get_categories' });
  STATE.cats = res.data || [];
  if (!STATE.cats.length) { grid.innerHTML = `<div class="orders-empty">لا توجد فئات</div>`; return; }
  grid.innerHTML = STATE.cats.map(c => `
    <div class="mgmt-card">
      <div class="mgmt-card-head">
        <div class="mgmt-card-thumb-placeholder">${c.icon}</div>
        <div>
          <div class="mgmt-card-title">${c.name_ar}</div>
          <div class="mgmt-card-sub">${c.name_en}</div>
        </div>
      </div>
      <div class="mgmt-card-body">🍽️ <span>${c.item_count || 0}</span> وجبة</div>
      <div class="mgmt-card-foot">
        <span class="badge badge-${c.is_active==1?'on':'off'}">${c.is_active==1?'نشط':'مخفي'}</span>
        <div class="act">
          <button class="btn-e" onclick="editCat(${c.id})">✏️ تعديل</button>
          <button class="btn-d" onclick="delCat(${c.id},'${c.name_ar}')">🗑</button>
        </div>
      </div>
    </div>
  `).join('');
}

function openCatModal(cat=null) {
  document.getElementById('cat-mod-title').textContent = cat ? 'تعديل الفئة' : 'إضافة فئة';
  document.getElementById('cm-id').value = cat?.id || '';
  document.getElementById('cm-ar').value = cat?.name_ar || '';
  document.getElementById('cm-en').value = cat?.name_en || '';
  document.getElementById('cm-dar').value = cat?.description_ar || '';
  document.getElementById('cm-den').value = cat?.description_en || '';
  document.getElementById('cm-icon').value = cat?.icon || '🍽️';
  document.getElementById('cm-active').value = cat?.is_active ?? 1;
  openMod('cat-modal');
}
function editCat(id) { openCatModal(STATE.cats.find(c => c.id == id)); }

async function saveCat() {
  const id = document.getElementById('cm-id').value;
  const params = { action: id ? 'update_category' : 'add_category',
    name_ar: document.getElementById('cm-ar').value.trim(),
    name_en: document.getElementById('cm-en').value.trim(),
    description_ar: document.getElementById('cm-dar').value.trim(),
    description_en: document.getElementById('cm-den').value.trim(),
    icon: document.getElementById('cm-icon').value.trim() || '🍽️',
    is_active: document.getElementById('cm-active').value };
  if (id) params.id = id;
  if (!params.name_ar || !params.name_en) { toast('يرجى إدخال الاسم', 'err'); return; }
  const btn = document.getElementById('cm-save'); btn.disabled = true;
  const res = await adm(params); btn.disabled = false;
  if (res.success) { toast('تم الحفظ ✓'); closeMod('cat-modal'); loadCats(); loadStats(); }
  else toast(res.message || 'خطأ', 'err');
}

async function delCat(id, name) {
  if (!confirm(`حذف فئة "${name}"؟\nسيتم حذف جميع الوجبات داخلها.`)) return;
  const res = await adm({ action: 'delete_category', id });
  if (res.success) { toast('تم الحذف'); loadCats(); loadStats(); }
  else toast('خطأ', 'err');
}

// ── ITEMS
async function loadItems() {
  const grid   = document.getElementById('items-cards');
  const filter = document.getElementById('item-filter');
  grid.innerHTML = `<div class="orders-empty">⏳ جاري التحميل...</div>`;
  if (STATE.cats.length === 0) { const cr = await admGet({action:'get_categories'}); STATE.cats = cr.data||[]; }
  const cur = filter.value;
  filter.innerHTML = `<option value="">كل الفئات</option>` + STATE.cats.map(c => `<option value="${c.id}" ${cur==c.id?'selected':''}>${c.name_ar}</option>`).join('');
  const params = { action: 'get_items' }; if (filter.value) params.category_id = filter.value;
  const res = await admGet(params);
  STATE.items = res.data || [];
  if (!STATE.items.length) { grid.innerHTML = `<div class="orders-empty">لا توجد وجبات</div>`; return; }
  grid.innerHTML = STATE.items.map(item => `
    <div class="mgmt-card">
      <div class="mgmt-card-head">
        ${item.image_url
          ? `<img class="mgmt-card-thumb" src="../${item.image_url}" alt="">`
          : `<div class="mgmt-card-thumb-placeholder">${STATE.cats.find(c=>c.id==item.category_id)?.icon||'🍽️'}</div>`}
        <div>
          <div class="mgmt-card-title">${item.name_ar}</div>
          <div class="mgmt-card-sub">${item.name_en}</div>
        </div>
      </div>
      <div class="mgmt-card-body">
        💰 <span>${Number(item.price).toLocaleString('ar-SY')} ل.س</span>
        &nbsp;|&nbsp; 📂 ${item.cat_name_ar||''}
      </div>
      <div class="mgmt-card-foot">
        <span class="badge badge-${item.is_active==1?'on':'off'}">${item.is_active==1?'نشط':'مخفي'}</span>
        <div class="act">
          <button class="btn-e" onclick="editItem(${item.id})">✏️ تعديل</button>
          <button class="btn-d" onclick="delItem(${item.id},'${item.name_ar}')">🗑</button>
        </div>
      </div>
    </div>
  `).join('');
}

async function openItemModal(item=null) {
  if (STATE.cats.length === 0) { const cr = await admGet({action:'get_categories'}); STATE.cats = cr.data||[]; }
  document.getElementById('im-title').textContent = item ? 'تعديل الوجبة' : 'إضافة وجبة';
  document.getElementById('im-id').value = item?.id || '';
  document.getElementById('im-ar').value = item?.name_ar || '';
  document.getElementById('im-en').value = item?.name_en || '';
  document.getElementById('im-price').value = item?.price || '';
  document.getElementById('im-dar').value = item?.description_ar || '';
  document.getElementById('im-den').value = item?.description_en || '';
  document.getElementById('im-ingr-ar').value = item?.ingredients_ar || '';
  document.getElementById('im-ingr-en').value = item?.ingredients_en || '';
  document.getElementById('im-feat-ar').value = item?.features_ar || '';
  document.getElementById('im-feat-en').value = item?.features_en || '';
  document.getElementById('im-active').value = item?.is_active ?? 1;
  document.getElementById('im-cat').innerHTML = STATE.cats.map(c => `<option value="${c.id}" ${item?.category_id==c.id?'selected':''}>${c.name_ar}</option>`).join('');
  document.getElementById('im-file').value = '';
  if (item?.image_url) {
    document.getElementById('im-preview').src = '../' + item.image_url;
    document.getElementById('im-preview').style.display = 'block';
    document.getElementById('im-placeholder').style.display = 'none';
  } else { resetImgPreview('im-preview','im-placeholder'); }
  openMod('item-modal');
}
async function editItem(id) {
  const res = await admGet({ action: 'get_item', id });
  openItemModal(res.data);
}
async function saveItem() {
  const id = document.getElementById('im-id').value;
  const params = { action: id ? 'update_item' : 'add_item',
    name_ar: document.getElementById('im-ar').value.trim(),
    name_en: document.getElementById('im-en').value.trim(),
    description_ar: document.getElementById('im-dar').value.trim(),
    description_en: document.getElementById('im-den').value.trim(),
    ingredients_ar: document.getElementById('im-ingr-ar').value.trim(),
    ingredients_en: document.getElementById('im-ingr-en').value.trim(),
    features_ar: document.getElementById('im-feat-ar').value.trim(),
    features_en: document.getElementById('im-feat-en').value.trim(),
    price: document.getElementById('im-price').value,
    is_active: document.getElementById('im-active').value,
    category_id: document.getElementById('im-cat').value };
  if (id) params.id = id;
  if (!params.name_ar || !params.name_en || !params.price) { toast('يرجى ملء الحقول المطلوبة *', 'err'); return; }
  const imgFile = document.getElementById('im-file').files[0];
  const btn = document.getElementById('im-save'); btn.disabled = true; btn.textContent = 'جاري الرفع...';
  const res = await adm(params, imgFile ? { image: imgFile } : null);
  btn.disabled = false; btn.textContent = 'حفظ الوجبة';
  if (res.success) { toast('تم الحفظ ✓'); closeMod('item-modal'); loadItems(); loadStats(); }
  else toast(res.message || 'خطأ', 'err');
}
async function delItem(id, name) {
  if (!confirm(`حذف "${name}"؟`)) return;
  const res = await adm({ action: 'delete_item', id });
  if (res.success) { toast('تم الحذف'); loadItems(); loadStats(); }
  else toast('خطأ', 'err');
}

// ── OFFERS
async function loadOffers() {
  const grid = document.getElementById('offers-cards');
  grid.innerHTML = `<div class="orders-empty">⏳ جاري التحميل...</div>`;
  const res = await admGet({ action: 'get_offers' });
  const offers = res.data || [];
  if (!offers.length) { grid.innerHTML = `<div class="orders-empty">لا توجد عروض</div>`; return; }
  grid.innerHTML = offers.map(o => `
    <div class="mgmt-card">
      <div class="mgmt-card-head">
        ${o.image_url
          ? `<img class="mgmt-card-thumb" src="../${o.image_url}" alt="">`
          : `<div class="mgmt-card-thumb-placeholder">🔥</div>`}
        <div>
          <div class="mgmt-card-title">${o.title_ar}</div>
          <div class="mgmt-card-sub">${o.title_en}</div>
        </div>
      </div>
      <div class="mgmt-card-body">
        <span style="text-decoration:line-through;color:rgba(200,170,100,.35);font-size:12px">${Number(o.original_price).toLocaleString('ar-SY')} ل.س</span>
        &nbsp;→&nbsp; <span>${Number(o.offer_price).toLocaleString('ar-SY')} ل.س</span>
        &nbsp;&nbsp;<span style="color:#e74c3c">-${o.discount}%</span>
      </div>
      <div class="mgmt-card-foot">
        <span class="badge badge-${o.is_active==1?'on':'off'}">${o.is_active==1?'نشط':'مخفي'}</span>
        <div class="act">
          <button class="btn-e" onclick="editOffer(${o.id})">✏️ تعديل</button>
          <button class="btn-d" onclick="delOffer(${o.id},'${o.title_ar}')">🗑</button>
        </div>
      </div>
    </div>
  `).join('');
}

async function openOfferModal(offer=null) {
  if (STATE.cats.length === 0) { const cr = await admGet({action:'get_categories'}); STATE.cats = cr.data||[]; }
  const allItems = await admGet({action:'get_items'}); STATE.items = allItems.data||[];
  document.getElementById('om-title').textContent = offer ? 'تعديل العرض' : 'إضافة عرض';
  document.getElementById('om-id').value = offer?.id || '';
  document.getElementById('om-ar').value = offer?.title_ar || '';
  document.getElementById('om-en').value = offer?.title_en || '';
  document.getElementById('om-orig').value = offer?.original_price || '';
  document.getElementById('om-offer').value = offer?.offer_price || '';
  document.getElementById('om-active').value = offer?.is_active ?? 1;
  document.getElementById('om-item').innerHTML = `<option value="">-- اختر وجبة --</option>` + STATE.items.map(i => `<option value="${i.id}" ${offer?.item_id==i.id?'selected':''}>${i.name_ar}</option>`).join('');
  document.getElementById('om-file').value = '';
  if (offer?.image_url) { document.getElementById('om-preview').src='../'+offer.image_url; document.getElementById('om-preview').style.display='block'; document.getElementById('om-placeholder').style.display='none'; }
  else { resetImgPreview('om-preview','om-placeholder'); }
  calcDiscount();
  openMod('offer-modal');
}
function calcDiscount() {
  const orig = parseFloat(document.getElementById('om-orig').value);
  const off = parseFloat(document.getElementById('om-offer').value);
  const prev = document.getElementById('discount-preview');
  if (orig > 0 && off > 0 && off < orig) {
    document.getElementById('discount-val').textContent = Math.round((1-off/orig)*100) + '%';
    prev.style.display = 'block';
  } else prev.style.display = 'none';
}
async function editOffer(id) {
  const res = await admGet({ action: 'get_offers' });
  const offer = (res.data||[]).find(o => o.id == id);
  openOfferModal(offer);
}
async function saveOffer() {
  const id = document.getElementById('om-id').value;
  const params = { action: id ? 'update_offer' : 'add_offer',
    title_ar: document.getElementById('om-ar').value.trim(),
    title_en: document.getElementById('om-en').value.trim(),
    original_price: document.getElementById('om-orig').value,
    offer_price: document.getElementById('om-offer').value,
    item_id: document.getElementById('om-item').value || 0,
    is_active: document.getElementById('om-active').value };
  if (id) params.id = id;
  if (!params.title_ar || !params.title_en || !params.original_price || !params.offer_price) { toast('يرجى ملء الحقول المطلوبة *', 'err'); return; }
  const imgFile = document.getElementById('om-file').files[0];
  const btn = document.getElementById('om-save'); btn.disabled = true; btn.textContent = 'جاري الحفظ...';
  const res = await adm(params, imgFile ? { image: imgFile } : null);
  btn.disabled = false; btn.textContent = 'حفظ العرض';
  if (res.success) { toast('تم الحفظ ✓'); closeMod('offer-modal'); loadOffers(); loadStats(); }
  else toast(res.message || 'خطأ', 'err');
}
async function delOffer(id, name) {
  if (!confirm(`حذف عرض "${name}"؟`)) return;
  const res = await adm({ action: 'delete_offer', id });
  if (res.success) { toast('تم الحذف'); loadOffers(); loadStats(); }
  else toast('خطأ', 'err');
}

// ── SETTINGS
async function saveSettings() {
  const params = { action: 'update_settings',
    restaurant_name_ar: document.getElementById('s-name-ar').value,
    restaurant_name_en: document.getElementById('s-name-en').value,
    slogan_ar: document.getElementById('s-slogan-ar').value,
    slogan_en: document.getElementById('s-slogan-en').value,
    contact_whatsapp: document.getElementById('s-wa').value,
    contact_phone: document.getElementById('s-phone').value,
    contact_facebook: document.getElementById('s-fb').value,
    contact_instagram: document.getElementById('s-ig').value,
    contact_show: document.getElementById('s-contact-show').value };
  const np = document.getElementById('s-new-pass').value;
  const cp = document.getElementById('s-cur-pass').value;
  if (np) { params.new_password = np; params.current_password = cp; }
  const res = await adm(params);
  if (res.success) toast('تم حفظ الإعدادات ✓');
  else toast(res.message || 'خطأ', 'err');
}

// ── LOGOUT
async function doLogout() {
  await adm({ action: 'logout' });
  window.location.href = 'login.php';
}

// ── SOCIAL LINKS
async function loadLinks() {
  const grid = document.getElementById('links-cards');
  grid.innerHTML = `<div class="orders-empty">⏳ جاري التحميل...</div>`;
  const res = await admGet({ action: 'get_social_links' });
  const links = res.data || [];
  if (!links.length) { document.getElementById('links-cards').innerHTML = `<div class="orders-empty">لا توجد روابط — أضف أول رابط الآن</div>`; return; }
  document.getElementById('links-cards').innerHTML = links.map(l => {
    const iconHtml = l.icon_type === 'upload' && l.icon_url
      ? `<img src="../${l.icon_url}" style="width:36px;height:36px;border-radius:50%;object-fit:contain;background:#fff;padding:3px">`
      : (l.icon_type === 'svg' ? `<span style="color:${l.color};display:flex;align-items:center">${l.icon_value.replace(/<svg/,'<svg style="width:28px;height:28px"')}</span>` : '–');
    const shortUrl = l.url.length > 30 ? l.url.slice(0,30)+'…' : l.url;
    return `
    <div class="mgmt-card">
      <div class="mgmt-card-head">
        <div class="mgmt-card-thumb-placeholder" style="background:${l.color}22;border:1px solid ${l.color}44">${iconHtml}</div>
        <div>
          <div class="mgmt-card-title">${l.label}</div>
          <div class="mgmt-card-sub" style="direction:ltr;font-size:11px">${shortUrl}</div>
        </div>
      </div>
      <div class="mgmt-card-body">🔢 ترتيب: <span>${l.sort_order}</span></div>
      <div class="mgmt-card-foot">
        <span class="badge badge-${l.is_active==1?'on':'off'}">${l.is_active==1?'نشط':'مخفي'}</span>
        <div class="act">
          <button class="btn-e" onclick="editLink(${l.id})">✏️ تعديل</button>
          <button class="btn-d" onclick="delLink(${l.id},'${l.label}')">🗑</button>
        </div>
      </div>
    </div>`;
  }).join('');
}

let _linksCache = [];
async function openLinkModal(link=null) {
  document.getElementById('lm-title').textContent = link ? 'تعديل الرابط' : 'إضافة رابط';
  document.getElementById('lm-id').value     = link?.id || '';
  document.getElementById('lm-label').value  = link?.label || '';
  document.getElementById('lm-url').value    = link?.url || '';
  document.getElementById('lm-color').value  = link?.color || '#25d366';
  document.getElementById('lm-order').value  = link?.sort_order ?? 0;
  document.getElementById('lm-active').value = link?.is_active ?? 1;
  const type = link?.icon_type || 'svg';
  document.getElementById('lm-icon-type').value = type;
  document.getElementById('lm-svg').value = (type==='svg') ? (link?.icon_value||'') : '';
  document.getElementById('lm-icon-file').value = '';
  resetImgPreview('lm-icon-preview','lm-icon-placeholder');
  if (type==='upload' && link?.icon_url) {
    document.getElementById('lm-icon-preview').src = '../'+link.icon_url;
    document.getElementById('lm-icon-preview').style.display='block';
    document.getElementById('lm-icon-placeholder').style.display='none';
  }
  toggleIconInput();
  openMod('link-modal');
}

function toggleIconInput() {
  const t = document.getElementById('lm-icon-type').value;
  document.getElementById('lm-svg-wrap').style.display    = t==='svg'    ? '' : 'none';
  document.getElementById('lm-upload-wrap').style.display = t==='upload' ? '' : 'none';
}

async function editLink(id) {
  const res = await admGet({ action: 'get_social_links' });
  const link = (res.data||[]).find(l => l.id == id);
  openLinkModal(link);
}

async function saveLink() {
  const id = document.getElementById('lm-id').value;
  const iconType = document.getElementById('lm-icon-type').value;
  let url = document.getElementById('lm-url').value.trim();
  // إضافة https:// تلقائياً إذا لم يكن الرابط يبدأ ببروتوكول معروف
  if (url && !url.match(/^(https?:\/\/|tel:|mailto:|\/\/)/i)) {
    url = 'https://' + url;
    document.getElementById('lm-url').value = url;
  }
  const params = {
    action: id ? 'update_social_link' : 'add_social_link',
    label: document.getElementById('lm-label').value.trim(),
    url: url,
    color: document.getElementById('lm-color').value,
    sort_order: document.getElementById('lm-order').value,
    is_active: document.getElementById('lm-active').value,
    icon_type: iconType,
    icon_value: iconType === 'svg' ? document.getElementById('lm-svg').value.trim() : '',
  };
  if (id) params.id = id;
  if (!params.label || !params.url) { toast('يرجى إدخال التسمية والرابط', 'err'); return; }
  const iconFile = document.getElementById('lm-icon-file').files[0];
  const btn = document.getElementById('lm-save'); btn.disabled = true; btn.textContent = 'جاري الحفظ...';
  const res = await adm(params, (iconType==='upload' && iconFile) ? { icon_file: iconFile } : null);
  btn.disabled = false; btn.textContent = 'حفظ الرابط';
  if (res.success) { toast('تم الحفظ ✓'); closeMod('link-modal'); loadLinks(); }
  else toast(res.message || 'خطأ', 'err');
}

async function delLink(id, label) {
  if (!confirm(`حذف الرابط "${label}"؟`)) return;
  const res = await adm({ action: 'delete_social_link', id });
  if (res.success) { toast('تم الحذف'); loadLinks(); }
  else toast('خطأ', 'err');
}

// ── إعدادات البوت ─────────────────────────────────────────
async function saveBotSettings() {
  const token  = document.getElementById('s-bot-token').value.trim();
  const chatId = document.getElementById('s-chat-id').value.trim();
  const res = await adm({ action: 'update_settings', telegram_bot_token: token, telegram_chat_id: chatId });
  if (res.success) toast('تم حفظ إعدادات البوت ✓');
  else toast('خطأ في الحفظ', 'err');
}

async function testBot() {
  const result = document.getElementById('bot-test-result');
  result.style.display = 'block';
  result.style.color = 'rgba(200,170,100,.6)';
  result.textContent = '⏳ جارٍ الاختبار...';
  const res = await adm({ action: 'test_telegram' });
  if (res.success) {
    result.style.color = '#25d366';
    result.textContent = '✅ البوت يعمل بشكل صحيح!';
  } else {
    result.style.color = '#e57373';
    result.textContent = '❌ ' + (res.message || 'فشل الاتصال — تحقق من التوكن والـ Chat ID');
  }
}

async function registerWebhook() {
  const result = document.getElementById('bot-test-result');
  const webhookInput = document.getElementById('s-webhook-url');
  const webhookUrl = (webhookInput?.value || '').trim();
  
  if (!webhookUrl) {
    result.style.display = 'block';
    result.style.color = '#e57373';
    result.textContent = '❌ يرجى إدخال رابط الموقع أو ngrok أولاً';
    return;
  }

  result.style.display = 'block';
  result.style.color = 'rgba(200,170,100,.6)';
  result.textContent = '⏳ جارٍ تسجيل الـ Webhook...';

  const fd = new FormData();
  fd.append('action', 'register_webhook');
  fd.append('webhook_url', webhookUrl);
  const res = await fetch('../api/admin.php', { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({ success: false }));

  if (res.success) {
    result.style.color = '#64b5f6';
    result.textContent = '✅ تم تسجيل الـ Webhook على: ' + (res.url || '');
  } else {
    result.style.color = '#e57373';
    result.textContent = '❌ ' + (res.message || 'فشل التسجيل');
  }
}

async function checkWebhook() {
  const result = document.getElementById('bot-test-result');
  result.style.display = 'block';
  result.style.color = 'rgba(200,170,100,.6)';
  result.textContent = '⏳ جارٍ فحص حالة الـ Webhook...';
  const fd = new FormData();
  fd.append('action', 'check_webhook');
  const res = await fetch('../api/admin.php', { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({ success: false }));
  if (res.success && res.webhook_info) {
    const w = res.webhook_info;
    const url = w.url || '(لا يوجد رابط)';
    const pending = w.pending_update_count || 0;
    const lastErr = w.last_error_message || '';
    result.style.color = w.url ? '#64b5f6' : '#e57373';
    result.innerHTML = `<b>Webhook URL:</b> ${url}<br>`
      + `<b>طلبات معلّقة:</b> ${pending}<br>`
      + (lastErr ? `<b style="color:#e57373">آخر خطأ:</b> ${lastErr}` : '<span style="color:#4caf50">✅ لا توجد أخطاء</span>');
  } else {
    result.style.color = '#e57373';
    result.textContent = '❌ فشل الفحص — ' + (res.message || '');
  }
}

// ── الطلبات ───────────────────────────────────────────────
const ORDER_STATUS_LABELS = {
  pending:   '⏳ بانتظار الرد',
  accepted:  '✅ مقبول',
  rejected:  '❌ مرفوض',
  preparing: '🟠 قيد التحضير',
  ready:     '🟢 جاهز',
  delivered: '✔️ مُسلَّم',
};
const ORDER_STATUS_COLORS = {
  pending:   'rgba(212,175,55,.3)',
  accepted:  'rgba(37,211,102,.3)',
  rejected:  'rgba(229,115,115,.3)',
  preparing: 'rgba(255,152,0,.3)',
  ready:     'rgba(37,211,102,.4)',
  delivered: 'rgba(100,181,246,.3)',
};

async function loadOrders() {
  const container = document.getElementById('orders-cards');
  const badge     = document.getElementById('orders-count-badge');
  const filter    = document.getElementById('orders-filter').value;
  container.innerHTML = `<div class="orders-empty">⏳ جاري التحميل...</div>`;

  const res    = await admGet({ action: 'get_orders', status: filter });
  const orders = res.data || [];

  if (badge) badge.textContent = orders.length ? `${orders.length} طلب` : '';

  if (!orders.length) {
    container.innerHTML = `<div class="orders-empty">📭 لا توجد طلبات</div>`;
    return;
  }

  function orderTypeHtml(o) {
    const type = o.order_type || 'pickup';
    if (type === 'dine_in')  return `<div class="order-type-badge">🍽️ في المطعم — طاولة رقم ${o.table_number || '-'}</div>`;
    if (type === 'delivery') return `<div class="order-type-badge">🛵 توصيل — ${o.delivery_address || '-'}</div>`;
    return `<div class="order-type-badge">🏃 استلام مباشر</div>`;
  }

  container.innerHTML = orders.map(o => {
    const items   = JSON.parse(o.items || '[]');
    const color   = ORDER_STATUS_COLORS[o.status] || 'rgba(212,175,55,.2)';
    const label   = ORDER_STATUS_LABELS[o.status] || o.status;
    const date    = new Date(o.created_at);
    const now     = new Date();
    const diffMin = Math.round((now - date) / 60000);
    const timeStr = diffMin < 60
      ? `منذ ${diffMin} دقيقة`
      : diffMin < 1440
        ? `منذ ${Math.round(diffMin/60)} ساعة`
        : date.toLocaleDateString('ar-SY');

    const itemsHtml = items.map(i =>
      `<div>• ${i.name} <span style="color:var(--gold)">×${i.qty}</span> — ${Number(i.price * i.qty).toLocaleString('ar-SY')} ل.س</div>`
    ).join('');

    return `<div class="order-card">
      <div class="order-card-head">
        <span class="order-num">${o.order_num}</span>
        <span class="order-badge" style="background:${color}">${label}</span>
      </div>
      <div class="order-customer">${o.customer_name}</div>
      ${o.customer_phone ? `<div class="order-phone">📞 ${o.customer_phone}</div>` : ''}
      ${orderTypeHtml(o)}
      <div class="order-items">${itemsHtml}</div>
      ${o.notes ? `<div class="order-notes">📝 ${o.notes}</div>` : ''}
      <div class="order-footer">
        <span class="order-total">${Number(o.total).toLocaleString('ar-SY')} ل.س</span>
        <span class="order-time">🕐 ${timeStr}</span>
      </div>
    </div>`;
  }).join('');
}

// ── إضافة get_orders و test_telegram و register_webhook للـ API ──
// (تم تعريفها في admin.php)

window.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-ov.open').forEach(m=>m.classList.remove('open')); });
</script>
</body>
</html>

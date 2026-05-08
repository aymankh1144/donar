<?php
session_start();
require_once __DIR__ . '/database.php';
$db = getDB();
$settings = [];
foreach ($db->query("SELECT key, value FROM settings")->fetchAll() as $row)
    $settings[$row['key']] = $row['value'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>MR. DONAR</title>
<link rel="icon" href="assets/images/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
:root{
  --gold:#D4AF37;--gold-light:#F5E18C;--gold-dark:#A67C00;
  --silver:#C8C8C8;--silver-light:#ECECEC;
  --radius-xl:32px;--radius-lg:24px;
  --tr:0.35s cubic-bezier(0.2,0.9,0.4,1.2);
}
body{
  --bg:#F0EBE1;--bg-glass:rgba(240,235,225,0.82);
  --text:#2C2823;--text2:#6a5a42;--text-gold:#B8942A;
  --border-glow:rgba(192,192,192,0.5);
  --header-bg:rgba(240,235,225,0.88);
  --divider-col:rgba(180,150,60,0.4);--section-col:#8a7050;
  background:var(--bg);font-family:'Cairo',sans-serif;
  min-height:100vh;overflow-x:hidden;
}
body.dark{
  --bg:#0B0A08;--bg-glass:rgba(14,13,10,0.82);
  --text:#E8E2D4;--text2:#9a8870;--text-gold:#D4AF37;
  --border-glow:rgba(212,175,55,0.3);
  --header-bg:rgba(11,10,8,0.9);
  --divider-col:rgba(180,150,60,0.3);--section-col:#b89a60;
}
[lang="en"]{direction:ltr}[lang="ar"]{direction:rtl}

/* زر تابع طلبك */
#track-order-btn{
  position:fixed;top:72px;left:50%;transform:translateX(-50%);
  z-index:450;
  background:linear-gradient(135deg,#1a3a1a,#0d2b0d);
  border:1.5px solid #4caf50;
  color:#4caf50;
  border-radius:30px;
  padding:9px 20px;
  font-family:'Cairo',sans-serif;
  font-size:13px;
  font-weight:700;
  cursor:pointer;
  display:none;
  align-items:center;
  gap:8px;
  box-shadow:0 4px 20px rgba(76,175,80,.3);
  animation:pulse-track 2s ease-in-out infinite;
  white-space:nowrap;
  backdrop-filter:blur(10px);
}
#track-order-btn .track-dot{
  width:8px;height:8px;border-radius:50%;
  background:#4caf50;
  animation:blink-dot 1.2s ease-in-out infinite;
}
@keyframes pulse-track{
  0%,100%{box-shadow:0 4px 20px rgba(76,175,80,.3)}
  50%{box-shadow:0 4px 30px rgba(76,175,80,.55)}
}
@keyframes blink-dot{
  0%,100%{opacity:1}50%{opacity:.2}
}

/* BG */
.bg-layer{position:fixed;inset:0;z-index:0;background:var(--bg);transition:background .5s}
.bg-layer::before{
  content:'';position:absolute;inset:0;
  background-image:url('assets/images/pattern.webp');
  background-repeat:repeat;background-size:420px auto;
  opacity:0.55;pointer-events:none;transition:opacity .5s;
}
body.dark .bg-layer::before{opacity:0.18}

.page{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column}
.main-content{flex:1}

/* HEADER */
.header{position:sticky;top:0;z-index:200;background:var(--header-bg);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border-bottom:1px solid var(--border-glow);transition:background .4s}
.header-inner{max-width:1200px;margin:0 auto;padding:0 1.8rem;height:68px;display:flex;align-items:center;justify-content:space-between}
.ctrl-group{display:flex;gap:10px}
.ctrl-btn{background:rgba(255,255,245,0.1);backdrop-filter:blur(8px);border:1px solid var(--border-glow);color:var(--text-gold);border-radius:40px;padding:7px 20px;font-weight:700;font-size:13px;cursor:pointer;font-family:'Cairo',sans-serif;transition:all var(--tr);letter-spacing:.3px}
.ctrl-btn:hover{background:rgba(212,175,55,0.2);transform:translateY(-2px)}
.theme-btn{font-size:16px;padding:6px 14px}

/* HERO */
.hero{max-width:1200px;margin:0 auto;padding:2rem 1.8rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:1.5rem}
.hero-text{flex:1}
.hero-name{font-family:'Cormorant Garamond',serif;font-size:clamp(2.2rem,7vw,3.8rem);font-weight:700;background:linear-gradient(135deg,var(--gold-light),var(--gold),var(--gold-dark));-webkit-background-clip:text;background-clip:text;color:transparent;letter-spacing:3px;line-height:1.1}
.hero-slogan{color:var(--text2);font-size:14px;margin-top:8px;font-weight:400}
.hero-logo{width:92px;height:92px;border-radius:50%;border:2px solid var(--gold);box-shadow:0 0 28px rgba(212,175,55,.35);object-fit:cover;flex-shrink:0}
@media(max-width:480px){.hero-logo{width:70px;height:70px}.hero-name{font-size:1.9rem}}

/* OFFERS */
.slider-wrap {
  overflow-x: auto;
  overflow-y: hidden;
  display: flex;
  scroll-behavior: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none; /* إخفاء شريط التمرير في Firefox */
  cursor: grab;
  padding-bottom: 10px; /* مساحة للظل السفلي للبطاقات */
}

.slider-wrap::-webkit-scrollbar {
  display: none; /* إخفاء شريط التمرير في Chrome/Safari/Edge */
}

.slider-track {
  display: flex;
  gap: 18px;
  width: max-content;
  padding: 10px 0;
  align-items: stretch; /* لضمان تساوي ارتفاع البطاقات إذا اختلف طول النص */
}

.offer-card {
  flex-shrink: 0;
  width: 240px; /* تثبيت عرض البطاقة */
  background: linear-gradient(145deg, #1a1713, #0f0e0b);
  border: 1px solid rgba(212, 175, 55, 0.4);
  border-radius: 24px;
  overflow: hidden; /* هذا السطر الأهم: يمنع الصورة من الخروج عن حدود البطاقة */
  cursor: pointer;
  transition: all var(--tr);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
  position: relative;
  user-select: none;
  -webkit-user-drag: none; /* لمنع السحب الافتراضي للصور في المتصفحات */
  display: flex;
  flex-direction: column;
}

.offer-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 24px 44px rgba(0, 0, 0, 0.45);
  border-color: var(--gold);
}

.offer-img {
  width: 100%;
  height: 140px; /* تثبيت ارتفاع الصورة */
  object-fit: cover; /* لضمان ملء الصورة للمساحة دون تغيير نسبها */
  pointer-events: none; /* يمنع التفاعل المباشر مع الصورة أثناء السحب */
}

.offer-img-ph {
  width: 100%;
  height: 140px;
  background: linear-gradient(135deg, #2a2418, #1a1510);
  display: flex;
  align-items: center;
  justify-content: center;
}

.offer-img-ph svg {
  width: 44px;
  height: 44px;
  opacity: 0.3;
  stroke: var(--gold);
}

.offer-body {
  padding: 0.9rem;
  flex-grow: 1; /* لتمدد مساحة النص إذا كانت الصورة ثابتة الارتفاع */
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.offer-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--gold-light);
  margin-bottom: 0.4rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.offer-prices {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.offer-old {
  font-size: 12px;
  color: rgba(212, 175, 55, 0.45);
  text-decoration: line-through;
}

.offer-new {
  font-size: 16px;
  font-weight: 800;
  color: var(--gold-light);
}

.offer-discount {
  background: #c0392b;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
}

.offer-corner {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(212, 175, 55, 0.9);
  color: #111;
  font-size: 10px;
  font-weight: 800;
  padding: 3px 9px;
  border-radius: 20px;
  z-index: 2; /* لضمان ظهور شارة الخصم فوق الصورة */
}

[lang="en"] .offer-corner {
  right: auto;
  left: 8px;
}
/* DIVIDER */
.fancy-divider{max-width:1200px;margin:1.5rem auto 1.2rem;padding:0 1.8rem;display:flex;align-items:center;gap:14px}
.fancy-divider::before,.fancy-divider::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.fancy-divider span{color:var(--section-col);font-size:17px;font-weight:800;letter-spacing:2px;white-space:nowrap}
body.dark .fancy-divider span{color:var(--gold)}
.dmd{width:8px;height:8px;background:var(--gold);transform:rotate(45deg);flex-shrink:0}

/* GRID */
.cats-wrap{max-width:1200px;margin:0 auto;padding:0 1.8rem 2.5rem}
#cats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
@media(min-width:600px){#cats-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:1000px){#cats-grid{grid-template-columns:repeat(4,1fr)}}

@keyframes border-scan {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ══ MARBLE CARD LIGHT — نسخة نظيفة بدون خطوط ══ */
/* ══ MARBLE CARD ══ */
.cat-card {
  /* الخلفية الأساسية للبطاقة */
  background: 
    linear-gradient(135deg, rgba(83, 80, 77, 0.9) 0%, transparent 100%), 
    url('assets/images/pattern.webp'), 
    linear-gradient(145deg, #888480, #c8c4be);
  background-size: cover;
  background-position: center;
  background-blend-mode: soft-light;

  border-radius: var(--radius-xl);
  padding: 1.7rem 1rem 1.5rem;
  cursor: pointer;
  text-decoration: none;
  display: block;
  position: relative;
  overflow: hidden; /* لقص الإطار الدوار */
  transition: all var(--tr);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  z-index: 1; /* تأمين سياق الطبقات */
}

/* 1. طبقة الإطار المتحرك (خلفية دوارة) */
.cat-card::before {
  content: "";
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(
    transparent, 
    #aa8a1f, /* ذهبي */
    transparent 25%,
    #6a6868, /* فضي */
    transparent 50%,
    #aa8a1f, /* ذهبي */
    transparent 75%,
    #6a6868, /* فضي */
    transparent
  );
  animation: border-scan 4s linear infinite;
  z-index: -2; /* تكون في أبعد نقطة في الخلف */
}

/* 2. طبقة الحماية (تغطي منتصف البطاقة وتترك الحواف) */
.cat-card::after {
  content: "";
  position: absolute;
  inset: 2px; /* هنا يتحكم سمك الإطار */
  background: inherit; /* ترث الرخام من الأب */
  background-clip: padding-box;
  border-radius: calc(var(--radius-xl) - 2px);
  z-index: -1; /* تكون فوق الإطار المتحرك وتحت المحتوى */
}

/* 3. ضمان ظهور المحتوى (اللوغو والنصوص) فوق الإطار */
.cat-card > * {
  position: relative;
  z-index: 10; /* رقم مرتفع لضمان الظهور */
}

/* ══ تعديلات الوضع الليلي ══ */
body.dark .cat-card {
  background: 
    linear-gradient(135deg, rgba(179, 179, 179, 0.9) 0%, transparent 100%),
    url('assets/images/pattern.webp'),
    linear-gradient(145deg, #1d1b18, #0b0a08);
  background-blend-mode: overlay;
  border: none; /* نعتمد على الإطار المتحرك بدلاً من Border الثابت */
}

/* إخفاء الزوائد القديمة لعدم التعارض */
.card-top-line, .cc {
  display: none !important;
}

/* تأثير التفاعل */
.cat-card:hover {
  transform: translateY(-6px) scale(1.02);
}
.cat-card:hover::before {
  animation-duration: 2s; /* تسريع الحركة عند اللمس */
}
/* ══ إصلاح الوضع الداكن للبطاقات ══ */
body.dark .cat-card {
  
  background: linear-gradient(rgba(11, 10, 8, 0.85), rgba(11, 10, 8, 0.85)), url('assets/images/pattern.webp'), linear-gradient(145deg, #1d1b18, #0b0a08) !important;
    linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%),
    url('assets/images/pattern.webp'),
    linear-gradient(145deg, #1d1b18, #0b0a08);
  background-size: cover;
  background-blend-mode: overlay;
  border: none !important; /* إلغاء الحدود الثابتة لتظهر الحركة */
  box-shadow: 0 8px 28px rgba(0,0,0,0.6);
}

/* ضمان أن طبقة الحماية في الداكن تستخدم نفس خلفية الرخام الأسود */
body.dark .cat-card::after {
  background: inherit !important;
  background-clip: padding-box !important;
  display: block !important;
}

/* إعادة تفعيل حركة الإطار في الوضع الداكن ومنع مسحها بالكود القديم */
body.dark .cat-card::before {
  content: "";
  display: block !important;
  background: conic-gradient(
    transparent, 
    #D4AF37, /* ذهبي */
    transparent 25%,
    #C8C8C8, /* فضي */
    transparent 50%,
    #D4AF37, 
    transparent 75%,
    #C8C8C8, 
    transparent
  ) !important;
  animation: border-scan 4s linear infinite !important;
}

/* إخفاء الزوايا القديمة والخطوط التي تظهر في صورتك */
body.dark .card-top-line, 
body.dark .cc {
  display: none !important;
}

/* تحسين مظهر النصوص في الوضع الداكن فوق الإطار */
body.dark .cat-name {
  color: var(--gold-light);
  text-shadow: 0 2px 10px rgba(0,0,0,0.7);
}/* hover */
.cat-card:hover{transform:translateY(-6px) scale(1.025);box-shadow:0 20px 44px rgba(0,0,0,.22),0 0 0 1.5px rgba(212,175,55,.55)}
body.dark .cat-card:hover{box-shadow:0 20px 44px rgba(0,0,0,.55),0 0 0 1.5px rgba(212,175,55,.38),0 0 30px rgba(212,175,55,.07)}
.cat-card:active,.cat-card.pressed{transform:scale(.96) !important}

/* gold corner brackets */
.cc{position:absolute;width:18px;height:18px;border-color:rgba(212,175,55,.9);border-style:solid;pointer-events:none}
.cc-tl{top:10px;right:10px;border-width:2px 0 0 2px;border-radius:5px 0 0 0}
.cc-tr{top:10px;left:10px;border-width:2px 2px 0 0;border-radius:0 5px 0 0}
.cc-bl{bottom:10px;right:10px;border-width:0 0 2px 2px;border-radius:0 0 0 5px}
.cc-br{bottom:10px;left:10px;border-width:0 2px 2px 0;border-radius:0 0 5px 0}

/* logo inside card */
.cat-logo{position:absolute;top:14px;left:50%;transform:translateX(-50%);width:44px;height:44px;border-radius:50%;border:2px solid rgba(212,175,55,.7);box-shadow:0 3px 12px rgba(0,0,0,.3);object-fit:cover;z-index:1}

/* card texts */
.cat-name{font-family:'Cormorant Garamond',serif;font-size:1.45rem;font-weight:700;text-align:center;margin:48px 0 6px;letter-spacing:.5px;line-height:1.2;position:relative;z-index:1}
body:not(.dark) .cat-name{color:#1c1916;text-shadow:0 1px 0 rgba(255,255,255,.65),0 -1px 2px rgba(0,0,0,.12)}
body.dark .cat-name{color:var(--gold-light);text-shadow:0 2px 10px rgba(0,0,0,.7)}

.cat-count{font-size:12px;font-weight:600;text-align:center;display:block;padding:3px 12px;border-radius:40px;margin:0 auto 10px;position:relative;z-index:1;width:fit-content}
body:not(.dark) .cat-count{color:#3a3530;background:rgba(0,0,0,0.07);border:1px solid rgba(0,0,0,.09)}
body.dark .cat-count{color:rgba(212,175,55,.75);background:rgba(0,0,0,.35);border:1px solid rgba(212,175,55,.15)}

.cat-arrow{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;margin:6px auto 0;font-size:14px;font-weight:700;transition:all var(--tr);position:relative;z-index:1}
body:not(.dark) .cat-arrow{background:rgba(0,0,0,.07);border:1px solid rgba(0,0,0,.14);color:#2a2520}
body.dark .cat-arrow{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.32);color:var(--gold-light)}
.cat-card:hover .cat-arrow{transform:scale(1.2) rotate(90deg)}
body:not(.dark) .cat-card:hover .cat-arrow{background:rgba(0,0,0,.13)}
body.dark .cat-card:hover .cat-arrow{background:rgba(212,175,55,.28)}

/* LOADING */
.loading-box{grid-column:1/-1;padding:3.5rem;text-align:center;color:var(--text2)}
.spinner{width:36px;height:36px;border:2px solid rgba(212,175,55,.2);border-top-color:var(--gold);border-radius:50%;animation:spin .75s linear infinite;margin:0 auto 1rem}
@keyframes spin{to{transform:rotate(360deg)}}

/* CAT VIEW */
#cat-view{display:none}
.cat-page{max-width:1200px;margin:0 auto;padding:1.5rem 1.8rem 3rem}
.back-btn{display:inline-flex;align-items:center;gap:8px;background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.35);color:var(--gold);border-radius:40px;padding:8px 20px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:all var(--tr);margin-bottom:1.5rem}
.back-btn:hover{background:rgba(212,175,55,.22);transform:translateY(-2px)}

/* ── قسم البحث المطور ── */
.search-wrap {
    margin-bottom: 1.5rem;
}

/* 1. الإعدادات الافتراضية (الوضع الداكن Dark Mode كما في الصورة) */
.search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #1a1713; /* أسود بني فاخر - غير شفاف لضمان الوضوح */
    border: 2px solid #D4AF37; /* حدود ذهبية واضحة */
    border-radius: 50px;
    padding: 0 16px;
    transition: all 0.3s ease;
}

.search-box:focus-within {
    border-color: #F5E18C; /* ذهبي فاتح عند الضغط */
    background: #231f1a;
    box-shadow: 0 0 15px rgba(212, 175, 55, 0.2);
}

.search-icon {
    color: #D4AF37; /* أيقونة ذهبية */
    flex-shrink: 0;
    margin-left: 10px;
}

.search-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #F5E18C; /* نص ذهبي فاتح */
    font-family: 'Cairo', sans-serif;
    font-size: 15px;
    padding: 13px 0;
    direction: rtl;
}

.search-input::placeholder {
    color: rgba(212, 175, 55, 0.5); /* نص إرشادي ذهبي خفيف */
}

/* 2. تخصيص الوضع الفاتح (Light Mode) بألوان مغايرة تماماً */
[data-theme="light"] .search-box {
    background: #ffffff; /* خلفية بيضاء نقية */
    border-color: #8a7050; /* حدود برونزية/بنية فاتحة */
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

[data-theme="light"] .search-box:focus-within {
    border-color: #D4AF37;
    background: #fffdf9;
    box-shadow: 0 4px 15px rgba(138, 112, 80, 0.15);
}

[data-theme="light"] .search-icon {
    color: #8a7050; /* أيقونة بنية غامقة للتباين */
}

[data-theme="light"] .search-input {
    color: #2C2823; /* نص داكن جداً للوضوح */
}

[data-theme="light"] .search-input::placeholder {
    color: #a89a85;
}

/* ── أزرار المسح والنتائج ── */
.search-clear {
    background: none;
    border: none;
    color: rgba(212, 175, 55, .5);
    cursor: pointer;
    font-size: 16px;
    padding: 4px 8px;
    transition: color .2s;
}

.search-clear:hover {
    color: var(--gold);
}

.search-empty {
    text-align: center;
    padding: 3rem;
    color: var(--text2);
    font-size: 15px;
}

/* ── السلة ── */
.add-to-cart-btn{display:inline-flex;align-items:center;gap:5px;background:#d4af37;color:#000;border:none;border-radius:20px;padding:6px 14px;font-size:13px;font-weight:700;font-family:'Cairo',sans-serif;cursor:pointer;transition:.2s;flex-shrink:0}
.add-to-cart-btn:hover{background:#e8c84a;transform:scale(1.05)}
.modal-cart-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:#d4af37;color:#000;border:none;border-radius:14px;padding:14px;font-size:16px;font-weight:700;font-family:'Cairo',sans-serif;cursor:pointer;margin-top:1.2rem;transition:.2s}
.modal-cart-btn:hover{background:#e8c84a}

/* شريط السلة السفلي */
.cart-bar{position:fixed;bottom:0;left:0;right:0;z-index:500;background:#d4af37;color:#000;cursor:pointer;box-shadow:0 -4px 20px rgba(212,175,55,.4);transition:transform .3s}
.cart-bar-inner{max-width:600px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:14px 20px;font-family:'Cairo',sans-serif;font-weight:700}
.cart-bar-count{background:#000;color:#d4af37;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px}
.cart-bar-label{font-size:16px}
.cart-bar-total{font-size:15px}

/* مودال السلة */
.cart-overlay{position:fixed;inset:0;z-index:600;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s}
.cart-overlay.open{opacity:1;pointer-events:auto}
.cart-modal{background:#13110c;border:1px solid rgba(212,175,55,.3);border-radius:24px 24px 0 0;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;padding:1.5rem;transform:translateY(100%);transition:transform .35s cubic-bezier(.34,1.56,.64,1)}
.cart-overlay.open .cart-modal{transform:translateY(0)}
.cart-modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem}
.cart-modal-title{font-size:20px;font-weight:700;color:#d4af37;font-family:'Cairo',sans-serif}
.cart-modal-close{background:none;border:none;color:rgba(212,175,55,.6);font-size:22px;cursor:pointer;line-height:1}
.cart-modal-close:hover{color:#d4af37}

/* عناصر السلة */
.cart-items-list{margin-bottom:1rem}
.cart-empty{text-align:center;padding:2rem;color:rgba(212,175,55,.4);font-size:15px}
.cart-item{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(212,175,55,.1);flex-wrap:wrap;gap:8px}
.cart-item-name{font-size:15px;font-weight:600;color:var(--text1);font-family:'Cairo',sans-serif;flex:1;min-width:120px}
.cart-item-controls{display:flex;align-items:center;gap:8px}
.qty-btn{width:28px;height:28px;border-radius:50%;border:1px solid rgba(212,175,55,.4);background:transparent;color:#d4af37;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
.qty-btn:hover{background:rgba(212,175,55,.15)}
.qty-num{min-width:22px;text-align:center;font-weight:700;color:var(--text1)}
.cart-item-price{font-size:13px;color:rgba(212,175,55,.7);white-space:nowrap}
.cart-item-remove{background:none;border:none;cursor:pointer;font-size:16px;opacity:.6;transition:.2s}
.cart-item-remove:hover{opacity:1}
.cart-total-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-top:1px solid rgba(212,175,55,.25);font-weight:700;font-size:17px;color:#d4af37;font-family:'Cairo',sans-serif;margin-bottom:1rem}

/* فورم الطلب */
.cart-form-title{font-size:16px;font-weight:700;color:var(--text1);font-family:'Cairo',sans-serif;margin-bottom:12px}
.cart-input{width:100%;background:rgba(212,175,55,.06);border:1px solid rgba(212,175,55,.2);border-radius:12px;padding:12px 14px;color:#fff;font-family:'Cairo',sans-serif;font-size:15px;margin-bottom:10px;outline:none;transition:border-color .2s;direction:rtl}
.cart-input:focus{border-color:rgba(212,175,55,.5)}
.cart-input::placeholder{color:rgba(212,175,55,.35)}
.cart-textarea{resize:none}
.cart-submit-btn{width:100%;background:#d4af37;color:#000;border:none;border-radius:14px;padding:15px;font-size:17px;font-weight:700;font-family:'Cairo',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;margin-top:4px}
.cart-submit-btn:hover:not(:disabled){background:#e8c84a}
.cart-submit-btn:disabled{opacity:.6;cursor:not-allowed}

/* حالة الطلب */
.order-status{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem 1rem;gap:1rem}
.order-status-icon{font-size:52px}
.order-status-text{font-size:16px;font-weight:600;color:#fff;font-family:'Cairo',sans-serif;text-align:center;line-height:1.6}

/* toast إضافة للسلة */
.cart-toast{position:fixed;bottom:80px;left:50%;transform:translateX(-50%) translateY(20px);background:#1e1b16;color:#d4af37;border:1px solid rgba(212,175,55,.3);border-radius:30px;padding:10px 22px;font-size:14px;font-family:'Cairo',sans-serif;z-index:700;opacity:0;transition:all .3s;white-space:nowrap}
.cart-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.cat-hero{background:linear-gradient(145deg,#1a1713,#0f0e0b);border:1px solid rgba(212,175,55,.35);border-radius:var(--radius-lg);padding:1.4rem 1.6rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.2rem}
.cat-hero-name{font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;color:var(--gold-light)}
.cat-hero-desc{font-size:13px;color:rgba(212,175,55,.5);margin-top:3px}

/* ITEMS */
.items-grid{display:grid;gap:20px;grid-template-columns:1fr}
@media(min-width:600px){.items-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:900px){.items-grid{grid-template-columns:repeat(3,1fr)}}
.item-card{background:linear-gradient(145deg,#1c1a15,#111008);border:1px solid rgba(212,175,55,.28);border-radius:28px;overflow:hidden;cursor:pointer;transition:all var(--tr);box-shadow:0 8px 22px rgba(0,0,0,.3);position:relative}
.item-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(212,175,55,.5),transparent)}
.item-card:hover{transform:translateY(-6px);box-shadow:0 22px 40px rgba(0,0,0,.4);border-color:var(--gold)}
.item-img{width:100%;aspect-ratio:16/9;object-fit:cover}
.item-img-ph{width:100%;;aspect-ratio:16/9;background:linear-gradient(135deg,#2a2418,#1a1510);display:flex;align-items:center;justify-content:center}
.item-img-ph svg{width:48px;height:48px;opacity:.28;stroke:var(--gold)}
.item-body{padding:1.1rem 1.2rem}
.item-name{font-size:1.05rem;font-weight:700;color:var(--gold-light);margin-bottom:5px}
.item-desc{font-size:13px;color:rgba(212,175,55,.6);line-height:1.5;margin-bottom:.7rem}
.item-footer{display:flex;justify-content:space-between;align-items:center}
.item-price{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:700;color:var(--gold-light)}
.item-currency{font-size:11px;font-weight:400;opacity:.65}
.item-more{font-size:12px;color:var(--gold-light);border:1px solid rgba(212,175,55,.35);border-radius:40px;padding:4px 14px;transition:.25s}
.item-card:hover .item-more{background:rgba(212,175,55,.2);border-color:var(--gold)}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.72);backdrop-filter:blur(14px);align-items:center;justify-content:center;padding:1rem}
.modal-overlay.open{display:flex}
.modal-wrap{position:relative;width:100%;max-width:600px}
.modal-box{background:linear-gradient(145deg,#1e1b16,#111008);border:1px solid var(--gold);border-radius:36px;width:100%;max-height:88vh;overflow-y:auto;box-shadow:0 32px 64px rgba(0,0,0,.55)}
.modal-img{width:100%;height:260px;object-fit:cover;border-radius:36px 36px 0 0}
.modal-img-ph{width:100%;height:180px;border-radius:36px 36px 0 0;background:linear-gradient(135deg,#2a2418,#1a1510);display:flex;align-items:center;justify-content:center}
.modal-img-ph svg{width:60px;height:60px;opacity:.22;stroke:var(--gold)}
.modal-close{position:absolute;top:16px;right:16px;background:rgba(0,0,0,.6);border:1px solid var(--gold);color:var(--gold);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all var(--tr);z-index:1}
[lang="en"] .modal-close{right:auto;left:16px}
.modal-close:hover{background:var(--gold);color:#000}
.modal-body{padding:1.6rem 1.8rem}
.modal-name{font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--gold-light);margin-bottom:.4rem}
.modal-price{font-family:'Cormorant Garamond',serif;font-size:1.5rem;color:var(--gold-light);margin-bottom:1rem}
.modal-section-title{font-size:11px;font-weight:800;color:var(--gold);text-transform:uppercase;letter-spacing:1.5px;margin:.9rem 0 .45rem}
.modal-text{font-size:14px;color:rgba(212,175,55,.7);line-height:1.65}
.modal-tags{display:flex;flex-wrap:wrap;gap:7px}
.modal-tag{background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.2);border-radius:40px;padding:4px 13px;font-size:12px;color:rgba(212,175,55,.85)}

/* CONTACT */
/* 1. تثبيت الشريط أسفل الشاشة */
.contact-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  z-index: 400;
  background: rgba(12, 11, 9, 0.95);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border-top: 1px solid rgba(212, 175, 55, 0.15);
  padding: 10px 0; /* تقليل الحشوة الرأسية */
  box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.3);
}

/* 2. ترتيب الأيقونات في سطر واحد بدون نص */
.contact-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  gap: 15px; /* المسافة بين الأيقونات */
  justify-content: center;
  align-items: center;
  flex-wrap: nowrap; /* منع الأيقونات من النزول لسطر جديد */
}

/* 3. تحويل الأزرار إلى دوائر وإخفاء النص */
.contact-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;  /* عرض ثابت للدائرة */
  height: 46px; /* ارتفاع ثابت للدائرة */
  border-radius: 50%; /* جعل الزر دائري بالكامل */
  padding: 0;   /* إلغاء الحشوة الداخلية لتوسيط الأيقونة */
  transition: all var(--tr);
  cursor: pointer;
  border: none;
  text-decoration: none;
}

/* 4. إخفاء النصوص داخل الأزرار */
.contact-btn span {
  display: none !important; /* إخفاء "واتساب"، "اتصال"، إلخ */
}

/* 5. ضبط حجم الأيقونة (SVG) */
.contact-btn svg {
  width: 22px;
  height: 22px;
  margin: 0; /* إلغاء أي هوامش قديمة */
}

/* تحسين مساحة الصفحة حتى لا يغطي الشريط المحتوى */
.page {
  padding-bottom: 70px; /* مساحة في أسفل الصفحة تعادل ارتفاع الشريط */
}.contact-btn svg{width:18px;height:18px;flex-shrink:0}
.btn-whatsapp{background:#25D366;color:#fff}.btn-whatsapp:hover{background:#1da851;transform:translateY(-2px)}
.btn-call{background:rgba(212,175,55,.15);border:1px solid var(--gold);color:var(--gold)}.btn-call:hover{background:rgba(212,175,55,.3);transform:translateY(-2px)}
.btn-facebook{background:#1877F2;color:#fff}.btn-facebook:hover{background:#145ecc;transform:translateY(-2px)}
.btn-instagram{background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff}.btn-instagram:hover{opacity:.88;transform:translateY(-2px)}

/* TOAST */
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);background:rgba(0,0,0,.85);backdrop-filter:blur(20px);color:var(--gold-light);border:1px solid var(--gold);border-radius:60px;padding:11px 26px;font-size:13px;font-weight:600;z-index:9999;transition:transform .38s cubic-bezier(.34,1.56,.64,1);white-space:nowrap}
.toast.show{transform:translateX(-50%) translateY(0)}

.fade-in{animation:fadeUp .5s ease both}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.stagger .cat-card:nth-child(1){animation:fadeUp .4s .04s ease both}
.stagger .cat-card:nth-child(2){animation:fadeUp .4s .09s ease both}
.stagger .cat-card:nth-child(3){animation:fadeUp .4s .14s ease both}
.stagger .cat-card:nth-child(4){animation:fadeUp .4s .19s ease both}
.stagger .cat-card:nth-child(5){animation:fadeUp .4s .24s ease both}
.stagger .cat-card:nth-child(6){animation:fadeUp .4s .29s ease both}
.stagger .cat-card:nth-child(7){animation:fadeUp .4s .34s ease both}
.stagger .cat-card:nth-child(8){animation:fadeUp .4s .39s ease both}
</style>
</head>
<body data-page="home">
<div class="bg-layer"></div>
<div class="page">

<header class="header">
  <div class="header-inner">
    <div class="ctrl-group">
      <button class="ctrl-btn theme-btn" id="theme-btn" onclick="toggleTheme()">🌙</button>
      <button class="ctrl-btn" id="lang-btn" onclick="toggleLang()">EN</button>
    </div>
    <div style="width:40px"></div>
  </div>
</header>

<div id="home-view" class="main-content">
  <div class="hero fade-in">
    <div class="hero-text">
      <h1 class="hero-name">MR. DONAR</h1>
      <p class="hero-slogan" id="hero-slogan"><?= htmlspecialchars($settings['slogan_ar'] ?? '') ?></p>
    </div>
    <img class="hero-logo" src="assets/images/logo.jpg" alt="MR.DONAR">
  </div>

  <div class="offers-section" id="offers-section" style="display:none">
    <div class="offers-header">
      <div class="offers-title"><span id="offers-label">العروض الخاصة</span></div>
      <span class="offers-badge" id="offers-badge">خصم</span>
    </div>
    <div class="slider-wrap"><div class="slider-track" id="slider-track"></div></div>
  </div>

  <div class="fancy-divider"><div class="dmd"></div><span id="cats-label">الفئات</span><div class="dmd"></div></div>

  <!-- حقل البحث الشامل -->
  <div class="search-wrap" id="global-search-wrap">
    <div class="search-box">
      <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" id="search-input" class="search-input" placeholder="ابحث في القائمة..." oninput="globalSearch(this.value)" autocomplete="off">
      <button class="search-clear" id="search-clear" onclick="clearSearch()" style="display:none">✕</button>
    </div>
    <div id="search-results" class="search-results-wrap" style="display:none"></div>
    <div id="search-empty" class="search-empty" style="display:none"></div>
  </div>

  <div class="cats-wrap"><div id="cats-grid"><div class="loading-box"><div class="spinner"></div></div></div></div>
</div>

<div id="cat-view" class="main-content">
  <div class="cat-page">
    <button class="back-btn" onclick="goBack()"><span id="back-arrow">←</span> <span id="back-text">رجوع</span></button>
    <div class="cat-hero" id="cat-hero"><div class="spinner" style="width:28px;height:28px;border-width:2px;margin:auto"></div></div>
    <div id="items-grid" class="items-grid"></div>
  </div>
</div>

<div class="modal-overlay" id="item-modal" onclick="if(event.target===this)closeModal()">
  <div class="modal-wrap">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <div class="modal-box" id="modal-content"></div>
  </div>
</div>

<?php if (!empty($settings['contact_show']) && $settings['contact_show'] == '1'): ?>
<footer class="contact-bar">
  <div class="contact-inner">
    <?php if (!empty($settings['contact_whatsapp'])): ?>
    <a class="contact-btn btn-whatsapp" href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/','',$settings['contact_whatsapp'])) ?>" target="_blank"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg><span id="wa-label">واتساب</span></a>
    <?php endif; ?>
    <?php if (!empty($settings['contact_phone'])): ?>
    <a class="contact-btn btn-call" href="tel:<?= htmlspecialchars($settings['contact_phone']) ?>"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02z"/></svg><span id="call-label">اتصال</span></a>
    <?php endif; ?>
    <?php if (!empty($settings['contact_facebook'])): ?>
    <a class="contact-btn btn-facebook" href="<?= htmlspecialchars($settings['contact_facebook']) ?>" target="_blank"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg><span id="fb-label">فيسبوك</span></a>
    <?php endif; ?>
    <?php if (!empty($settings['contact_instagram'])): ?>
    <a class="contact-btn btn-instagram" href="<?= htmlspecialchars($settings['contact_instagram']) ?>" target="_blank"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg><span id="ig-label">انستغرام</span></a>
    <?php endif; ?>
  </div>
</footer>
<?php endif; ?>

<!-- App Download Section -->
<div style="padding:2rem 1rem 2.5rem;text-align:center;background:var(--bg-secondary,#111);border-top:1px solid rgba(255,255,255,0.07)">
  <p style="font-family:'Cairo',sans-serif;font-size:0.85rem;color:rgba(200,170,100,0.5);margin-bottom:1.25rem;letter-spacing:1px" id="app-dl-label">حمّل تطبيق طلباتك بلس</p>
  <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center">
    <a href="https://apps.apple.com/us/app/talabatuk-plus/id6759189465" style="display:flex;align-items:center;background:#000;border-radius:1rem;padding:.75rem 1.25rem;text-decoration:none;border:1px solid #27272a;gap:.75rem" target="_blank" rel="noopener">
        <svg viewBox="0 0 384 512" style="width:2rem;height:2rem;color:#fff;flex-shrink:0" fill="currentColor"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/></svg>
        <div style="display:flex;flex-direction:column;align-items:flex-start;color:#fff">
            <span style="font-size:10px;opacity:.8;font-weight:300;font-family:'Cairo',sans-serif">Download on the</span>
            <span style="font-size:1.2rem;font-weight:700;font-family:'Cairo',sans-serif;line-height:1.2">App Store</span>
        </div>
    </a>
    <a href="https://play.google.com/store/apps/details?id=com.talabatukplus.user" style="display:flex;align-items:center;background:#000;border-radius:1rem;padding:.75rem 1.25rem;text-decoration:none;border:1px solid #27272a;gap:.75rem" target="_blank" rel="noopener">
        <svg viewBox="0 0 512 512" style="width:2rem;height:2rem;color:#fff;flex-shrink:0" fill="currentColor"><path d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-10.3 18-28.5-1.2-36.3zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z"/></svg>
        <div style="display:flex;flex-direction:column;align-items:flex-start;color:#fff">
            <span style="font-size:10px;opacity:.8;font-weight:300;text-transform:uppercase;letter-spacing:.05em;font-family:'Cairo',sans-serif">GET IT ON</span>
            <span style="font-size:1.2rem;font-weight:700;font-family:'Cairo',sans-serif;line-height:1.2">Google Play</span>
        </div>
    </a>
    <a href="https://talabatukplus.com/downloads/talapatuk-plus-user.apk" download="Talabatuk-Plus.apk" style="display:flex;align-items:center;background:#000;border-radius:1rem;padding:.75rem 1.25rem;text-decoration:none;border:1px solid #27272a;gap:.75rem">
        <svg viewBox="0 0 24 24" style="width:2rem;height:2rem;color:#fff;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <div style="display:flex;flex-direction:column;align-items:flex-start;color:#fff">
            <span style="font-size:10px;opacity:.8;font-weight:300;text-transform:uppercase;letter-spacing:.05em;font-family:'Cairo',sans-serif">Direct Download</span>
            <span style="font-size:1.2rem;font-weight:700;font-family:'Cairo',sans-serif;line-height:1.2">تحميل مباشر APK</span>
        </div>
    </a>
  </div>
</div>

</div>
<div class="toast" id="toast"></div>

<!-- زر تابع طلبك -->
<button id="track-order-btn" onclick="openTrackOrder()">
  <span class="track-dot"></span>
  <span id="track-order-label">تابع طلبك</span>
</button>

<script>
const S={lang:localStorage.getItem('lang')||'ar',theme:localStorage.getItem('theme')||'light',cfg:{slogan_ar:<?= json_encode($settings['slogan_ar']??'') ?>,slogan_en:<?= json_encode($settings['slogan_en']??'') ?>}};
const TX={
  ar:{cats:'الفئات',back:'رجوع',arrow:'←',items_n:n=>`${n} عنصر`,empty_cats:'لا توجد فئات',empty_items:'لا توجد وجبات',syp:'ل.س',offers:'العروض الخاصة',badge:'خصم',ingredients:'المكونات',features:'المميزات',desc:'الوصف',wa:'واتساب',call:'اتصال',fb:'فيسبوك',ig:'انستغرام',more:'التفاصيل ←',add:'أضف',view_cart:'عرض السلة',cart_title:'سلة الطلبات',total:'الإجمالي',order_info:'بيانات الطلب',name_ph:'الاسم *',phone_ph:'رقم الهاتف *',notes_ph:'ملاحظات إضافية (اختياري)',send_order:'إرسال الطلب',sending:'جارٍ الإرسال...',cart_empty:'السلة فارغة',order_ok:'تم استلام طلبك بنجاح. بانتظار رد المطبخ...',order_err:'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مجدداً.',conn_err:'تعذّر الاتصال بالخادم.',added:'✓ أُضيف:',search_ph:'ابحث في القائمة...',no_results:'لا توجد نتائج لهذا البحث',status:{pending:{icon:'⏳',text:'بانتظار رد المطبخ...'},accepted:{icon:'✅',text:'تم قبول طلبك. جارٍ التحضير قريباً.'},rejected:{icon:'❌',text:'عذراً، تم رفض طلبك.'},preparing:{icon:'🍳',text:'طلبك قيد التحضير الآن.'},ready:{icon:'🟢',text:'طلبك جاهز للتسليم!'},delivered:{icon:'✔️',text:'تم تسليم طلبك. شكراً لك!'}}},
  en:{cats:'Categories',back:'Back',arrow:'→',items_n:n=>`${n} items`,empty_cats:'No categories',empty_items:'No items',syp:'SYP',offers:'Special Offers',badge:'SALE',ingredients:'Ingredients',features:'Features',desc:'Description',wa:'WhatsApp',call:'Call',fb:'Facebook',ig:'Instagram',more:'Details →',add:'Add',view_cart:'View Cart',cart_title:'Order Cart',total:'Total',order_info:'Order Details',name_ph:'Name *',phone_ph:'Phone number *',notes_ph:'Additional notes (optional)',send_order:'Send Order',sending:'Sending...',cart_empty:'Cart is empty',order_ok:'Your order has been received. Waiting for kitchen response...',order_err:'An error occurred. Please try again.',conn_err:'Could not connect to server.',added:'✓ Added:',search_ph:'Search menu...',no_results:'No results found',status:{pending:{icon:'⏳',text:'Waiting for kitchen response...'},accepted:{icon:'✅',text:'Your order has been accepted!'},rejected:{icon:'❌',text:'Sorry, your order was rejected.'},preparing:{icon:'🍳',text:'Your order is being prepared.'},ready:{icon:'🟢',text:'Your order is ready for delivery!'},delivered:{icon:'✔️',text:'Your order has been delivered. Thank you!'}}}
};
const t=()=>TX[S.lang];
const FOOD_SVG=`<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px"><path d="M18 2v20M15 2v6c0 1.657 1.343 3 3 3s3-1.343 3-3V2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6 2v5M9 2v5M6 7c0 1.657 1.343 3 3 3V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>`;

document.addEventListener('DOMContentLoaded',()=>{applyTheme(S.theme,false);applyLang(S.lang,false);loadOffers();loadCategories();const p=new URLSearchParams(location.search);if(p.get('cat'))showCategory(parseInt(p.get('cat')));});

function applyTheme(v,save=true){S.theme=v;if(save)localStorage.setItem('theme',v);document.body.classList.toggle('dark',v==='dark');document.getElementById('theme-btn').textContent=v==='dark'?'☀️':'🌙';}
function toggleTheme(){applyTheme(S.theme==='dark'?'light':'dark');}

function applyLang(l,reload=true){
  S.lang=l;localStorage.setItem('lang',l);document.documentElement.lang=l;document.documentElement.dir=l==='ar'?'rtl':'ltr';
  document.getElementById('lang-btn').textContent=l==='ar'?'EN':'ع';
  document.getElementById('cats-label').textContent=t().cats;
  document.getElementById('hero-slogan').textContent=l==='ar'?S.cfg.slogan_ar:S.cfg.slogan_en;
  document.getElementById('back-text').textContent=t().back;
  document.getElementById('back-arrow').textContent=t().arrow;
  const ol=document.getElementById('offers-label');if(ol)ol.textContent=t().offers;
  const ob=document.getElementById('offers-badge');if(ob)ob.textContent=t().badge;
  ['wa','call','fb','ig'].forEach(k=>{const el=document.getElementById(k+'-label');if(el)el.textContent=t()[k];});
  // ترجمة عناصر السلة
  const si=document.getElementById('search-input');if(si)si.placeholder=t().search_ph;
  const se=document.getElementById('search-empty');if(se)se.textContent=t().no_results;
  const cmt=document.querySelector('.cart-modal-title');if(cmt)cmt.textContent=t().cart_title;
  const cbl=document.querySelector('.cart-bar-label');if(cbl)cbl.textContent=t().view_cart;
  const ctr=document.querySelector('.cart-total-row > span:first-child');if(ctr)ctr.textContent=t().total;
  const cft=document.querySelector('.cart-form-title');if(cft)cft.textContent=t().order_info;
  const on=document.getElementById('order-name');if(on)on.placeholder=t().name_ph;
  const op=document.getElementById('order-phone');if(op)op.placeholder=t().phone_ph;
  const ono=document.getElementById('order-notes');if(ono)ono.placeholder=t().notes_ph;
  const csb=document.getElementById('submit-btn-text');if(csb)csb.textContent=t().send_order;
  const adl=document.getElementById('app-dl-label');if(adl)adl.textContent=l==='ar'?'حمّل تطبيق طلباتك بلس':'Download Talabatuk Plus App';
  const tol=document.getElementById('track-order-label');if(tol)tol.textContent=l==='ar'?'تابع طلبك':'Track Your Order';
  if(reload){loadOffers();loadCategories();}
}
function toggleLang(){applyLang(S.lang==='ar'?'en':'ar');}

async function api(p){const r=await fetch('api/menu.php?'+new URLSearchParams(p));return r.json();}
function priceHTML(p){return Number(p).toLocaleString('ar-SY')+' <span class="item-currency">'+t().syp+'</span>';}

async function loadOffers(){
  const sec=document.getElementById('offers-section'),track=document.getElementById('slider-track');
  const data=await api({action:'offers'}).catch(()=>null);
  if(!data?.success||!data.data.length){sec.style.display='none';return;}
  sec.style.display='block';
  const cards=data.data.map(o=>`<div class="offer-card" onclick="showCategory(${o.item_id||0})"><div class="offer-corner">-${o.discount}%</div>${o.image_url?`<img class="offer-img" src="${o.image_url}" alt="${S.lang==='ar'?o.title_ar:o.title_en}" loading="lazy">`:`<div class="offer-img-ph">${FOOD_SVG}</div>`}<div class="offer-body"><div class="offer-title">${S.lang==='ar'?o.title_ar:o.title_en}</div><div class="offer-prices"><span class="offer-old">${Number(o.original_price).toLocaleString('ar-SY')}</span><span class="offer-new">${Number(o.offer_price).toLocaleString('ar-SY')} ${t().syp}</span><span class="offer-discount">-${o.discount}%</span></div></div></div>`).join('');
  track.innerHTML=cards+cards;
}

async function loadCategories(){
  const grid=document.getElementById('cats-grid');
  grid.innerHTML=`<div class="loading-box"><div class="spinner"></div></div>`;
  const data=await api({action:'categories'}).catch(()=>null);
  if(!data?.success||!data.data.length){grid.innerHTML=`<div class="loading-box">${t().empty_cats}</div>`;return;}
  grid.className='stagger';
  grid.style.cssText='display:grid;grid-template-columns:repeat(2,1fr);gap:20px';
  grid.innerHTML=data.data.map(cat=>`
    <a class="cat-card" href="#" onclick="showCategory(${cat.id});return false;"
       ontouchstart="this.classList.add('pressed')" ontouchend="this.classList.remove('pressed')" ontouchcancel="this.classList.remove('pressed')">
      <div class="card-top-line"></div>
      <span class="cc cc-tl"></span><span class="cc cc-tr"></span><span class="cc cc-bl"></span><span class="cc cc-br"></span>
      <img class="cat-logo" src="assets/images/logo.jpg" alt="logo">
      <div class="cat-name">${S.lang==='ar'?cat.name_ar:cat.name_en}</div>
      <div class="cat-count">${t().items_n(cat.item_count)}</div>
      <div class="cat-arrow">${t().arrow}</div>
    </a>`).join('');
}

async function showCategory(catId){
  clearSearch();
  if(!catId)return;
  document.getElementById('home-view').style.display='none';
  document.getElementById('cat-view').style.display='block';
  history.pushState({catId},'',`?cat=${catId}`);window.scrollTo(0,0);
  document.getElementById('cat-hero').innerHTML=`<div class="spinner" style="width:28px;height:28px;border-width:2px;margin:auto"></div>`;
  document.getElementById('items-grid').innerHTML=`<div class="loading-box"><div class="spinner"></div></div>`;
  const data=await api({action:'items',category_id:catId}).catch(()=>null);
  if(!data?.success)return;
  const cat=data.category;
  document.getElementById('cat-hero').innerHTML=`<div><div class="cat-hero-name">${S.lang==='ar'?cat.name_ar:cat.name_en}</div><div class="cat-hero-desc">${S.lang==='ar'?cat.description_ar:cat.description_en}</div></div>`;
  if(!data.data.length){document.getElementById('items-grid').innerHTML=`<div class="loading-box">${t().empty_items}</div>`;return;}
  document.getElementById('items-grid').innerHTML=data.data.map(item=>`
    <div class="item-card fade-in" onclick="openItemModal(${item.id})">
      ${item.image_url?`<img class="item-img" src="${item.image_url}" alt="${S.lang==='ar'?item.name_ar:item.name_en}" loading="lazy">`:`<div class="item-img-ph">${FOOD_SVG}</div>`}
      <div class="item-body">
        <div class="item-name">${S.lang==='ar'?item.name_ar:item.name_en}</div>
        ${(S.lang==='ar'?item.description_ar:item.description_en)?`<div class="item-desc">${S.lang==='ar'?item.description_ar:item.description_en}</div>`:''}
        <div class="item-footer">
          <div class="item-price">${priceHTML(item.price)}</div>
          <button class="add-to-cart-btn" onclick="event.stopPropagation();addToCart(${item.id},'${(S.lang==='ar'?item.name_ar:item.name_en).replace(/'/g,"\\'")}',${item.price})">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4h-.01l-1.1 2-2.76 5H8.53l-.13-.27L6.16 6l-.95-2-.94-2H1v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 15.1 6.1 16 7 16h12v-2H7.42c-.13 0-.25-.11-.25-.25z"/></svg>
            ${t().add}
          </button>
        </div>
      </div>
    </div>`).join('');
}

async function openItemModal(id){
  const modal=document.getElementById('item-modal'),content=document.getElementById('modal-content');
  modal.classList.add('open');document.body.style.overflow='hidden';
  content.innerHTML=`<div style="padding:3rem;text-align:center"><div class="spinner"></div></div>`;
  const data=await api({action:'item',id}).catch(()=>null);
  if(!data?.success){closeModal();return;}
  const item=data.data,name=S.lang==='ar'?item.name_ar:item.name_en;
  const desc=S.lang==='ar'?item.description_ar:item.description_en;
  const ingr=S.lang==='ar'?item.ingredients_ar:item.ingredients_en;
  const feat=S.lang==='ar'?item.features_ar:item.features_en;
  const ingrTags=ingr?ingr.split(/[،,]+/).filter(x=>x.trim()).map(i=>`<span class="modal-tag">${i.trim()}</span>`).join(''):'';
  const featTags=feat?feat.split(/[،,]+/).filter(x=>x.trim()).map(f=>`<span class="modal-tag">✓ ${f.trim()}</span>`).join(''):'';
  content.innerHTML=`${item.image_url?`<img class="modal-img" src="${item.image_url}" alt="${name}">`:`<div class="modal-img-ph">${FOOD_SVG}</div>`}<div class="modal-body"><div class="modal-name">${name}</div><div class="modal-price">${priceHTML(item.price)}</div>${desc?`<div class="modal-section-title">${t().desc}</div><div class="modal-text">${desc}</div>`:''}${ingrTags?`<div class="modal-section-title">${t().ingredients}</div><div class="modal-tags">${ingrTags}</div>`:''}${featTags?`<div class="modal-section-title">${t().features}</div><div class="modal-tags">${featTags}</div>`:''}<button class="modal-cart-btn" onclick="addToCart(${item.id},'${name.replace(/'/g,"\\'")}',${item.price});closeModal()"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4h-.01l-1.1 2-2.76 5H8.53l-.13-.27L6.16 6l-.95-2-.94-2H1v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 15.1 6.1 16 7 16h12v-2H7.42c-.13 0-.25-.11-.25-.25z"/></svg> ${t().add}</button></div>`;
}
function closeModal(){document.getElementById('item-modal').classList.remove('open');document.body.style.overflow='';}
function goBack(){document.getElementById('cat-view').style.display='none';document.getElementById('home-view').style.display='block';history.pushState({},'',location.pathname);window.scrollTo(0,0);loadCategories();}
window.addEventListener('popstate',e=>{if(e.state?.catId)showCategory(e.state.catId);else{document.getElementById('cat-view').style.display='none';document.getElementById('home-view').style.display='block';}});
window.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

function enableSmartSlider() {
  const slider = document.querySelector('.slider-wrap');
  if (!slider) return;

  let isDown = false;
  let startX;
  let scrollLeft;
  let autoPlayTick;

  // 1. وظيفة التحريك التلقائي المتوافقة مع RTL
  const startAutoPlay = () => {
    // نتأكد من عدم وجود أكثر من Timer واحد يعمل
    clearInterval(autoPlayTick); 
    
    autoPlayTick = setInterval(() => {
      const isRTL = document.documentElement.dir === 'rtl';
      
      if (isRTL) {
        // في الـ RTL، نتحرك لليسار بتقليل القيمة
        slider.scrollLeft -= 1;
        
        // المتصفحات تختلف في تقييم scrollLeft للـ RTL (بعضها سالب وبعضها موجب)
        // Math.abs يحل هذه المشكلة.
        if (Math.abs(slider.scrollLeft) >= (slider.scrollWidth / 2)) {
          slider.scrollLeft = 0;
        }
      } else {
        slider.scrollLeft += 1;
        if (slider.scrollLeft >= (slider.scrollWidth / 2)) {
          slider.scrollLeft = 0;
        }
      }
    }, 25); // سرعة التحريك (كلما قل الرقم زادت السرعة)
  };

  const stopAutoPlay = () => clearInterval(autoPlayTick);

  // 2. التحكم عن طريق السحب بالماوس
  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.style.cursor = 'grabbing';
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
    stopAutoPlay();
  });

  slider.addEventListener('mouseleave', () => {
    if (isDown) {
      isDown = false;
      slider.style.cursor = 'grab';
      startAutoPlay();
    }
  });

  slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.style.cursor = 'grab';
    startAutoPlay();
  });

  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2; // سرعة السحب اليدوي
    slider.scrollLeft = scrollLeft - walk;
  });

  // 3. التحكم عن طريق اللمس (للجوال)
  slider.addEventListener('touchstart', stopAutoPlay, {passive: true});
  slider.addEventListener('touchend', startAutoPlay);

  // بدء التحريك
  startAutoPlay();
}

// تأخير التشغيل قليلاً لضمان أن الـ API جلب الصور وبنى الـ DOM
setTimeout(enableSmartSlider, 1500);

// ── البحث الشامل ───────────────────────────────────────────
let _allItems = []; // كاش كل الوجبات

async function globalSearch(query) {
  const q       = query.trim();
  const clear   = document.getElementById('search-clear');
  const results = document.getElementById('search-results');
  const empty   = document.getElementById('search-empty');
  const cats    = document.querySelector('.cats-wrap');

  clear.style.display = q ? 'block' : 'none';

  if (!q) {
    results.style.display = 'none';
    empty.style.display   = 'none';
    cats.style.display    = '';
    return;
  }

  // جلب كل الوجبات مرة واحدة وتخزينها
  if (!_allItems.length) {
    try {
      const res  = await fetch('api/menu.php?action=all_items');
      const data = await res.json();
      if (data.success) _allItems = data.data;
    } catch(e) { return; }
  }

  const lq      = q.toLowerCase();
  const matched = _allItems.filter(i =>
    (i.name_ar||'').includes(q) ||
    (i.name_en||'').toLowerCase().includes(lq) ||
    (i.description_ar||'').includes(q)
  );

  cats.style.display = 'none';

  if (!matched.length) {
    results.style.display = 'none';
    empty.style.display   = 'block';
    return;
  }

  empty.style.display   = 'none';
  results.style.display = 'grid';
  results.innerHTML = matched.map(item => `
    <div class="item-card search-result-card fade-in" onclick="openItemModal(${item.id})">
      ${item.image_url ? `<img class="item-img" src="${item.image_url}" alt="${S.lang==='ar'?item.name_ar:item.name_en}" loading="lazy">` : `<div class="item-img-ph">${FOOD_SVG}</div>`}
      <div class="item-body">
        <div class="item-name">${S.lang==='ar'?item.name_ar:item.name_en}</div>
        <div class="item-footer">
          <div class="item-price">${priceHTML(item.price)}</div>
          <button class="add-to-cart-btn" onclick="event.stopPropagation();addToCart(${item.id},'${(S.lang==='ar'?item.name_ar:item.name_en).replace(/'/g,"\\'")}',${item.price})">
            ${t().add}
          </button>
        </div>
      </div>
    </div>`).join('');
}

function filterItems(q) { /* متروكة للتوافق */ }

function clearSearch() {
  const input = document.getElementById('search-input');
  input.value = '';
  globalSearch('');
  input.focus();
}

// ── متابعة حالة الطلب ──────────────────────────────────────
let _pollTimer = null;
let _activeOrderNum = null;

function startPolling(orderNum) {
  stopPolling();
  _activeOrderNum = orderNum;
  _pollTimer = setInterval(() => checkOrderStatus(orderNum), 8000);
  checkOrderStatus(orderNum); // فحص فوري
}

function stopPolling() {
  if (_pollTimer) { clearInterval(_pollTimer); _pollTimer = null; }
}

async function checkOrderStatus(orderNum) {
  try {
    const res  = await fetch(`api/orders.php?action=status&order_num=${orderNum}`);
    const data = await res.json();
    if (!data.success) return;

    const s = t().status[data.status];
    if (s) {
      // تحديث الحالة في القائمة إذا كانت مفتوحة
      const saved = JSON.parse(localStorage.getItem('my_orders') || '[]');
      const idx = saved.findIndex(o => o.order_num === orderNum);
      if (idx !== -1) { saved[idx].status = data.status; localStorage.setItem('my_orders', JSON.stringify(saved)); }
      // إذا كانت القائمة مفتوحة، أعد رسمها
      const overlay = document.getElementById('cart-overlay');
      if (overlay.classList.contains('open')) {
        const icon = document.getElementById('order-status-icon');
        const text = document.getElementById('order-status-text');
        if (icon && icon.style.display !== 'none') { icon.textContent = s.icon; }
        if (text && text.querySelector) {
          // multi-order view — لا تعدّل النص يدوياً، أعد رسم القائمة
        } else if (text) {
          text.textContent = s.text + '  (' + orderNum + ')';
        }
      }
    }

    // تحديث حالة الطلب في localStorage
    const saved = JSON.parse(localStorage.getItem('my_orders') || '[]');
    const idx = saved.findIndex(o => o.order_num === orderNum);
    if (idx !== -1) { saved[idx].status = data.status; localStorage.setItem('my_orders', JSON.stringify(saved)); }

    if (['rejected','delivered'].includes(data.status)) {
      stopPolling();
      // بعد رفض أو تسليم، أخفِ الزر بعد دقيقتين
      setTimeout(() => hideTrackBtn(), 120000);
    }
  } catch(e) { /* تجاهل أخطاء الشبكة */ }
}

// ── زر تابع طلبك ──────────────────────────────────────────
function initTrackBtn() {
  const saved = JSON.parse(localStorage.getItem('my_orders') || '[]');
  if (!saved.length) return;
  // أظهر الزر إذا كان هناك أي طلب نشط خلال آخر ساعتين
  const active = saved.filter(o => {
    const age = Date.now() - o.ts;
    return age < 7200000 && !['delivered','rejected'].includes(o.status || '');
  });
  if (active.length) showTrackBtn(active[0].order_num);
}

function showTrackBtn(orderNum) {
  const btn   = document.getElementById('track-order-btn');
  const label = document.getElementById('track-order-label');
  if (!btn) return;
  if (label) label.textContent = t().lang === 'en' ? 'Track Your Order' : 'تابع طلبك';
  btn.style.display = 'flex';
  _activeOrderNum = orderNum;
}

function hideTrackBtn() {
  const btn = document.getElementById('track-order-btn');
  if (btn) btn.style.display = 'none';
}

function openTrackOrder() {
  const saved = JSON.parse(localStorage.getItem('my_orders') || '[]');
  if (!saved.length) return;

  // عرض قائمة بكل الطلبات بدلاً من طلب واحد
  document.getElementById('cart-form').style.display    = 'none';
  const statusDiv = document.getElementById('order-status');
  statusDiv.style.display = 'flex';
  statusDiv.style.flexDirection = 'column';
  statusDiv.style.gap = '10px';
  statusDiv.style.padding = '1rem';
  statusDiv.style.overflowY = 'auto';

  // بناء قائمة الطلبات
  const listHtml = saved.map(o => {
    const s      = t().status[o.status || 'pending'];
    const ageMin = Math.round((Date.now() - o.ts) / 60000);
    const timeStr = ageMin < 60 ? `منذ ${ageMin} دق` : `منذ ${Math.round(ageMin/60)} س`;
    return `<div style="background:rgba(0,0,0,.3);border:1px solid rgba(212,175,55,.2);border-radius:12px;padding:12px 14px;text-align:right">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <span style="color:#d4af37;font-weight:800;font-size:13px">${o.order_num}</span>
        <span style="font-size:11px;color:rgba(200,170,100,.4)">${timeStr}</span>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:24px">${s?.icon || '⏳'}</span>
        <span style="font-size:13px;color:#fff;line-height:1.4">${s?.text || ''}</span>
      </div>
    </div>`;
  }).join('');

  document.getElementById('order-status-icon').style.display = 'none';
  document.getElementById('order-status-text').innerHTML =
    `<div style="width:100%;font-family:'Cairo',sans-serif">
       <div style="font-size:15px;font-weight:700;color:#d4af37;margin-bottom:12px;text-align:center">
         ${S.lang==='ar'?'طلباتك':'Your Orders'} (${saved.length})
       </div>
       <div style="display:flex;flex-direction:column;gap:8px">${listHtml}</div>
     </div>`;

  document.getElementById('cart-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';

  // polling لأحدث طلب نشط
  const active = saved.find(o => !['delivered','rejected'].includes(o.status || ''));
  if (active) startPolling(active.order_num);
}

// تشغيل الزر عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', initTrackBtn);
</script>
<!-- شريط السلة السفلي -->
<div class="cart-bar" id="cart-bar" style="display:none" onclick="openCartModal()">
  <div class="cart-bar-inner">
    <span class="cart-bar-count" id="cart-bar-count">0</span>
    <span class="cart-bar-label">عرض السلة</span>
    <span class="cart-bar-total" id="cart-bar-total">0 ل.س</span>
  </div>
</div>

<!-- مودال السلة + نموذج الطلب -->
<div class="cart-overlay" id="cart-overlay" onclick="if(event.target===this)closeCartModal()">
  <div class="cart-modal" id="cart-modal">
    <div class="cart-modal-header">
      <span class="cart-modal-title">سلة الطلبات</span>
      <button class="cart-modal-close" onclick="closeCartModal()">✕</button>
    </div>

    <!-- قائمة العناصر -->
    <div class="cart-items-list" id="cart-items-list"></div>

    <!-- الإجمالي -->
    <div class="cart-total-row">
      <span>الإجمالي</span>
      <span id="cart-total-display">0 ل.س</span>
    </div>

    <!-- نموذج بيانات الزبون -->
    <div class="cart-form" id="cart-form">
      <div class="cart-form-title">بيانات الطلب</div>
      <input type="text" id="order-name" class="cart-input" placeholder="الاسم *" maxlength="80">
      <input type="tel" id="order-phone" class="cart-input" placeholder="رقم الهاتف *" maxlength="20" required>
      <textarea id="order-notes" class="cart-input cart-textarea" placeholder="ملاحظات إضافية (اختياري)" rows="3" maxlength="300"></textarea>
      <button class="cart-submit-btn" id="cart-submit-btn" onclick="submitOrder()">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        <span id="submit-btn-text">إرسال الطلب</span>
      </button>
    </div>

    <!-- حالة الطلب بعد الإرسال -->
    <div class="order-status" id="order-status" style="display:none">
      <div class="order-status-icon" id="order-status-icon">⏳</div>
      <div class="order-status-text" id="order-status-text">جارٍ إرسال طلبك...</div>
    </div>
  </div>
</div>

<script>
// ── السلة ────────────────────────────────────────────────────
let CART = []; // [{id, name, price, qty}]

function addToCart(id, name, price) {
  const existing = CART.find(i => i.id === id);
  if (existing) {
    existing.qty++;
  } else {
    CART.push({ id, name, price, qty: 1 });
  }
  updateCartBar();
  showCartToast(name);
}

function removeFromCart(id) {
  CART = CART.filter(i => i.id !== id);
  updateCartBar();
  renderCartItems();
}

function changeQty(id, delta) {
  const item = CART.find(i => i.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) { removeFromCart(id); return; }
  updateCartBar();
  renderCartItems();
}

function cartTotal() {
  return CART.reduce((s, i) => s + i.price * i.qty, 0);
}

function updateCartBar() {
  const bar   = document.getElementById('cart-bar');
  const count = CART.reduce((s, i) => s + i.qty, 0);
  document.getElementById('cart-bar-count').textContent = count;
  document.getElementById('cart-bar-total').textContent = cartTotal().toLocaleString('ar-SY') + ' ل.س';
  bar.style.display = CART.length ? 'flex' : 'none';
}

function showCartToast(name) {
  const el = document.createElement('div');
  el.className = 'cart-toast';
  el.textContent = `${t().added} ${name}`;
  document.body.appendChild(el);
  requestAnimationFrame(() => el.classList.add('show'));
  setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 2000);
}

function renderCartItems() {
  const list = document.getElementById('cart-items-list');
  if (!CART.length) {
    list.innerHTML = `<div class="cart-empty">${t().cart_empty}</div>`;
    document.getElementById('cart-total-display').textContent = '0 ل.س';
    return;
  }
  list.innerHTML = CART.map(item => `
    <div class="cart-item">
      <div class="cart-item-name">${item.name}</div>
      <div class="cart-item-controls">
        <button class="qty-btn" onclick="changeQty(${item.id},-1)">−</button>
        <span class="qty-num">${item.qty}</span>
        <button class="qty-btn" onclick="changeQty(${item.id},1)">+</button>
        <span class="cart-item-price">${(item.price * item.qty).toLocaleString('ar-SY')} ل.س</span>
        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">🗑</button>
      </div>
    </div>`).join('');
  document.getElementById('cart-total-display').textContent = cartTotal().toLocaleString('ar-SY') + ' ل.س';
}

function openCartModal() {
  renderCartItems();
  // ✅ استئناف: إذا كان هناك طلب معلق من جلسة سابقة، اعرضه
  const savedOrders = JSON.parse(localStorage.getItem('my_orders') || '[]');
  const lastOrder = savedOrders[0];
  const isPending = lastOrder && (Date.now() - lastOrder.ts) < 3600000; // أقل من ساعة
  if (isPending && CART.length === 0) {
    document.getElementById('cart-form').style.display    = 'none';
    document.getElementById('order-status').style.display = 'flex';
    const savedStatus = lastOrder.status || 'pending';
    const statusObj   = t().status[savedStatus] || t().status.pending;
    document.getElementById('order-status-icon').textContent = statusObj.icon;
    document.getElementById('order-status-text').textContent = statusObj.text + '  (' + lastOrder.order_num + ')';
    startPolling(lastOrder.order_num);
  } else {
    document.getElementById('order-status').style.display = 'none';
    document.getElementById('order-status-icon').style.display = '';
    document.getElementById('cart-form').style.display    = 'block';
  }
  document.getElementById('cart-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeCartModal() {
  document.getElementById('cart-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

async function submitOrder() {
  if (!CART.length) return;

  const name  = document.getElementById('order-name').value.trim();
  const phone = document.getElementById('order-phone').value.trim();
  const notes = document.getElementById('order-notes').value.trim();
  if (!phone) { toast(S.lang==='ar'?'رقم الهاتف مطلوب':'Phone number is required','err'); return; }

  if (!name) {
    document.getElementById('order-name').focus();
    document.getElementById('order-name').style.borderColor = '#e57373';
    setTimeout(() => document.getElementById('order-name').style.borderColor = '', 2000);
    return;
  }

  const btn = document.getElementById('cart-submit-btn');
  btn.disabled = true;
  const sbt=document.getElementById('submit-btn-text');if(sbt)sbt.textContent=t().sending;

  try {
    const res = await fetch('api/orders.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        customer_name:  name,
        customer_phone: phone,
        notes:          notes,
        items:          CART.map(i=>({id:i.id, qty:i.qty})), // ✅ نرسل ID والكمية فقط، لا الأسعار
        total:          0
      })
    });
    const data = await res.json();

    document.getElementById('cart-form').style.display = 'none';
    document.getElementById('order-status').style.display = 'flex';

    if (data.success) {
      document.getElementById('order-status-icon').textContent = '⏳';
      document.getElementById('order-status-text').textContent = t().order_ok + '  (' + data.order_num + ')';
      // ✅ حفظ رقم الطلب في localStorage لمتابعته بعد إعادة التحميل
      const savedOrders = JSON.parse(localStorage.getItem('my_orders') || '[]');
      savedOrders.unshift({order_num: data.order_num, ts: Date.now(), status: 'pending'});
      // احتفظ بآخر 10 طلبات كحد أقصى
      localStorage.setItem('my_orders', JSON.stringify(savedOrders.slice(0, 10)));
      CART = [];
      updateCartBar();
      showTrackBtn(data.order_num); // ✅ إظهار زر التتبع
      startPolling(data.order_num);
    } else {
      document.getElementById('order-status-icon').textContent = '❌';
      document.getElementById('order-status-text').textContent = data.message || t().order_err;
    }
  } catch(e) {
    document.getElementById('cart-form').style.display = 'none';
    document.getElementById('order-status').style.display = 'flex';
    document.getElementById('order-status-icon').textContent = '❌';
    document.getElementById('order-status-text').textContent = t().conn_err;
  }

  btn.disabled = false;
  const st=document.getElementById('submit-btn-text');if(st)st.textContent=t().send_order;
}
</script>

</body>
</html>
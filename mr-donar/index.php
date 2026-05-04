<?php
$MENU_API = 'http://localhost/mr-mkh/api/menu.php?action=social_links';
// للسيرفر الحقيقي استبدل بـ: 'https://menu-r.mr-donar.com/api/menu.php?action=social_links'

$links = [];
try {
    $ctx = stream_context_create(['http'=>['timeout'=>3,'ignore_errors'=>true]]);
    $raw = @file_get_contents($MENU_API, false, $ctx);
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (!empty($decoded['success'])) $links = $decoded['data'];
    }
} catch (Throwable $e) {}

$count     = count($links);
$angleDeg  = $count > 0 ? 360.0 / $count : 72.0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="icon" href="logo.jpeg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mr. Donar | مستر دونر</title>
    <meta name="description" content="مستر دونر - مطعم الشاورما الأول.">
    <meta property="og:image" content="logo.jpeg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #000;
            background-image: url('pattern.png');
            background-repeat: repeat;
            background-size: 150px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.70);
            z-index: 0;
        }

        .logo-container {
            margin-bottom: 50px;
            text-align: center;
            z-index: 10;
            position: relative;
        }

        .logo-container img {
            width: 250px;
            max-width: 80vw;
            border-radius: 50%;
            box-shadow: 0 0 40px rgba(212,175,55,0.15);
            transition: 0.3s ease-in-out;
        }

        .logo-container img:hover {
            box-shadow: 0 0 60px rgba(212,175,55,0.3);
            transform: scale(1.02);
        }

        /* ─── القائمة الدائرية ─── */
        .menu {
            position: relative;
            z-index: 10;
            width: 240px;
            height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* زر التبديل المركزي */
        .toggle {
            position: relative;
            height: 65px;
            width: 65px;
            background: #d4af37;
            border-radius: 50%;
            box-shadow: 0 3px 15px rgba(212,175,55,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            cursor: pointer;
            transition: 0.5s;
            z-index: 1000;
        }

        .click-text {
            position: absolute;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.1;
            font-family: Arial, sans-serif;
            transition: 0.3s;
            pointer-events: none;
        }

        .close-icon {
            position: absolute;
            opacity: 0;
            transform: scale(0);
            transition: 0.3s;
            display: flex;
            pointer-events: none;
        }

        .menu.active .toggle      { transform: rotate(180deg); }
        .menu.active .click-text  { opacity: 0; transform: scale(0); }
        .menu.active .close-icon  { opacity: 1; transform: scale(1.2); }

        /* ─── عناصر القائمة ─── */
        .menu li {
            position: absolute;
            /* تحديد نقطة الدوران من مركز الـ toggle */
            left: 50%;
            top: 50%;
            margin-left: -22.5px;  /* نصف عرض الـ a */
            margin-top: -22.5px;
            list-style: none;
            transform-origin: 22.5px 22.5px;   /* مركز الأيقونة نفسها */
            transition: 0.5s;
            transition-delay: calc(0.1s * var(--i));
            /* مخفية في المنتصف قبل الفتح */
            transform: rotate(0deg) translateX(0px);
            opacity: 0;
            pointer-events: none;
        }

        .menu.active li {
            transform: rotate(calc(<?= $angleDeg ?>deg * var(--i) + 90deg)) translateX(110px);
            opacity: 1;
            pointer-events: auto;
        }

        .menu li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 50%;
            /* نعكس دوران الـ li حتى تبقى الأيقونة مستقيمة */
            transform: rotate(calc(-1 * (<?= $angleDeg ?>deg * var(--i) + 90deg)));
            color: var(--clr);
            box-shadow: 0 3px 4px rgba(0,0,0,0.15);
            transition: color 0.3s, background 0.3s, box-shadow 0.3s;
            text-decoration: none;
            overflow: hidden;
        }

        .menu li a:hover {
            color: #fff;
            background: var(--clr);
            box-shadow: 0 0 10px var(--clr), 0 0 30px var(--clr);
        }

        .menu li a svg { pointer-events: none; }

        .menu li a img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <img src="logo.jpeg" alt="Mr. Donar Logo">
    </div>

    <ul class="menu" id="menu">
        <div class="toggle" id="toggle">
            <span class="click-text">Click<br>me</span>
            <span class="close-icon">
                <svg viewBox="0 0 384 512" width="20" height="20">
                    <path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                </svg>
            </span>
        </div>

        <?php
        // رابط قاعدة موقع المنيو — يُستخدم لعرض الأيقونات المرفوعة
        // على localhost يشير لـ mr-mkh، على السيرفر لـ menu-r.mr-donar.com
        $MENU_BASE = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
            ? 'http://localhost/mr-mkh/'
            : 'https://menu-r.mr-donar.com/';

        foreach ($links as $i => $link):
            // إصلاح الروابط التي لا تحتوي على بروتوكول
            $rawUrl = trim($link['url']);
            if ($rawUrl !== '' &&
                !str_starts_with($rawUrl, 'http://') &&
                !str_starts_with($rawUrl, 'https://') &&
                !str_starts_with($rawUrl, 'tel:') &&
                !str_starts_with($rawUrl, 'mailto:') &&
                !str_starts_with($rawUrl, '//')) {
                $rawUrl = 'https://' . $rawUrl;
            }

            $isExternal = !str_starts_with($rawUrl, 'tel:') && !str_starts_with($rawUrl, 'mailto:');
            $target     = $isExternal ? 'target="_blank" rel="noopener"' : '';
            $color      = htmlspecialchars($link['color']);
            $url        = htmlspecialchars($rawUrl);
            $label      = htmlspecialchars($link['label']);
        ?>
        <li style="--i:<?= $i ?>;--clr:<?= $color ?>;">
            <a href="<?= $url ?>" <?= $target ?> title="<?= $label ?>">
                <?php if ($link['icon_type'] === 'upload' && !empty($link['icon_url'])): ?>
                    <img src="<?= $MENU_BASE . htmlspecialchars($link['icon_url']) ?>" alt="<?= $label ?>">
                <?php elseif ($link['icon_type'] === 'svg' && !empty($link['icon_value'])): ?>
                    <?= $link['icon_value'] ?>
                <?php else: ?>
                    <?= mb_substr($label, 0, 1) ?>
                <?php endif; ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <script>
        const toggle = document.getElementById('toggle');
        const menu   = document.getElementById('menu');
        toggle.onclick = () => menu.classList.toggle('active');
        window.addEventListener('keydown', e => { if(e.key==='Escape') menu.classList.remove('active'); });
    </script>
</body>
</html>
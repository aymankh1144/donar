<?php
require_once __DIR__ . '/../core/auth.php';

if (Auth::isLoggedIn()) {
    header('Location: /dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجري - دخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .container {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
        .left { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 2rem; display: flex; flex-direction: column; justify-content: center; color: white; }
        .left h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .left p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem; line-height: 1.8; }
        .features { list-style: none; }
        .features li {
            padding: 0.8rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .features li:before {
            content: '✓';
            display: inline-flex;
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        .right { padding: 3rem 2rem; display: flex; flex-direction: column; justify-content: center; }
        .logo { text-align: center; margin-bottom: 2rem; }
        .logo h2 { font-size: 1.8rem; color: #667eea; margin-bottom: 0.5rem; }
        .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; }
        .tab-btn { flex: 1; padding: 1rem; background: none; border: none; cursor: pointer; font-size: 1rem; font-weight: 600; color: #999; border-bottom: 3px solid transparent; transition: 0.3s; font-family: 'Cairo', sans-serif; }
        .tab-btn.active { color: #667eea; border-bottom-color: #667eea; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 0.8rem 1rem; border: 2px solid #f0f0f0; border-radius: 8px; font-size: 1rem; font-family: 'Cairo', sans-serif; transition: 0.3s; }
        .form-group input:focus { outline: none; border-color: #667eea; background: #f8f9ff; }
        .btn { width: 100%; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s; font-family: 'Cairo', sans-serif; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .error { background: #fee; color: #c33; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: none; }
        .success { background: #efe; color: #3c3; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: none; }
        .text-center { text-align: center; margin-top: 1rem; color: #666; }
        .text-center a { color: #667eea; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <h1>متجري</h1>
            <p>منصة متكاملة لإنشاء متجرك الإلكتروني في سوريا بسهولة وسرعة</p>
            <ul class="features">
                <li>متجر احترافي بدون تكاليف إضافية</li>
                <li>إدارة المنتجات والطلبات بكل سهولة</li>
                <li>تصميم متجاوب يعمل على جميع الأجهزة</li>
                <li>دعم اللغة العربية والإنجليزية</li>
                <li>نظام تحليلات قوي</li>
                <li>دعم فني متاح 24/7</li>
            </ul>
        </div>
        <div class="right">
            <div class="logo">
                <h2>متجري</h2>
                <p style="color: #999; font-size: 0.9rem;">منصة متاجرك الإلكترونية</p>
            </div>
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('login')">دخول</button>
                <button class="tab-btn" onclick="switchTab('register')">إنشاء حساب</button>
            </div>
            <div class="error" id="error"></div>
            <div class="success" id="success"></div>
            <div class="tab-content active" id="login-tab">
                <form id="login-form" onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label>اسم المستخدم أو البريد الإلكتروني</label>
                        <input type="text" id="login-username" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" id="login-password" required>
                    </div>
                    <button type="submit" class="btn">دخول</button>
                </form>
            </div>
            <div class="tab-content" id="register-tab">
                <form id="register-form" onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <label>اسم المتجر (بالعربية)</label>
                        <input type="text" id="register-store-ar" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المتجر (بالإنجليزية)</label>
                        <input type="text" id="register-store-en" required>
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" id="register-email" required>
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="tel" id="register-phone" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" id="register-username" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" id="register-password" required>
                    </div>
                    <button type="submit" class="btn">إنشاء حساب</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }

        async function handleLogin(e) {
            e.preventDefault();
            const username = document.getElementById('login-username').value;
            const password = document.getElementById('login-password').value;

            try {
                const response = await fetch('/api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = '/dashboard';
                } else {
                    showError(data.message || 'خطأ في الدخول');
                }
            } catch (err) {
                showError('حدث خطأ في الاتصال');
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const data = {
                store_name_ar: document.getElementById('register-store-ar').value,
                store_name_en: document.getElementById('register-store-en').value,
                email: document.getElementById('register-email').value,
                phone: document.getElementById('register-phone').value,
                username: document.getElementById('register-username').value,
                password: document.getElementById('register-password').value,
                store_slug: document.getElementById('register-store-ar').value.toLowerCase().replace(/\s+/g, '-')
            };

            try {
                const response = await fetch('/api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    showSuccess('تم إنشاء الحساب بنجاح! يمكنك الدخول الآن');
                    setTimeout(() => {
                        document.getElementById('login-username').value = data.username;
                        document.getElementById('login-password').value = data.password;
                        switchTab('login');
                    }, 1500);
                } else {
                    showError(result.message || 'خطأ في إنشاء الحساب');
                }
            } catch (err) {
                showError('حدث خطأ في الاتصال');
            }
        }

        function showError(msg) {
            const errorEl = document.getElementById('error');
            errorEl.textContent = msg;
            errorEl.style.display = 'block';
            setTimeout(() => errorEl.style.display = 'none', 5000);
        }

        function showSuccess(msg) {
            const successEl = document.getElementById('success');
            successEl.textContent = msg;
            successEl.style.display = 'block';
            setTimeout(() => successEl.style.display = 'none', 5000);
        }
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/../core/auth.php';
Auth::requireLogin();

$user = Auth::getUser();
$store = Auth::getStore();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - متجري</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: #f5f7fa; }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header h1 { font-size: 1.8rem; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .btn-logout { background: rgba(255, 255, 255, 0.2); border: 1px solid white; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; transition: 0.3s; }
        .btn-logout:hover { background: rgba(255, 255, 255, 0.3); }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; display: grid; grid-template-columns: 250px 1fr; gap: 2rem; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            height: fit-content;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .sidebar h3 { font-size: 0.9rem; color: #999; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar ul { list-style: none; }
        .sidebar li {
            margin-bottom: 0.5rem;
            border-radius: 6px;
            overflow: hidden;
        }
        .sidebar a {
            display: block;
            padding: 0.8rem 1rem;
            color: #666;
            text-decoration: none;
            transition: 0.3s;
            font-weight: 500;
        }
        .sidebar a:hover { background: #f0f0f0; color: #667eea; }
        .sidebar a.active { background: #667eea; color: white; }
        .main { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; }
        .tab-btn { padding: 1rem; background: none; border: none; cursor: pointer; font-size: 1rem; font-weight: 600; color: #999; border-bottom: 3px solid transparent; transition: 0.3s; font-family: 'Cairo', sans-serif; }
        .tab-btn.active { color: #667eea; border-bottom-color: #667eea; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.8rem 1rem; border: 2px solid #f0f0f0; border-radius: 8px; font-size: 1rem; font-family: 'Cairo', sans-serif; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; background: #f8f9ff; }
        .btn { padding: 0.8rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; font-family: 'Cairo', sans-serif; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; }
        .stat-card h4 { font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem; }
        .stat-card .value { font-size: 2rem; font-weight: 800; }
        .color-picker { display: flex; gap: 1rem; flex-wrap: wrap; }
        .color-input { width: 80px; height: 80px; border: 2px solid #f0f0f0; border-radius: 12px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>متجري</h1>
            <p style="opacity: 0.9; font-size: 0.9rem; margin-top: 0.3rem;">أهلاً بك، <?= htmlspecialchars($store['store_name_ar'] ?? '') ?></p>
        </div>
        <div class="user-menu">
            <span><?= htmlspecialchars($user['username'] ?? '') ?></span>
            <button class="btn-logout" onclick="logout()">تسجيل الخروج</button>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>القائمة</h3>
            <ul>
                <li><a href="#" class="nav-link active" onclick="switchTab('dashboard')">لوحة التحكم</a></li>
                <li><a href="#" class="nav-link" onclick="switchTab('store')">إعدادات المتجر</a></li>
                <li><a href="#" class="nav-link" onclick="switchTab('products')">المنتجات</a></li>
                <li><a href="#" class="nav-link" onclick="switchTab('categories')">الفئات</a></li>
                <li><a href="#" class="nav-link" onclick="switchTab('orders')">الطلبات</a></li>
            </ul>
        </div>

        <div class="main">
            <!-- Dashboard Tab -->
            <div class="tab-content active" id="dashboard-tab">
                <h2>لوحة التحكم</h2>
                <div class="stats" id="stats-container">
                    <div class="stat-card">
                        <h4>إجمالي المنتجات</h4>
                        <div class="value" id="stat-products">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>إجمالي الطلبات</h4>
                        <div class="value" id="stat-orders">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>إجمالي العملاء</h4>
                        <div class="value" id="stat-customers">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>إجمالي الإيرادات</h4>
                        <div class="value" id="stat-revenue">-</div>
                    </div>
                </div>
            </div>

            <!-- Store Settings Tab -->
            <div class="tab-content" id="store-tab">
                <h2>إعدادات المتجر</h2>
                <form onsubmit="saveStoreSettings(event)" style="max-width: 600px;">
                    <div class="form-group">
                        <label>اسم المتجر (عربي)</label>
                        <input type="text" id="store-name-ar" value="<?= htmlspecialchars($store['store_name_ar'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المتجر (إنجليزي)</label>
                        <input type="text" id="store-name-en" value="<?= htmlspecialchars($store['store_name_en'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea id="store-description-ar" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>الألوان الرئيسية</label>
                        <div class="color-picker">
                            <input type="color" id="color-primary" class="color-input" value="<?= $store['color_primary'] ?? '#D4AF37' ?>" title="اللون الأساسي">
                            <input type="color" id="color-secondary" class="color-input" value="<?= $store['color_secondary'] ?? '#C8C8C8' ?>" title="اللون الثانوي">
                            <input type="color" id="color-accent" class="color-input" value="<?= $store['color_accent'] ?? '#A67C00' ?>" title="لون التمييز">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="tel" id="contact-phone" value="<?= htmlspecialchars($store['contact_phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>واتساب</label>
                        <input type="tel" id="contact-whatsapp" value="<?= htmlspecialchars($store['contact_whatsapp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" id="contact-email" value="<?= htmlspecialchars($store['contact_email'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn">حفظ الإعدادات</button>
                </form>
            </div>

            <!-- Products Tab -->
            <div class="tab-content" id="products-tab">
                <h2>المنتجات</h2>
                <button class="btn" onclick="showProductForm()" style="margin-bottom: 1.5rem;">إضافة منتج جديد</button>
                <div id="products-list"></div>
            </div>

            <!-- Categories Tab -->
            <div class="tab-content" id="categories-tab">
                <h2>الفئات</h2>
                <button class="btn" onclick="showCategoryForm()" style="margin-bottom: 1.5rem;">إضافة فئة جديدة</button>
                <div id="categories-list"></div>
            </div>

            <!-- Orders Tab -->
            <div class="tab-content" id="orders-tab">
                <h2>الطلبات</h2>
                <div id="orders-list"></div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
            document.getElementById(tab + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            if (tab === 'dashboard') {
                loadDashboard();
            } else if (tab === 'products') {
                loadProducts();
            } else if (tab === 'categories') {
                loadCategories();
            } else if (tab === 'orders') {
                loadOrders();
            }
        }

        async function loadDashboard() {
            try {
                const response = await fetch('/api/stores.php?action=dashboard');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('stat-products').textContent = data.data.total_products || 0;
                    document.getElementById('stat-orders').textContent = data.data.total_orders || 0;
                    document.getElementById('stat-customers').textContent = data.data.total_customers || 0;
                    document.getElementById('stat-revenue').textContent = (data.data.total_revenue || 0) + ' ل.س';
                }
            } catch (err) {
                console.error('Error loading dashboard:', err);
            }
        }

        async function loadProducts() {
            try {
                const response = await fetch('/api/products.php?action=list');
                const data = await response.json();
                if (data.success) {
                    const list = document.getElementById('products-list');
                    list.innerHTML = data.data.map(p => `
                        <div style="padding: 1rem; border: 1px solid #eee; border-radius: 8px; margin-bottom: 1rem;">
                            <h4>${p.name_ar}</h4>
                            <p>${p.description_ar}</p>
                            <p><strong>السعر:</strong> ${p.price} ل.س</p>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error('Error loading products:', err);
            }
        }

        async function loadCategories() {
            try {
                const response = await fetch('/api/categories.php?action=list');
                const data = await response.json();
                if (data.success) {
                    const list = document.getElementById('categories-list');
                    list.innerHTML = data.data.map(c => `
                        <div style="padding: 1rem; border: 1px solid #eee; border-radius: 8px; margin-bottom: 1rem;">
                            <h4>${c.name_ar} ${c.icon}</h4>
                            <p>${c.description_ar || 'بدون وصف'}</p>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error('Error loading categories:', err);
            }
        }

        async function loadOrders() {
            try {
                const response = await fetch('/api/orders.php?action=list');
                const data = await response.json();
                if (data.success) {
                    const list = document.getElementById('orders-list');
                    list.innerHTML = data.data.map(o => `
                        <div style="padding: 1rem; border: 1px solid #eee; border-radius: 8px; margin-bottom: 1rem;">
                            <h4>${o.order_number}</h4>
                            <p><strong>العميل:</strong> ${o.customer_name}</p>
                            <p><strong>المبلغ:</strong> ${o.total_amount} ل.س</p>
                            <p><strong>الحالة:</strong> ${o.status}</p>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error('Error loading orders:', err);
            }
        }

        async function saveStoreSettings(e) {
            e.preventDefault();
            const data = {
                store_name_ar: document.getElementById('store-name-ar').value,
                store_name_en: document.getElementById('store-name-en').value,
                description_ar: document.getElementById('store-description-ar').value,
                color_primary: document.getElementById('color-primary').value,
                color_secondary: document.getElementById('color-secondary').value,
                color_accent: document.getElementById('color-accent').value,
                contact_phone: document.getElementById('contact-phone').value,
                contact_whatsapp: document.getElementById('contact-whatsapp').value,
                contact_email: document.getElementById('contact-email').value
            };

            try {
                const response = await fetch('/api/stores.php?action=update-settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                alert(result.message);
            } catch (err) {
                alert('حدث خطأ: ' + err.message);
            }
        }

        function showProductForm() {
            alert('سيتم إضافة نموذج إضافة المنتجات قريباً');
        }

        function showCategoryForm() {
            alert('سيتم إضافة نموذج إضافة الفئات قريباً');
        }

        async function logout() {
            if (confirm('هل تريد تسجيل الخروج؟')) {
                await fetch('/api/auth.php?action=logout', { method: 'POST' });
                window.location.href = '/';
            }
        }

        // Load dashboard on page load
        loadDashboard();
    </script>
</body>
</html>

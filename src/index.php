<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSH Supermarket - دخول / تسجيل</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --green: #1A6B3C; --orange: #F4A024; --bg: #f4f7f6; }
        body { font-family: 'Cairo', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-container { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .logo { font-size: 2.5rem; font-weight: 900; color: var(--green); text-align: center; margin-bottom: 10px; }
        .logo span { color: var(--orange); }
        .tabs { display: flex; margin-bottom: 25px; border-bottom: 2px solid #eee; }
        .tab { flex: 1; text-align: center; padding: 10px; cursor: pointer; font-weight: 600; color: #888; }
        .tab.active { color: var(--green); border-bottom: 3px solid var(--green); }
        .form-panel { display: none; }
        .form-panel.active { display: block; }
        .form-group { margin-bottom: 15px; text-align: right; }
        label { display: block; font-size: 0.9rem; margin-bottom: 5px; color: #444; font-weight: 600; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Cairo'; }
        input:focus { border-color: var(--green); outline: none; background: #f9fffb; }
        .btn { width: 100%; padding: 12px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .btn:hover { background: #14522d; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; display: none; font-size: 0.85rem; text-align: center; }
        .error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="logo">H<span>S</span>H</div>
    
    <div class="tabs">
        <div class="tab active" onclick="switchTab('login', this)">دخول</div>
        <div class="tab" onclick="switchTab('register', this)">حساب جديد</div>
    </div>

    <div id="authAlert" class="alert"></div>

    <div id="loginPanel" class="form-panel active">
        <form onsubmit="handleAuth(event, 'login')">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">تسجيل الدخول</button>
        </form>
    </div>

    <div id="registerPanel" class="form-panel">
        <form onsubmit="handleAuth(event, 'register')">
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>الاسم الأول</label>
                    <input type="text" name="firstName" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>اسم العائلة</label>
                    <input type="text" name="lastName" required>
                </div>
            </div>
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>رقم الهاتف</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>المدينة</label>
                <input type="text" name="city">
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">إنشاء حساب</button>
        </form>
    </div>
</div>

<script>
    function switchTab(type, el) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        document.getElementById(type + 'Panel').classList.add('active');
        document.getElementById('authAlert').style.display = 'none';
    }

    async function handleAuth(event, action) {
        event.preventDefault();
        const alertBox = document.getElementById('authAlert');
        const formData = new FormData(event.target);
        formData.append('action', action);

        try {
            const response = await fetch('auth.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                alertBox.className = 'alert success';
                alertBox.textContent = 'تم بنجاح! جاري التحويل...';
                alertBox.style.display = 'block';
                	setTimeout(() => { window.location.href = result.redirect; }, 1500);
            } else {
                alertBox.className = 'alert error';
                alertBox.textContent = result.error;
                alertBox.style.display = 'block';
            }
        } catch (e) {
            alertBox.className = 'alert error';
            alertBox.textContent = 'حدث خطأ في الاتصال بالسيرفر';
            alertBox.style.display = 'block';
        }
    }
</script>

</body>
</html>


<?php $__env->startSection('title', 'تسجيل الدخول - El Dokan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 30px; color: #667eea;">تسجيل الدخول</h2>
        
        <div class="alert alert-info">
            <strong>ملاحظة:</strong> هذه صفحة عرض فقط. تسجيل الدخول الفعلي متاح عبر API فقط.
            <br><br>
            <strong>للتسجيل الدخول:</strong> استخدم نقطة النهاية التالية:
            <br><code>POST /api/auth/login</code>
        </div>

        <form id="loginForm" style="margin-top: 20px;">
            <div class="form-group">
                <label for="phone">رقم الهاتف</label>
                <input type="tel" id="phone" name="phone" placeholder="01000000000" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                تسجيل الدخول
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p>ليس لديك حساب؟ <a href="/api/auth/register" style="color: #667eea;">سجل الآن</a></p>
        </div>
    </div>

    <!-- API Documentation -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="color: #667eea; margin-bottom: 15px;">كيفية تسجيل الدخول عبر API</h3>
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 14px;">
            <strong>POST</strong> http://localhost:8000/api/auth/login<br><br>
            <strong>Headers:</strong><br>
            Content-Type: application/json<br>
            Accept: application/json<br><br>
            <strong>Body:</strong><br>
            {<br>
            &nbsp;&nbsp;"phone": "01000000000",<br>
            &nbsp;&nbsp;"password": "password"<br>
            }
        </div>
    </div>

    <!-- Test Credentials -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="color: #667eea; margin-bottom: 15px;">بيانات تجريبية</h3>
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffc107;">
            <p><strong>المدير:</strong></p>
            <p>الهاتف: <code>01000000000</code></p>
            <p>كلمة المرور: <code>password</code></p>
            <hr style="margin: 15px 0;">
            <p><strong>مستخدم عادي:</strong></p>
            <p>الهاتف: <code>01000000001</code></p>
            <p>كلمة المرور: <code>password</code></p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        alert('هذه صفحة عرض فقط. لتسجيل الدخول الفعلي، يرجى استخدام API:\n\nPOST /api/auth/login\n\nمع البيانات:\n{\n  "phone": "01000000000",\n  "password": "password"\n}');
        
        // يمكنك إضافة كود AJAX هنا للاتصال بالـ API إذا أردت
        /*
        fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                phone: document.getElementById('phone').value,
                password: document.getElementById('password').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                localStorage.setItem('token', data.token);
                alert('تم تسجيل الدخول بنجاح!');
            } else {
                alert('فشل تسجيل الدخول: ' + (data.message || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            alert('حدث خطأ: ' + error.message);
        });
        */
    });
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\dokan\back\resources\views/login.blade.php ENDPATH**/ ?>
@extends('layouts.app')

@section('title', 'El Dokan - منصة التجارة الإلكترونية')

@section('content')
<div class="hero">
    <div class="container">
        <h1>🏪 مرحباً بك في El Dokan</h1>
        <p>منصة تجارة إلكترونية متكاملة للبيع والتسوق والاستشارات الطبية</p>
        <a href="/login" class="btn btn-primary">ابدأ الآن</a>
    </div>
</div>

<div class="container">
    <!-- Statistics -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number">50+</div>
            <div>متجر نشط</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">500+</div>
            <div>منتج متاح</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">20+</div>
            <div>طبيب معتمد</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">1000+</div>
            <div>عميل سعيد</div>
        </div>
    </div>

    <!-- Features -->
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 30px; color: #667eea;">المميزات الرئيسية</h2>
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🛒</div>
                <h3>التسوق الإلكتروني</h3>
                <p>تسوق من آلاف المنتجات من متاجر موثوقة مع توصيل سريع وآمن</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👨‍⚕️</div>
                <h3>الاستشارات الطبية</h3>
                <p>احجز استشارة مع أطباء معتمدين عبر الإنترنت أو في العيادات</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3>إدارة الوصفات</h3>
                <p>احفظ وادير وصفاتك الطبية مع تذكيرات الأدوية التلقائية</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>التوصيل السريع</h3>
                <p>نظام توصيل متقدم مع تتبع الطلبات في الوقت الفعلي</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>نظام مالي متقدم</h3>
                <p>إدارة محافظ إلكترونية ومدفوعات آمنة مع تقارير مالية مفصلة</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🗺️</div>
                <h3>نظام GPS</h3>
                <p>تتبع الموقع بدقة مع خرائط تفاعلية لتحديد العناوين</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>دعم فني 24/7</h3>
                <p>فريق دعم متاح على مدار الساعة لمساعدتك في أي وقت</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3>أمان عالي</h3>
                <p>حماية متقدمة لبياناتك ومدفوعاتك مع تشفير SSL</p>
            </div>
        </div>
    </div>

    <!-- Project Information -->
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 20px;">معلومات المشروع</h2>
        <div style="line-height: 2;">
            <p><strong>اسم المشروع:</strong> El Dokan (الدكان)</p>
            <p><strong>الإصدار:</strong> 1.0.0</p>
            <p><strong>الحالة:</strong> قيد التطوير</p>
            <p><strong>المنصة:</strong> Laravel 10.49.1</p>
            <p><strong>قاعدة البيانات:</strong> MySQL</p>
        </div>
    </div>

    <!-- API Information -->
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 20px;">واجهة برمجة التطبيقات (API)</h2>
        <div class="alert alert-info">
            <strong>ملاحظة:</strong> هذه منصة API. يمكنك الوصول إلى الواجهة البرمجية عبر:
            <br><code>http://localhost:8000/api</code>
        </div>
        <div style="margin-top: 20px;">
            <h3 style="margin-bottom: 15px;">نقاط النهاية الرئيسية:</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 5px;">
                    <strong>POST</strong> /api/auth/register - تسجيل مستخدم جديد
                </li>
                <li style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 5px;">
                    <strong>POST</strong> /api/auth/login - تسجيل الدخول
                </li>
                <li style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 5px;">
                    <strong>GET</strong> /api/products - قائمة المنتجات
                </li>
                <li style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 5px;">
                    <strong>GET</strong> /api/doctors - قائمة الأطباء
                </li>
                <li style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 5px;">
                    <strong>GET</strong> /api/shops - قائمة المتاجر
                </li>
            </ul>
        </div>
    </div>

    <!-- Technology Stack -->
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 20px;">التقنيات المستخدمة</h2>
        <div class="features">
            <div class="feature-card">
                <h4>Laravel Framework</h4>
                <p>إطار عمل PHP قوي وآمن</p>
            </div>
            <div class="feature-card">
                <h4>Laravel Sanctum</h4>
                <p>نظام المصادقة والتوثيق</p>
            </div>
            <div class="feature-card">
                <h4>MySQL Database</h4>
                <p>قاعدة بيانات علائقية موثوقة</p>
            </div>
            <div class="feature-card">
                <h4>Spatie Permissions</h4>
                <p>نظام الأدوار والصلاحيات</p>
            </div>
        </div>
    </div>
</div>
@endsection





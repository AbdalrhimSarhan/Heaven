# 🚀 متطلبات المشروع - الفصل الدراسي 2026

## 📖 نظرة عامة

هذا المشروع يطبق **10 متطلبات غير وظيفية** لنظام تجارة إلكترونية عالي الأداء.

---

## ⚙️ الإعداد الأولي

### 1. تثبيت المتطلبات
```bash
composer install
npm install
```

### 2. إنشاء ملف البيئة
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 3. إعداد قاعدة البيانات
```bash
# تحديث بيانات اتصال قاعدة البيانات في .env
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. تشغيل الـ Migrations
```bash
php artisan migrate:fresh --seed
```

**سيتم إنشاء:**
- ✅ المستخدمين (مع بيانات اختبار)
- ✅ الفئات
- ✅ المتاجر
- ✅ المنتجات
- ✅ ربط المتاجر بالمنتجات

---

## 🧪 الاختبار

### الخطوة 1: بدء خادم التطوير
```bash
php artisan serve
```

الخادم سيعمل على: `http://localhost:8000`

### الخطوة 2: تشغيل اختبار المتطلب الأول

**على Windows (PowerShell):**
```powershell
# اجعل من الممكن تشغيل scripts
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# شغل الاختبار
.\test-requirement-1.ps1
```

**على Linux/Mac:**
```bash
chmod +x test-requirement-1.sh
./test-requirement-1.sh
```

---

## 📊 المتطلبات المطبقة

### ✅ المتطلب #1: Concurrent Access & Data Integrity
- **الملفات:**
  - `app/Http/Controllers/CartItemController.php` - الطرق الآمنة
  - `routes/api.php` - الـ routes
  - `REQUIREMENT_1_DOCUMENTATION.md` - التوثيق الكامل

- **الـ Routes:**
  - `POST /api/auth/cart` - بدون حماية (للمقارنة)
  - `POST /api/auth/cart/safe` - مع Pessimistic Locking

- **الحماية المطبقة:**
  - ✅ Pessimistic Locking (`lockForUpdate()`)
  - ✅ Database Transactions
  - ✅ Row-Level Locks
  - ✅ ACID Compliance

---

## 🔐 الـ API Routes

### التوثيق
```bash
# موقع توثيق المشروع
REQUIREMENT_1_DOCUMENTATION.md    # شرح المتطلب الأول
TESTING_REQUIREMENTS.md           # شرح كل المتطلبات
```

### المصادقة
```bash
POST /api/auth/login
{
  "mobile": "0100123456",
  "password": "password"
}
```

**استجابة:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### السلة (Cart)
```bash
# إضافة للسلة (بدون حماية)
POST /api/auth/cart
Header: Authorization: Bearer TOKEN
{
  "product_id": 1,
  "store_id": 1,
  "quantity": 5
}

# إضافة للسلة (مع حماية)
POST /api/auth/cart/safe
Header: Authorization: Bearer TOKEN
{
  "product_id": 1,
  "store_id": 1,
  "quantity": 5
}

# عرض السلة
GET /api/auth/show/cart
Header: Authorization: Bearer TOKEN

# تحديث كمية العنصر
PUT /api/auth/cart/{cartItemId}
Header: Authorization: Bearer TOKEN
{
  "quantity": 10
}

# حذف من السلة
DELETE /api/auth/cart/{cartItemId}
Header: Authorization: Bearer TOKEN
```

---

## 📝 الـ Logging

جميع العمليات يتم تسجيلها في:
```
storage/logs/laravel.log
```

لعرض الـ logs في الوقت الفعلي:
```bash
tail -f storage/logs/laravel.log
```

**مثال على الـ Logs:**
```log
[2026-04-28 10:15:30] local.WARNING: ⚠️ [BASIC - NO LOCK] Adding to cart START
[2026-04-28 10:15:30] local.INFO: 📊 [BASIC] Current Stock Level
[2026-04-28 10:15:30] local.INFO: 📉 [BASIC] Inventory Decremented
[2026-04-28 10:15:30] local.INFO: ✅ [BASIC] SUCCESS

[2026-04-28 10:15:31] local.INFO: 🔒 [SAFE - WITH LOCK] Adding to cart START
[2026-04-28 10:15:31] local.INFO: 🔓 [SAFE] Lock Acquired
[2026-04-28 10:15:31] local.INFO: ✅ [SAFE] SUCCESS
```

---

## 🎯 النتائج المتوقعة

### اختبار المتطلب الأول

**السيناريو:** 50 طلب متزامن لإضافة نفس المنتج

**الكمية الأولية:** 100

**الطلبات:** 50 × 1 = 50 وحدة

**النتيجة المتوقعة:** 100 - 50 = 50

---

**❌ الطريقة الأساسية (بدون حماية):**
```
النتيجة الفعلية: 62 أو 71 أو 84 (عشوائية)
المشكلة: Race Condition!
المخاطر: 🔴 عالية جداً
```

---

**✅ الطريقة الآمنة (مع Locking):**
```
النتيجة الفعلية: 50 (دقيقة 100%)
الحماية: ✅ كاملة
المخاطر: 🟢 لا توجد
```

---

## 📈 قياس الأداء

### الأوقات المتوقعة

| الطريقة | الوقت | الأمان | الملاحظات |
|--------|-------|-------|---------|
| **Basic (No Lock)** | ~10-12ms | ❌ منخفض | سريع لكن غير آمن |
| **Safe (With Lock)** | ~13-16ms | ✅ عالي | آمن مع أداء جيد |
| **Overhead** | ~3-5ms | - | التكلفة الإضافية للأمان |

---

## 🧩 بنية المشروع

```
app/
├── Models/              # نماذج قاعدة البيانات
├── Http/
│   ├── Controllers/
│   │   └── CartItemController.php    # ✅ المتطلب #1
│   └── Requests/
├── Helpers/
│   └── ResponseHelper.php
└── services/

routes/
├── api.php             # ✅ Routes المحدثة

database/
├── migrations/         # Migrations قاعدة البيانات
└── seeders/           # ✅ Seeders محسّنة
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    ├── CategorySeeder.php
    ├── StoreSeeder.php
    ├── ProductSeeder.php
    └── StoreProductSeeder.php

Documentation/
├── REQUIREMENT_1_DOCUMENTATION.md  # شرح المتطلب الأول
├── TESTING_REQUIREMENTS.md         # شرح كل المتطلبات
└── test-requirement-1.sh           # اختبار Linux
   test-requirement-1.ps1           # اختبار Windows
```

---

## 🐛 استكشاف الأخطاء

### مشكلة: "SQLSTATE[HY000]: General error"
**السبب:** قاعدة البيانات غير متصلة
**الحل:**
```bash
# تحقق من إعدادات .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=

# أعد تشغيل الـ migration
php artisan migrate:fresh --seed
```

### مشكلة: "Class not found"
**السبب:** لم يتم تشغيل composer
**الحل:**
```bash
composer dump-autoload
```

### مشكلة: "401 Unauthorized"
**السبب:** JWT token غير صحيح
**الحل:**
```bash
# احصل على token جديد
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"mobile": "0100123456", "password": "password"}'
```

---

## 📚 المراجع والموارد

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Database Transactions](https://laravel.com/docs/database#transactions)
- [JWT Authentication](https://github.com/tymondesigns/jwt-auth)

---

## ✅ Checklist

- [ ] تثبيت المتطلبات
- [ ] إعداد البيئة (.env)
- [ ] تشغيل الـ migrations
- [ ] تشغيل الـ seeders
- [ ] التحقق من الـ API routes
- [ ] تشغيل اختبار المتطلب #1
- [ ] التحقق من الـ logs
- [ ] فحص نتائج الأداء

---

## 📞 الدعم والمساعدة

للأسئلة أو المشاكل:
1. تحقق من `TESTING_REQUIREMENTS.md`
2. اطلع على الـ logs في `storage/logs/laravel.log`
3. تأكد من تشغيل `php artisan migrate:fresh --seed` أولاً

---

**آخر تحديث:** 28 أبريل 2026

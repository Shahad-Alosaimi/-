-- ============================================
-- مشروع: موقع المفقودات والموجودات
-- الملف: schema.sql
-- المسؤولة: العضوة الأولى
-- ============================================

CREATE DATABASE IF NOT EXISTS lost_found_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lost_found_db;

-- ============================================
-- جدول 1: التصنيفات (Categories)
-- ============================================
CREATE TABLE categories (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name_ar   VARCHAR(100) NOT NULL COMMENT 'اسم التصنيف بالعربي',
    name_en   VARCHAR(100) NOT NULL COMMENT 'اسم التصنيف بالإنجليزي',
    icon      VARCHAR(50)  DEFAULT 'fa-box' COMMENT 'أيقونة FontAwesome'
);

-- بيانات التصنيفات الافتراضية
INSERT INTO categories (name_ar, name_en, icon) VALUES
('إلكترونيات',  'Electronics',  'fa-mobile-alt'),
('وثائق ومستندات', 'Documents', 'fa-id-card'),
('محافظ وحقائب',  'Wallets & Bags', 'fa-wallet'),
('مفاتيح',       'Keys',        'fa-key'),
('مجوهرات',      'Jewelry',     'fa-gem'),
('ملابس',        'Clothing',    'fa-tshirt'),
('حيوانات أليفة', 'Pets',       'fa-paw'),
('أخرى',         'Other',       'fa-box');

-- ============================================
-- جدول 2: المستخدمون (Users)
-- ============================================
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)        NOT NULL COMMENT 'الاسم الكامل',
    email         VARCHAR(150) UNIQUE NOT NULL COMMENT 'البريد الإلكتروني',
    phone         VARCHAR(20)         DEFAULT NULL COMMENT 'رقم الجوال (اختياري)',
    password_hash VARCHAR(255)        NOT NULL COMMENT 'كلمة المرور مشفرة',
    city          VARCHAR(100)        DEFAULT NULL COMMENT 'المدينة',
    profile_pic   VARCHAR(255)        DEFAULT 'default.png',
    is_active     TINYINT(1)          DEFAULT 1 COMMENT '1=نشط, 0=موقوف',
    created_at    DATETIME            DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- جدول 3: البلاغات (Items)
-- ============================================
CREATE TABLE items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NOT NULL COMMENT 'صاحب البلاغ',
    category_id   INT          NOT NULL COMMENT 'تصنيف الغرض',
    type          ENUM('lost', 'found') NOT NULL COMMENT 'مفقود أم موجود',
    title         VARCHAR(200) NOT NULL COMMENT 'عنوان البلاغ',
    description   TEXT         NOT NULL COMMENT 'وصف تفصيلي',
    city          VARCHAR(100) NOT NULL COMMENT 'المدينة',
    district      VARCHAR(100) DEFAULT NULL COMMENT 'الحي',
    location_desc VARCHAR(255) DEFAULT NULL COMMENT 'وصف الموقع نصياً',
    incident_date DATE         NOT NULL COMMENT 'تاريخ الفقدان أو الإيجاد',
    image1        VARCHAR(255) DEFAULT NULL COMMENT 'صورة رئيسية',
    image2        VARCHAR(255) DEFAULT NULL,
    image3        VARCHAR(255) DEFAULT NULL,
    status        ENUM('active', 'resolved', 'deleted') DEFAULT 'active',
    views         INT          DEFAULT 0 COMMENT 'عدد المشاهدات',
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- فهرسة لتسريع البحث
CREATE INDEX idx_items_type     ON items(type);
CREATE INDEX idx_items_status   ON items(status);
CREATE INDEX idx_items_city     ON items(city);
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_date     ON items(incident_date);

-- ============================================
-- جدول 4: الرسائل الداخلية (Messages)
-- ============================================
CREATE TABLE messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    item_id     INT  NOT NULL COMMENT 'البلاغ المرتبط بالرسالة',
    sender_id   INT  NOT NULL COMMENT 'المرسل',
    receiver_id INT  NOT NULL COMMENT 'المستقبل',
    body        TEXT NOT NULL COMMENT 'نص الرسالة',
    is_read     TINYINT(1) DEFAULT 0 COMMENT '0=غير مقروءة, 1=مقروءة',
    created_at  DATETIME   DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (item_id)     REFERENCES items(id)  ON DELETE CASCADE,
    FOREIGN KEY (sender_id)   REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id)  ON DELETE CASCADE
);

CREATE INDEX idx_messages_receiver ON messages(receiver_id, is_read);

-- ============================================
-- جدول 5: الإشعارات (Notifications)
-- ============================================
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL COMMENT 'المستخدم الذي يستقبل الإشعار',
    type       VARCHAR(50)  NOT NULL COMMENT 'نوع الإشعار: new_message, item_resolved',
    message    VARCHAR(255) NOT NULL COMMENT 'نص الإشعار',
    link       VARCHAR(255) DEFAULT NULL COMMENT 'رابط الصفحة المرتبطة',
    is_read    TINYINT(1)   DEFAULT 0,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- ملاحظات للعضوات الأخريات:
-- ============================================
-- عضوة 3 (تسجيل الدخول): استخدمي جدول users
--   - التسجيل: INSERT INTO users
--   - الدخول:  SELECT * FROM users WHERE email=? AND is_active=1
--
-- عضوة 4 (إضافة البلاغ): استخدمي جدول items + categories
--   - جلب التصنيفات: SELECT * FROM categories
--   - حفظ بلاغ:      INSERT INTO items
--
-- عضوة 5 و 6 (عرض البلاغات): استخدمي جدول items
--   - SELECT items.*, users.full_name, categories.name_ar
--     FROM items JOIN users ON ... JOIN categories ON ...
--     WHERE items.status='active'
--
-- عضوة 8 (الرسائل): استخدمي جدول messages + notifications
-- ============================================

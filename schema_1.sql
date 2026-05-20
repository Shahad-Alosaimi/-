-- ============================================
-- تطمن — جامعة الطائف
-- قاعدة البيانات
-- ============================================

CREATE DATABASE tatman_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tatman_db;

-- ============================================
-- جدول 1: التصنيفات
-- ============================================
CREATE TABLE categories (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name_ar  VARCHAR(100) NOT NULL,
    icon     VARCHAR(50)  DEFAULT 'fa-box'
);

INSERT INTO categories (name_ar, icon) VALUES
('إلكترونيات',       'fa-mobile-alt'),
('وثائق ومستندات',   'fa-id-card'),
('محافظ وحقائب',     'fa-wallet'),
('مفاتيح',           'fa-key'),
('مجوهرات وإكسسوار', 'fa-gem'),
('ملابس',            'fa-tshirt'),
('أخرى',             'fa-box');

-- ============================================
-- جدول 2: المستخدمون
-- ============================================
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)        NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL COMMENT 'البريد الجامعي',
    student_id    VARCHAR(20)         DEFAULT NULL COMMENT 'الرقم الجامعي',
    password_hash VARCHAR(255)        NOT NULL,
    profile_pic   VARCHAR(255)        DEFAULT 'default.png',
    is_active     TINYINT(1)          DEFAULT 1,
    created_at    DATETIME            DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- جدول 3: البلاغات
-- ============================================
CREATE TABLE items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NOT NULL,
    category_id   INT          NOT NULL,
    type          ENUM('lost','found') NOT NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT         NOT NULL,
    building      VARCHAR(100) DEFAULT NULL COMMENT 'المبنى داخل الجامعة',
    location_desc VARCHAR(255) DEFAULT NULL COMMENT 'وصف الموقع (قاعة، مدخل...)',
    incident_date DATE         NOT NULL,
    image1        VARCHAR(255) DEFAULT NULL,
    image2        VARCHAR(255) DEFAULT NULL,
    image3        VARCHAR(255) DEFAULT NULL,
    status        ENUM('active','resolved','deleted') DEFAULT 'active',
    views         INT          DEFAULT 0,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE INDEX idx_type     ON items(type);
CREATE INDEX idx_status   ON items(status);
CREATE INDEX idx_building ON items(building);
CREATE INDEX idx_category ON items(category_id);

-- ============================================
-- جدول 4: الرسائل
-- ============================================
CREATE TABLE messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    item_id     INT        NOT NULL,
    sender_id   INT        NOT NULL,
    receiver_id INT        NOT NULL,
    body        TEXT       NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME   DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (item_id)     REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_receiver ON messages(receiver_id, is_read);

-- ============================================
-- جدول 5: الإشعارات
-- ============================================
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    message    VARCHAR(255) NOT NULL,
    link       VARCHAR(255) DEFAULT NULL,
    is_read    TINYINT(1)   DEFAULT 0,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- ملاحظات للعضوات:
-- عضوة 3: جدول users — تسجيل بالبريد الجامعي + الرقم الجامعي
-- عضوة 4: جدول items + categories — building بدل city
-- عضوة 5 و6: SELECT من items مع building و location_desc
-- عضوة 8: جدول messages + notifications
-- ============================================

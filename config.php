<?php
// ============================================
// إعدادات الاتصال بقاعدة البيانات
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // غيري هذا لاسم مستخدم قاعدة بياناتك
define('DB_PASS', '');            // غيري هذا لكلمة مرور قاعدة بياناتك
define('DB_NAME', 'lost_found_db');
define('SITE_URL', 'http://localhost/lost_found');
define('SITE_NAME', 'مفقودات وموجودات الجامعة');
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// الاتصال
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $conn->connect_error);
}

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة مساعدة: تنظيف المدخلات
function clean($conn, $input) {
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

// دالة مساعدة: هل المستخدم مسجل دخول؟
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة مساعدة: إعادة التوجيه
function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}
?>

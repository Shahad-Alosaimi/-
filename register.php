<?php
require_once 'config.php';
$pageTitle = 'إنشاء حساب';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($conn, $_POST['full_name']);
    $email    = clean($conn, $_POST['email']);
    $phone    = clean($conn, $_POST['phone']);
    $city     = clean($conn, $_POST['city']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'يرجى تعبئة جميع الحقول المطلوبة';
    } elseif ($password !== $confirm) {
        $error = 'كلمة المرور وتأكيدها غير متطابقتين';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        // التحقق أن الإيميل غير موجود
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = 'هذا البريد الإلكتروني مسجل مسبقاً';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (full_name, email, phone, city, password_hash)
                          VALUES ('$name','$email','$phone','$city','$hash')");
            $_SESSION['user_id']   = $conn->insert_id;
            $_SESSION['user_name'] = $name;
            redirect('index.php');
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="form-title"><i class="fas fa-user-plus"></i> إنشاء حساب جديد</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>الاسم الكامل *</label>
                <input type="text" name="full_name" required placeholder="مثال: نورة أحمد">
            </div>
            <div class="form-group">
                <label>البريد الجامعي *</label>
                <input type="email" name="email" required placeholder="example@university.edu.sa">
            </div>
            <div class="form-group">
                <label>رقم الجوال</label>
                <input type="tel" name="phone" placeholder="05XXXXXXXX">
            </div>
            <div class="form-group">
                <label>المدينة</label>
                <input type="text" name="city" placeholder="مثال: الرياض">
            </div>
            <div class="form-group">
                <label>كلمة المرور *</label>
                <input type="password" name="password" required placeholder="6 أحرف على الأقل">
            </div>
            <div class="form-group">
                <label>تأكيد كلمة المرور *</label>
                <input type="password" name="confirm_password" required placeholder="أعيدي كتابة كلمة المرور">
            </div>
            <button type="submit" class="btn-submit">إنشاء الحساب</button>
        </form>
        <div class="form-link">عندك حساب بالفعل؟ <a href="login.php">سجلي دخول</a></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

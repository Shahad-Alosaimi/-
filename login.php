<?php
require_once 'config.php';
$pageTitle = 'تسجيل الدخول';
$error = '';

if (isLoggedIn()) redirect('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email' AND is_active=1");
    $user   = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        redirect('index.php');
    } else {
        $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="form-title"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required placeholder="example@university.edu.sa">
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required placeholder="كلمة المرور">
            </div>
            <button type="submit" class="btn-submit">دخول</button>
        </form>
        <div class="form-link">ما عندك حساب؟ <a href="register.php">سجلي الآن</a></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

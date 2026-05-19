<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$pageTitle = 'تعديل البلاغ';
$user_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
$error = '';

$item = $conn->query("SELECT * FROM items WHERE id=$id AND user_id=$user_id")->fetch_assoc();
if (!$item) redirect('dashboard.php');

$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = clean($conn, $_POST['title']);
    $description = clean($conn, $_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $city        = clean($conn, $_POST['city']);
    $district    = clean($conn, $_POST['district']);
    $inc_date    = clean($conn, $_POST['incident_date']);

    if (empty($title) || empty($description)) {
        $error = 'يرجى تعبئة الحقول المطلوبة';
    } else {
        $conn->query("UPDATE items SET title='$title', description='$description',
                      category_id=$category_id, city='$city', district='$district',
                      incident_date='$inc_date'
                      WHERE id=$id AND user_id=$user_id");
        redirect("item.php?id=$id");
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width:680px;">
        <div class="form-title"><i class="fas fa-edit"></i> تعديل البلاغ</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>عنوان البلاغ *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($item['title']) ?>">
            </div>
            <div class="form-group">
                <label>التصنيف</label>
                <select name="category_id">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $item['category_id']==$cat['id'] ? 'selected':'' ?>>
                            <?= $cat['name_ar'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>الوصف *</label>
                <textarea name="description" required><?= htmlspecialchars($item['description']) ?></textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-group">
                    <label>المدينة *</label>
                    <input type="text" name="city" required value="<?= htmlspecialchars($item['city']) ?>">
                </div>
                <div class="form-group">
                    <label>الحي / المبنى</label>
                    <input type="text" name="district" value="<?= htmlspecialchars($item['district'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>التاريخ</label>
                <input type="date" name="incident_date" value="<?= $item['incident_date'] ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn-submit">حفظ التعديلات</button>
            <div class="form-link"><a href="dashboard.php">رجوع للوحة التحكم</a></div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$pageTitle = 'إضافة بلاغ';
$error = $success = '';

$type = isset($_GET['type']) && $_GET['type'] === 'found' ? 'found' : 'lost';
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = clean($conn, $_POST['title']);
    $description   = clean($conn, $_POST['description']);
    $category_id   = (int)$_POST['category_id'];
    $type_post     = $_POST['type'] === 'found' ? 'found' : 'lost';
    $building      = clean($conn, $_POST['building']);
    $location_desc = clean($conn, $_POST['location_desc']);
    $inc_date      = clean($conn, $_POST['incident_date']);
    $user_id       = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($building)) {
        $error = 'يرجى تعبئة جميع الحقول المطلوبة';
    } else {
        // معالجة الصور
        $images = ['', '', ''];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        for ($i = 1; $i <= 3; $i++) {
            $key = "image$i";
            if (!empty($_FILES[$key]['name'])) {
                if (!in_array($_FILES[$key]['type'], $allowed)) {
                    $error = 'نوع الصورة غير مدعوم، يرجى رفع JPG أو PNG';
                    break;
                }
                $filename = uniqid() . '_' . basename($_FILES[$key]['name']);
                move_uploaded_file($_FILES[$key]['tmp_name'], UPLOAD_PATH . $filename);
                $images[$i-1] = $filename;
            }
        }

        if (!$error) {
            $conn->query("INSERT INTO items
                (user_id, category_id, type, title, description, building, location_desc, incident_date, image1, image2, image3)
                VALUES ($user_id, $category_id, '$type_post', '$title', '$description', '$building', '$location_desc', '$inc_date',
                '{$images[0]}', '{$images[1]}', '{$images[2]}')");
            $id = $conn->insert_id;
            redirect("item.php?id=$id");
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width:680px;">
        <div class="form-title">
            <?= $type === 'lost' ? '🔴 أبلغ عن مفقود' : '🟢 أبلغ عن موجود' ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type" value="<?= $type ?>">

            <div class="form-group">
                <label>نوع البلاغ</label>
                <select name="type" onchange="this.form.action='add_item.php?type='+this.value; this.form.submit();">
                    <option value="lost"  <?= $type==='lost'  ? 'selected' : '' ?>>🔴 مفقود</option>
                    <option value="found" <?= $type==='found' ? 'selected' : '' ?>>🟢 موجود</option>
                </select>
            </div>

            <div class="form-group">
                <label>عنوان البلاغ *</label>
                <input type="text" name="title" required placeholder="مثال: مفاتيح سيارة سوداء">
            </div>

            <div class="form-group">
                <label>التصنيف *</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name_ar'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>الوصف التفصيلي *</label>
                <textarea name="description" required placeholder="اوصف الغرض بالتفصيل: اللون، الشكل، أي علامات مميزة..."></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-group">
                    <label>المبنى *</label>
                    <input type="text" name="building" required placeholder="مثال: مبنى D">
                </div>
                <div class="form-group">
                    <label>وصف الموقع</label>
                    <input type="text" name="location_desc" placeholder="مثال: قرب المكتبة، قاعة 101">
                </div>
            </div>

            <div class="form-group">
                <label>تاريخ <?= $type==='lost' ? 'الفقدان' : 'الإيجاد' ?> *</label>
                <input type="date" name="incident_date" required max="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>الصورة الرئيسية</label>
                <input type="file" name="image1" accept="image/*">
            </div>
            <div class="form-group">
                <label>صورة إضافية (اختياري)</label>
                <input type="file" name="image2" accept="image/*">
            </div>
            <div class="form-group">
                <label>صورة إضافية (اختياري)</label>
                <input type="file" name="image3" accept="image/*">
            </div>

            <button type="submit" class="btn-submit">نشر البلاغ</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

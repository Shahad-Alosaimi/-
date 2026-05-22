<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);

$result = $conn->query("SELECT items.*, users.full_name, users.city as user_city,
                        categories.name_ar, categories.icon
                        FROM items
                        JOIN users ON items.user_id = users.id
                        JOIN categories ON items.category_id = categories.id
                        WHERE items.id=$id AND items.status != 'deleted'");

if ($result->num_rows === 0) { header('Location: index.php'); exit; }
$item = $result->fetch_assoc();
$pageTitle = $item['title'];

// زيادة المشاهدات
$conn->query("UPDATE items SET views = views+1 WHERE id=$id");

include 'includes/header.php';
?>

<div class="container">
    <div class="item-detail">

        <!-- نوع البلاغ وعنوانه -->
        <span class="card-type <?= $item['type']==='lost' ? 'type-lost':'type-found' ?>" style="font-size:15px; padding:6px 18px;">
            <?= $item['type']==='lost' ? '🔴 مفقود':'🟢 موجود' ?>
        </span>
        <h1 style="font-size:24px; margin:12px 0;"><?= htmlspecialchars($item['title']) ?></h1>

        <!-- الصور -->
        <?php if ($item['image1'] || $item['image2'] || $item['image3']): ?>
        <div class="item-images">
            <?php foreach (['image1','image2','image3'] as $img): ?>
                <?php if ($item[$img]): ?>
                    <img src="uploads/<?= htmlspecialchars($item[$img]) ?>" alt="صورة البلاغ">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- التفاصيل -->
        <div class="item-info">
            <table>
                <tr><td><i class="fas fa-align-right"></i> الوصف</td><td><?= nl2br(htmlspecialchars($item['description'])) ?></td></tr>
                <tr><td><i class="fas fa-tag"></i> التصنيف</td><td><?= htmlspecialchars($item['name_ar']) ?></td></tr>
                <tr><td><i class="fas fa-map-marker-alt"></i> المدينة</td><td><?= htmlspecialchars($item['city']) ?></td></tr>
                <?php if ($item['district']): ?>
                <tr><td><i class="fas fa-building"></i> الموقع</td><td><?= htmlspecialchars($item['district']) ?></td></tr>
                <?php endif; ?>
                <tr><td><i class="fas fa-calendar"></i> التاريخ</td><td><?= date('d/m/Y', strtotime($item['incident_date'])) ?></td></tr>
                <tr><td><i class="fas fa-user"></i> الناشرة</td><td><?= htmlspecialchars($item['full_name']) ?></td></tr>
                <tr><td><i class="fas fa-eye"></i> المشاهدات</td><td><?= $item['views'] ?></td></tr>
            </table>
        </div>

        <!-- زر التواصل -->
        <div style="margin-top:24px;">
            <?php if (!isLoggedIn()): ?>
                <a href="login.php" class="btn-submit" style="display:inline-block; padding:12px 30px; text-decoration:none; border-radius:8px;">
                    <i class="fas fa-sign-in-alt"></i> سجلي دخول للتواصل
                </a>
            <?php elseif ($_SESSION['user_id'] != $item['user_id']): ?>
                <a href="messages.php?item=<?= $item['id'] ?>&to=<?= $item['user_id'] ?>"
                   class="btn-submit" style="display:inline-block; padding:12px 30px; text-decoration:none; border-radius:8px;">
                    <i class="fas fa-envelope"></i> تواصلي مع الناشرة
                </a>
            <?php else: ?>
                <a href="dashboard.php" style="color:#1a237e;"><i class="fas fa-edit"></i> هذا بلاغك — إدارته من لوحة التحكم</a>
            <?php endif; ?>
        </div>

    </div>
</div>
<!-- البلاغات المشابهة -->

<hr style="margin:40px 0;">

<h2>بلاغات مشابهة</h2>

<div class="similar-items">

<?php

$category_id = $item['category_id'];

$similar = $conn->query("
SELECT *
FROM items
WHERE category_id = $category_id
AND id != $id
AND status != 'deleted'
LIMIT 4
");

while($row = $similar->fetch_assoc()):

?>

<div class="similar-card">

    <?php if($row['image1']): ?>

        <img
            src="uploads/<?= htmlspecialchars($row['image1']) ?>"
            width="200"
        >

    <?php endif; ?>

    <h3>
        <?= htmlspecialchars($row['title']) ?>
    </h3>

    <p>
        <?= mb_substr(htmlspecialchars($row['description']),0,70) ?>...
    </p>

    <a href="item.php?id=<?= $row['id'] ?>">
        عرض التفاصيل
    </a>

</div>

<?php endwhile; ?>

</div>
<?php include 'includes/footer.php'; ?>

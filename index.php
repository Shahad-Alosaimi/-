<?php
require_once 'config.php';
$pageTitle = 'الرئيسية';

// جلب آخر البلاغات
$sql = "SELECT items.*, users.full_name, categories.name_ar, categories.icon
        FROM items
        JOIN users ON items.user_id = users.id
        JOIN categories ON items.category_id = categories.id
        WHERE items.status = 'active'
        ORDER BY items.created_at DESC
        LIMIT 12";
$result = $conn->query($sql);
$items = $result->fetch_all(MYSQLI_ASSOC);

// إحصائيات
$total_lost  = $conn->query("SELECT COUNT(*) as c FROM items WHERE type='lost'  AND status='active'")->fetch_assoc()['c'];
$total_found = $conn->query("SELECT COUNT(*) as c FROM items WHERE type='found' AND status='active'")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<div class="container">

    <!-- Hero -->
    <div class="hero">
        <h1><i class="fas fa-university"></i> مفقودات وموجودات الجامعة</h1>
        <p>فقدتِ شيئاً داخل الحرم الجامعي؟ أو وجدتِ شيئاً؟ ساعدينا نوصله لصاحبه!</p>
        <div class="hero-buttons">
            <a href="add_item.php?type=lost"  class="btn-lost"><i class="fas fa-exclamation-circle"></i> أبلغ عن مفقود</a>
            <a href="add_item.php?type=found" class="btn-found"><i class="fas fa-check-circle"></i> أبلغ عن موجود</a>
        </div>
        <div style="margin-top:24px; display:flex; gap:40px; justify-content:center; flex-wrap:wrap;">
            <div><span style="font-size:28px; font-weight:bold;"><?= $total_lost ?></span><br><small>بلاغ مفقود</small></div>
            <div><span style="font-size:28px; font-weight:bold;"><?= $total_found ?></span><br><small>بلاغ موجود</small></div>
        </div>
    </div>

    <!-- بحث سريع -->
    <form class="search-bar" action="search.php" method="GET">
        <input type="text" name="q" placeholder="ابحثي عن شيء... (مثال: مفاتيح، محفظة)">
        <select name="type">
            <option value="">الكل</option>
            <option value="lost">مفقودات</option>
            <option value="found">موجودات</option>
        </select>
        <button type="submit"><i class="fas fa-search"></i> بحث</button>
    </form>

    <!-- آخر البلاغات -->
    <div class="section-title">آخر البلاغات</div>
    <div class="cards-grid">
        <?php foreach ($items as $item): ?>
        <a href="item.php?id=<?= $item['id'] ?>" class="card">
            <?php if ($item['image1']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image1']) ?>" alt="صورة البلاغ">
            <?php else: ?>
                <div class="card-no-img"><i class="fas <?= $item['icon'] ?>"></i></div>
            <?php endif; ?>
            <div class="card-body">
                <span class="card-type <?= $item['type'] === 'lost' ? 'type-lost' : 'type-found' ?>">
                    <?= $item['type'] === 'lost' ? '🔴 مفقود' : '🟢 موجود' ?>
                </span>
                <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                <div class="card-meta">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['city']) ?>
                    &nbsp;|&nbsp;
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($item['name_ar']) ?>
                    <br>
                    <i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
        <div style="text-align:center; padding:60px; color:#aaa;">
            <i class="fas fa-box-open" style="font-size:50px; margin-bottom:16px; display:block;"></i>
            لا توجد بلاغات حتى الآن — كوني أول من يضيف!
        </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top:30px;">
        <a href="search.php" style="color:#1a237e; font-size:15px;">عرض جميع البلاغات <i class="fas fa-arrow-left"></i></a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

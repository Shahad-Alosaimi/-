<?php
require_once 'config.php';
$pageTitle = 'البحث';

$q          = isset($_GET['q'])          ? clean($conn, $_GET['q'])   : '';
$type       = isset($_GET['type'])       ? clean($conn, $_GET['type']) : '';
$category   = isset($_GET['category'])   ? (int)$_GET['category']     : 0;
$city       = isset($_GET['city'])       ? clean($conn, $_GET['city']) : '';

$where = ["items.status='active'"];
if ($q)        $where[] = "(items.title LIKE '%$q%' OR items.description LIKE '%$q%')";
if ($type === 'lost' || $type === 'found') $where[] = "items.type='$type'";
if ($category) $where[] = "items.category_id=$category";
if ($city)     $where[] = "items.city LIKE '%$city%'";

$sql = "SELECT items.*, users.full_name, categories.name_ar, categories.icon
        FROM items
        JOIN users ON items.user_id = users.id
        JOIN categories ON items.category_id = categories.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY items.created_at DESC";

$result = $conn->query($sql);
$items  = $result->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title">البحث في البلاغات</div>

    <form class="search-bar" method="GET" style="flex-wrap:wrap; gap:10px;">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="ابحثي...">
        <select name="type">
            <option value="">الكل</option>
            <option value="lost"  <?= $type==='lost'  ? 'selected':'' ?>>🔴 مفقودات</option>
            <option value="found" <?= $type==='found' ? 'selected':'' ?>>🟢 موجودات</option>
        </select>
        <select name="category">
            <option value="">كل التصنيفات</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category==$cat['id'] ? 'selected':'' ?>><?= $cat['name_ar'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" placeholder="المدينة">
        <button type="submit"><i class="fas fa-search"></i> بحث</button>
    </form>

    <div style="margin-bottom:16px; color:#777; font-size:14px;">
        وجدنا <strong><?= count($items) ?></strong> نتيجة
    </div>

    <div class="cards-grid">
        <?php foreach ($items as $item): ?>
        <a href="item.php?id=<?= $item['id'] ?>" class="card">
            <?php if ($item['image1']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image1']) ?>" alt="">
            <?php else: ?>
                <div class="card-no-img"><i class="fas <?= $item['icon'] ?>"></i></div>
            <?php endif; ?>
            <div class="card-body">
                <span class="card-type <?= $item['type']==='lost' ? 'type-lost':'type-found' ?>">
                    <?= $item['type']==='lost' ? '🔴 مفقود':'🟢 موجود' ?>
                </span>
                <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                <div class="card-meta">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($item['city']) ?>
                    &nbsp;|&nbsp; <?= htmlspecialchars($item['name_ar']) ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
        <div style="text-align:center; padding:60px; color:#aaa;">
            <i class="fas fa-search" style="font-size:50px; margin-bottom:16px; display:block;"></i>
            لا توجد نتائج — جربي كلمات بحث مختلفة
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

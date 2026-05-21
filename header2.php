<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' : '' ?>تطمن | مفقودات وموجودات الجامعة</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a href="<?= SITE_URL ?>" class="logo">
            <i class="fas fa-search-location"></i>
            تطمن
        </a>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="<?= SITE_URL ?>/search.php"><i class="fas fa-search"></i> بحث</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/add_item.php" class="btn-add"><i class="fas fa-plus"></i> أضف بلاغ</a>
                <a href="<?= SITE_URL ?>/dashboard.php"><i class="fas fa-user"></i> حسابي</a>
                <a href="<?= SITE_URL ?>/messages.php"><i class="fas fa-envelope"></i> رسائلي</a>
                <a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> دخول</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn-add"><i class="fas fa-user-plus"></i> تسجيل</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="main-content">

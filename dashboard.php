<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$pageTitle = 'لوحة التحكم';
$user_id = $_SESSION['user_id'];

// حذف بلاغ
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("UPDATE items SET status='deleted' WHERE id=$del_id AND user_id=$user_id");
    redirect('dashboard.php');
}

// تغيير الحالة إلى مكتمل
if (isset($_GET['resolve'])) {
    $res_id = (int)$_GET['resolve'];
    $conn->query("UPDATE items SET status='resolved' WHERE id=$res_id AND user_id=$user_id");
    redirect('dashboard.php');
}

$user   = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$items  = $conn->query("SELECT items.*, categories.name_ar FROM items
                         JOIN categories ON items.category_id=categories.id
                         WHERE items.user_id=$user_id AND items.status!='deleted'
                         ORDER BY items.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$msgs   = $conn->query("SELECT COUNT(*) as c FROM messages WHERE receiver_id=$user_id AND is_read=0")->fetch_assoc()['c'];

$total_lost     = array_filter($items, fn($i) => $i['type']==='lost'     && $i['status']==='active');
$total_found    = array_filter($items, fn($i) => $i['type']==='found'    && $i['status']==='active');
$total_resolved = array_filter($items, fn($i) => $i['status']==='resolved');

include 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-grid">

        <!-- Sidebar -->
        <div class="sidebar">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:70px; height:70px; background:#e8eaf6; border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:32px; color:#1a237e;">
                    <i class="fas fa-user"></i>
                </div>
                <div style="font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></div>
                <div style="font-size:13px; color:#777;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
            <a href="add_item.php?type=lost"><i class="fas fa-exclamation-circle"></i> أضف مفقود</a>
            <a href="add_item.php?type=found"><i class="fas fa-check-circle"></i> أضف موجود</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> رسائلي
                <?php if ($msgs > 0): ?><span style="background:#e53935;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-right:4px;"><?= $msgs ?></span><?php endif; ?>
            </a>
            <a href="logout.php" style="color:#e53935;"><i class="fas fa-sign-out-alt"></i> خروج</a>
        </div>

        <!-- Main -->
        <div>
            <!-- إحصائيات -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="num" style="color:#e53935;"><?= count($total_lost) ?></div>
                    <div class="lbl">مفقودات نشطة</div>
                </div>
                <div class="stat-card">
                    <div class="num" style="color:#43a047;"><?= count($total_found) ?></div>
                    <div class="lbl">موجودات نشطة</div>
                </div>
                <div class="stat-card">
                    <div class="num"><?= count($total_resolved) ?></div>
                    <div class="lbl">تم حلها</div>
                </div>
            </div>

            <!-- قائمة البلاغات -->
            <div class="section-title">بلاغاتي</div>

            <?php if (empty($items)): ?>
                <div style="text-align:center; padding:40px; color:#aaa;">
                    ما أضفتِ أي بلاغ بعد —
                    <a href="add_item.php">أضيفي الآن</a>
                </div>
            <?php else: ?>
            <div style="background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.07);">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8f9ff;">
                        <tr>
                            <th style="padding:12px 16px; text-align:right; font-size:13px; color:#555;">العنوان</th>
                            <th style="padding:12px 16px; text-align:center; font-size:13px; color:#555;">النوع</th>
                            <th style="padding:12px 16px; text-align:center; font-size:13px; color:#555;">الحالة</th>
                            <th style="padding:12px 16px; text-align:center; font-size:13px; color:#555;">التاريخ</th>
                            <th style="padding:12px 16px; text-align:center; font-size:13px; color:#555;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr style="border-top:1px solid #f0f0f0;">
                        <td style="padding:12px 16px;"><a href="item.php?id=<?= $item['id'] ?>" style="color:#1a237e;"><?= htmlspecialchars($item['title']) ?></a></td>
                        <td style="padding:12px 16px; text-align:center;">
                            <span class="card-type <?= $item['type']==='lost' ? 'type-lost':'type-found' ?>">
                                <?= $item['type']==='lost' ? 'مفقود':'موجود' ?>
                            </span>
                        </td>
                        <td style="padding:12px 16px; text-align:center; font-size:13px;">
                            <?php
                            $status_labels = ['active'=>'🟡 نشط','resolved'=>'✅ مكتمل'];
                            echo $status_labels[$item['status']] ?? $item['status'];
                            ?>
                        </td>
                        <td style="padding:12px 16px; text-align:center; font-size:13px; color:#777;"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                        <td style="padding:12px 16px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                                <a href="edit_item.php?id=<?= $item['id'] ?>" style="color:#1a237e; font-size:13px;"><i class="fas fa-edit"></i> تعديل</a>
                                <?php if ($item['status']==='active'): ?>
                                <a href="dashboard.php?resolve=<?= $item['id'] ?>" style="color:#43a047; font-size:13px;" onclick="return confirm('تأكيد: تم العثور عليه؟')"><i class="fas fa-check"></i> تم</a>
                                <?php endif; ?>
                                <a href="dashboard.php?delete=<?= $item['id'] ?>" style="color:#e53935; font-size:13px;" onclick="return confirm('هل تريدين حذف هذا البلاغ؟')"><i class="fas fa-trash"></i> حذف</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

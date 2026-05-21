<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');

$pageTitle = 'الحساب الشخصي';
$current_student_id = $_SESSION['user_id'];

// 1. معالجة طلب حذف الإعلان
if (isset($_GET['delete'])) {
    $target_delete_id = (int)$_GET['delete'];
    $conn->query("UPDATE items SET status='deleted' WHERE id=$target_delete_id AND user_id=$current_student_id");
    redirect('dashboard.php');
}

// 2. معالجة طلب إغلاق البلاغ (تم العثور عليه)
if (isset($_GET['resolve'])) {
    $target_resolve_id = (int)$_GET['resolve'];
    $conn->query("UPDATE items SET status='resolved' WHERE id=$target_resolve_id AND user_id=$current_student_id");
    redirect('dashboard.php');
}

// 3. جلب بيانات الطالب وبلاغاته والرسائل
$student_data = $conn->query("SELECT * FROM users WHERE id=$current_student_id")->fetch_assoc();

$my_reports = $conn->query("SELECT items.*, categories.name_ar FROM items
                           JOIN categories ON items.category_id=categories.id
                           WHERE items.user_id=$current_student_id AND items.status!='deleted'
                           ORDER BY items.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$unread_chats_count = $conn->query("SELECT COUNT(*) as total_msg FROM messages WHERE receiver_id=$current_student_id AND is_read=0")->fetch_assoc()['total_msg'];


// 4. حساب الإحصائيات بالطريقة التقليدية (Foreach Loop) لتبدو كتابة طالب
$active_lost_counter = 0;
$active_found_counter = 0;
$resolved_counter = 0;

foreach ($my_reports as $single_item) {
    if ($single_item['type'] === 'lost' && $single_item['status'] === 'active') {
        $active_lost_counter++;
    }
    if ($single_item['type'] === 'found' && $single_item['status'] === 'active') {
        $active_found_counter++;
    }
    if ($single_item['status'] === 'resolved') {
        $resolved_counter++;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-grid">

        <div class="sidebar" style="background: #fafafa; border-left: 1px solid #e0e0e0;">
            <div style="text-align:center; margin-bottom:25px;">
                <div style="width:75px; height:75px; background:#d1c4e9; border-radius:12px; margin:0 auto 12px; display:flex; align-items:center; justify-content:center; font-size:30px; color:#4527a0;">
                    <i class="fas fa-user-gradient"></i>
                </div>
                <div style="font-weight:700; color: #212121;"><?= htmlspecialchars($student_data['full_name']) ?></div>
                <div style="font-size:12px; color:#616161; margin-top:4px;"><?= htmlspecialchars($student_data['email']) ?></div>
            </div>
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="add_item.php?type=lost"><i class="fas fa-search"></i> تسجيل غرض مفقود</a>
            <a href="add_item.php?type=found"><i class="fas fa-hands-helping"></i> تسجيل غرض معثور عليه</a>
            <a href="messages.php"><i class="fas fa-comments"></i> صندوق الرسائل
                <?php if ($unread_chats_count > 0): ?><span style="background:#ff5722;color:#fff;border-radius:4px;padding:2px 6px;font-size:11px;margin-right:6px;"><?= $unread_chats_count ?></span><?php endif; ?>
            </a>
            <a href="logout.php" style="color:#d32f2f; border-top: 1px dashed #e0e0e0; margin-top: 15px; padding-top: 15px;"><i class="fas fa-power-off"></i> تسجيل الخروج</a>
        </div>

        <div>
            <div class="stat-cards">
                <div class="stat-card" style="border-bottom: 4px solid #e53935; border-radius: 6px;">
                    <div class="num" style="color:#e53935; font-weight: bold;"><?= $active_lost_counter ?></div>
                    <div class="lbl" style="font-size: 13px; color: #555;">مفقودات معلنة حالياً</div>
                </div>
                <div class="stat-card" style="border-bottom: 4px solid #2e7d32; border-radius: 6px;">
                    <div class="num" style="color:#2e7d32; font-weight: bold;"><?= $active_found_counter ?></div>
                    <div class="lbl" style="font-size: 13px; color: #555;">موجودات معلنة حالياً</div>
                </div>
                <div class="stat-card" style="border-bottom: 4px solid #0277bd; border-radius: 6px;">
                    <div class="num" style="color:#0277bd; font-weight: bold;"><?= $resolved_counter ?></div>
                    <div class="lbl" style="font-size: 13px; color: #555;">إعلانات مقفلة ومستردة</div>
                </div>
            </div>

            <div class="section-title" style="font-weight: bold; color: #333; margin-top: 25px;">قائمة أغراضي المعلنة</div>

            <?php if (empty($my_reports)): ?>
                <div style="text-align:center; padding:50px; color:#999; background: #fff; border-radius: 8px;">
                    لا توجد أي بلاغات مسجلة باسمك حالياً — 
                    <a href="add_item.php" style="color: #4527a0; font-weight: bold;">أضف أول بلاغ الآن</a>
                </div>
            <?php else: ?>
            <div style="background:#fff; border-radius:6px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.05); margin-top: 15px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f5f5f5; border-bottom: 2px solid #e0e0e0;">
                        <tr>
                            <th style="padding:14px 16px; text-align:right; font-size:13px; color:#424242;">عنوان الإعلان</th>
                            <th style="padding:14px 16px; text-align:center; font-size:13px; color:#424242;">التصنيف والموقع</th>
                            <th style="padding:14px 16px; text-align:center; font-size:13px; color:#424242;">الحالة الحالية</th>
                            <th style="padding:14px 16px; text-align:center; font-size:13px; color:#424242;">تاريخ النشر</th>
                            <th style="padding:14px 16px; text-align:center; font-size:13px; color:#424242;">خيارات التحكم</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($my_reports as $report): ?>
                    <tr style="border-top:1px solid #f0f0f0;">
                        <td style="padding:14px 16px;"><a href="item.php?id=<?= $report['id'] ?>" style="color:#0277bd; font-weight: 600; text-decoration: none;"><?= htmlspecialchars($report['title']) ?></a></td>
                        <td style="padding:14px 16px; text-align:center;">
                            <span class="card-type <?= $report['type']==='lost' ? 'type-lost':'type-found' ?>" style="border-radius: 4px; padding: 3px 8px; font-size: 12px;">
                                <?= $report['type']==='lost' ? 'مفقود':'موجود' ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px; text-align:center; font-size:13px; font-weight: 600;">
                            <?php if ($report['status'] === 'active'): ?>
                                <span style="color: #f57c00;">تحت المتابعة</span>
                            <?php else: ?>
                                <span style="color: #2e7d32;">تم الإغلاق والاستلام</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px 16px; text-align:center; font-size:12px; color:#757575;"><?= date('d-m-Y', strtotime($report['created_at'])) ?></td>
                        <td style="padding:14px 16px; text-align:center;">
                            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                                <a href="edit_item.php?id=<?= $report['id'] ?>" style="color:#0277bd; font-size:13px; text-decoration: none;"><i class="fas fa-edit"></i> تعديل</a>
                                
                                <?php if ($report['status']==='active'): ?>
                                <a href="dashboard.php?resolve=<?= $report['id'] ?>" style="color:#2e7d32; font-size:13px; text-decoration: none;" onclick="return confirm('تأكيد: هل تم حل هذا البلاغ واستلام الغرض؟')"><i class="fas fa-check-circle"></i> تم الاستلام</a>
                                <?php endif; ?>
                                
                                <a href="dashboard.php?delete=<?= $report['id'] ?>" style="color:#c62828; font-size:13px; text-decoration: none;" onclick="return confirm('تنبيه: هل أنت متأكد من رغبتك في حذف هذا الإعلان؟')"><i class="fas fa-trash-alt"></i> إزالة</a>
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

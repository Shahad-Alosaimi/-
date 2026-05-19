<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');
$pageTitle = 'الرسائل';
$user_id = $_SESSION['user_id'];

// إرسال رسالة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = (int)$_POST['receiver_id'];
    $item_id     = (int)$_POST['item_id'];
    $body        = clean($conn, $_POST['body']);
    if ($body && $receiver_id && $item_id) {
        $conn->query("INSERT INTO messages (item_id, sender_id, receiver_id, body)
                      VALUES ($item_id, $user_id, $receiver_id, '$body')");
        // إشعار
        $conn->query("INSERT INTO notifications (user_id, type, message, link)
                      VALUES ($receiver_id, 'new_message', 'لديك رسالة جديدة', 'messages.php?item=$item_id&to=$user_id')");
    }
    redirect("messages.php?item=$item_id&to=$receiver_id");
}

$item_id     = (int)($_GET['item'] ?? 0);
$other_id    = (int)($_GET['to']   ?? 0);

// تحديد محادثات المستخدم
$convos = $conn->query("
    SELECT DISTINCT
        IF(sender_id=$user_id, receiver_id, sender_id) AS other_id,
        item_id,
        MAX(created_at) AS last_msg
    FROM messages
    WHERE sender_id=$user_id OR receiver_id=$user_id
    GROUP BY item_id, other_id
    ORDER BY last_msg DESC
")->fetch_all(MYSQLI_ASSOC);

// رسائل المحادثة المختارة
$chat_messages = [];
if ($item_id && $other_id) {
    $conn->query("UPDATE messages SET is_read=1 WHERE item_id=$item_id AND receiver_id=$user_id");
    $chat_messages = $conn->query("
        SELECT messages.*, users.full_name
        FROM messages
        JOIN users ON messages.sender_id = users.id
        WHERE messages.item_id=$item_id
          AND ((sender_id=$user_id AND receiver_id=$other_id) OR (sender_id=$other_id AND receiver_id=$user_id))
        ORDER BY created_at ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $item_info  = $conn->query("SELECT title FROM items WHERE id=$item_id")->fetch_assoc();
    $other_user = $conn->query("SELECT full_name FROM users WHERE id=$other_id")->fetch_assoc();
}

include 'includes/header.php';
?>

<div class="container">
    <div class="section-title">الرسائل</div>

    <div style="display:grid; grid-template-columns:300px 1fr; gap:20px;">

        <!-- قائمة المحادثات -->
        <div class="msg-list">
            <?php if (empty($convos)): ?>
                <div style="padding:30px; text-align:center; color:#aaa; font-size:14px;">لا توجد رسائل</div>
            <?php endif; ?>
            <?php foreach ($convos as $c):
                $other = $conn->query("SELECT full_name FROM users WHERE id={$c['other_id']}")->fetch_assoc();
                $unread = $conn->query("SELECT COUNT(*) as n FROM messages WHERE item_id={$c['item_id']} AND sender_id={$c['other_id']} AND receiver_id=$user_id AND is_read=0")->fetch_assoc()['n'];
            ?>
            <a href="messages.php?item=<?= $c['item_id'] ?>&to=<?= $c['other_id'] ?>"
               class="msg-item <?= $unread>0 ? 'unread':'' ?>">
                <div class="msg-avatar"><i class="fas fa-user"></i></div>
                <div class="msg-content">
                    <div class="msg-name"><?= htmlspecialchars($other['full_name'] ?? 'مجهول') ?></div>
                    <div class="msg-preview">بلاغ #<?= $c['item_id'] ?></div>
                </div>
                <?php if ($unread>0): ?>
                    <span style="background:#e53935; color:#fff; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:11px;"><?= $unread ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- نافذة المحادثة -->
        <div>
        <?php if ($item_id && $other_id && isset($item_info)): ?>
            <div class="chat-box">
                <div style="margin-bottom:14px; font-size:15px; font-weight:600; color:#1a237e; border-bottom:1px solid #f0f0f0; padding-bottom:10px;">
                    <i class="fas fa-comments"></i>
                    محادثة مع <?= htmlspecialchars($other_user['full_name']) ?>
                    <span style="font-size:13px; color:#aaa; font-weight:400;"> — <?= htmlspecialchars($item_info['title']) ?></span>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <?php foreach ($chat_messages as $msg): ?>
                    <div class="bubble <?= $msg['sender_id']==$user_id ? 'me':'other' ?>">
                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                        <div style="font-size:10px; opacity:0.6; margin-top:4px;"><?= date('h:i A d/m', strtotime($msg['created_at'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="chat-input">
                    <input type="hidden" name="receiver_id" value="<?= $other_id ?>">
                    <input type="hidden" name="item_id" value="<?= $item_id ?>">
                    <input type="text" name="body" placeholder="اكتبي رسالتك..." required autocomplete="off">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:80px; color:#aaa;">
                <i class="fas fa-comments" style="font-size:50px; margin-bottom:16px; display:block;"></i>
                اختاري محادثة للعرض
            </div>
        <?php endif; ?>
        </div>

    </div>
</div>

<script>
// تمرير لأسفل المحادثة تلقائياً
const c = document.getElementById('chat-messages');
if (c) c.scrollTop = c.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>

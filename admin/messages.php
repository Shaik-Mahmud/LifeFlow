<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: messages.php");
    exit;
}

if (isset($_GET['clear_all'])) {
    $pdo->query("DELETE FROM messages");
    header("Location: messages.php");
    exit;
}

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM messages";
$params = [];
if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$activeRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status != 'Completed'")->fetchColumn();
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LifeFlow admin contact messages.">
    <title>Messages | LifeFlow Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-home.css" rel="stylesheet">
</head>

<body class="admin-home-page">
    <div class="admin-shell"><aside class="admin-sidebar" id="adminSidebar"><a class="admin-sidebar-brand" href="index.php"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span></a><p class="admin-sidebar-label">Administration</p><nav class="admin-menu" aria-label="Admin navigation"><a href="index.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a><a href="manage-requests.php"><i class="fa-solid fa-heart-pulse"></i> Blood requests <span><?= $activeRequests ?></span></a><a href="manage-donors.php"><i class="fa-solid fa-users"></i> Donors</a><a class="active" href="messages.php"><i class="fa-solid fa-envelope"></i> Messages <span id="messageBadge"><?= $totalMessages ?></span></a><a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></nav><div class="admin-sidebar-bottom"><a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
        <div class="admin-main"><header class="admin-topbar"><button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="adminSidebar"><i class="fa-solid fa-bars"></i></button><div><p class="admin-eyebrow">Administrator portal</p><h1>Messages</h1></div><div class="admin-profile"><span class="admin-profile-avatar">AD</span><div><strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong><small>LifeFlow team</small></div></div></header>
            <main class="admin-content">
                <section class="admin-welcome">
                    <div>
                        <span class="section-kicker">Contact inbox</span>
                        <h2>Messages from visitors</h2>
                        <p>Messages submitted through the LifeFlow contact form.</p>
                    </div>
                    <?php if (count($messages) > 0 || !empty($search)): ?>
                        <a href="messages.php?clear_all=1" class="btn btn-outline-danger" onclick="return confirm('Clear all messages? This cannot be undone.');"><i class="fa-solid fa-trash-can me-1"></i> Clear messages</a>
                    <?php endif; ?>
                </section>
                
                <section class="admin-panel">
                    <div class="admin-panel-heading donor-management-heading">
                        <div>
                            <h2>Inbox</h2>
                            <p id="messageCount"><?= count($messages) ?> <?= count($messages) === 1 ? 'message' : 'messages' ?> <?= !empty($search) ? 'found' : 'received' ?></p>
                        </div>
                        <div class="admin-search">
                            <form method="GET" action="messages.php" style="margin:0;">
                                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--muted);"></i>
                                <input type="search" name="search" id="messageSearch" placeholder="Search messages" value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()" style="padding-left: 40px; border: 1px solid var(--line); border-radius: 20px; padding-top: 8px; padding-bottom: 8px;">
                            </form>
                        </div>
                    </div>
                    
                    <div class="message-list" id="messageList">
                        <?php foreach($messages as $msg): ?>
                            <article class="admin-message-card">
                                <div class="admin-message-avatar"><?= strtoupper(substr($msg['name'], 0, 2)) ?></div>
                                <div class="admin-message-content">
                                    <div class="admin-message-heading">
                                        <div>
                                            <h3><?= htmlspecialchars($msg['name']) ?></h3>
                                            <p><?= htmlspecialchars($msg['email']) ?></p>
                                        </div>
                                        <time><?= date('M j, Y h:i A', strtotime($msg['created_at'])) ?></time>
                                    </div>
                                    <span class="admin-message-topic"><?= htmlspecialchars($msg['subject']) ?></span>
                                    <p class="admin-message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                </div>
                                <a href="messages.php?delete_id=<?= $msg['id'] ?>" class="admin-message-delete" aria-label="Delete message" onclick="return confirm('Delete this message?');">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(empty($messages)): ?>
                        <div class="empty-admin-messages" id="emptyMessages">
                            <span><i class="fa-regular fa-envelope-open"></i></span>
                            <h3>No contact messages yet</h3>
                            <p>New messages from <a href="../contact.php">the contact form</a> will appear here.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.admin-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    </script>
</body>
</html>

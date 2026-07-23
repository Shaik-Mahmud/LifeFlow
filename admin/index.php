<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch stats
$activeRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status != 'Completed'")->fetchColumn();
$availableDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Available'")->fetchColumn();
$fulfilledRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Completed'")->fetchColumn();
$pendingReview = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'Unread'")->fetchColumn();

// Fetch recent requests
$recentRequestsStmt = $pdo->query("SELECT * FROM blood_requests ORDER BY created_at DESC LIMIT 5");
$recentRequests = $recentRequestsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent activity (combining donors and requests for simplicity)
$recentActivity = [];
$recentDonorsStmt = $pdo->query("SELECT full_name, created_at, 'donor' as type FROM donors ORDER BY created_at DESC LIMIT 3");
while($row = $recentDonorsStmt->fetch(PDO::FETCH_ASSOC)) {
    $recentActivity[] = $row;
}
$recentReqsStmt = $pdo->query("SELECT blood_group, city, urgency, created_at, 'request' as type FROM blood_requests ORDER BY created_at DESC LIMIT 3");
while($row = $recentReqsStmt->fetch(PDO::FETCH_ASSOC)) {
    $recentActivity[] = $row;
}
usort($recentActivity, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recentActivity = array_slice($recentActivity, 0, 5);

// Fetch donor distribution by blood group
$bgStmt = $pdo->query("SELECT blood_group, COUNT(*) as count FROM donors WHERE availability_status = 'Available' GROUP BY blood_group");
$bloodGroupsData = [];
while ($row = $bgStmt->fetch(PDO::FETCH_ASSOC)) {
    $bloodGroupsData[$row['blood_group']] = $row['count'];
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LifeFlow administrator dashboard.">
    <title>Admin Dashboard | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-home.css" rel="stylesheet">
</head>

<body class="admin-home-page">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <a class="admin-sidebar-brand" href="index.php"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span></a>
            <p class="admin-sidebar-label">Administration</p>
            <nav class="admin-menu" aria-label="Admin navigation"><a class="active" href="index.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a><a href="manage-requests.php"><i class="fa-solid fa-heart-pulse"></i> Blood requests <span><?= $activeRequests ?></span></a><a href="manage-donors.php"><i class="fa-solid fa-users"></i> Donors</a><a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a><a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></nav>
            <div class="admin-sidebar-bottom"><a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div>
        </aside>

        <div class="admin-main"><header class="admin-topbar"><button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="adminSidebar"><i class="fa-solid fa-bars"></i></button><div><p class="admin-eyebrow">Administrator portal</p><h1>Dashboard</h1></div><div class="admin-profile"><span class="admin-profile-avatar">AD</span><div><strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong><small>LifeFlow team</small></div></div></header>

            <main class="admin-content"><section class="admin-welcome"><div><span class="section-kicker">Overview</span><h2>Good morning, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>.</h2><p>Here’s what’s happening across LifeFlow today.</p></div><a href="../blood-request.php" target="_blank" class="btn btn-danger"><i class="fa-solid fa-plus me-1"></i> Create request</a></section>

                <section class="row g-4 admin-stats"><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-red"><i class="fa-solid fa-heart-pulse"></i></span><div><small>Active requests</small><strong><?= $activeRequests ?></strong></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-blue"><i class="fa-solid fa-users"></i></span><div><small>Available donors</small><strong><?= $availableDonors ?></strong></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-green"><i class="fa-solid fa-circle-check"></i></span><div><small>Requests fulfilled</small><strong><?= $fulfilledRequests ?></strong></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-gold"><i class="fa-solid fa-clock"></i></span><div><small>Unread Messages</small><strong><?= $pendingReview ?></strong></div></article></div></section>

                <section class="row g-4 mt-1"><div class="col-xl-8" id="requests"><article class="admin-panel"><div class="admin-panel-heading"><div><h2>Recent blood requests</h2><p>Latest requests awaiting coordination.</p></div><a href="manage-requests.php">View all <i class="fa-solid fa-arrow-right"></i></a></div><div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Patient / hospital</th><th>Blood type</th><th>Location</th><th>Urgency</th><th>Status</th></tr></thead><tbody>
                    <?php foreach($recentRequests as $req): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($req['hospital']) ?></strong><small>Requested <?= date('M j, Y H:i', strtotime($req['created_at'])) ?></small></td>
                            <td><span class="admin-blood-badge"><?= htmlspecialchars($req['blood_group']) ?></span></td>
                            <td><?= htmlspecialchars($req['city']) ?></td>
                            <td><span class="urgency <?= strtolower($req['urgency']) ?>"><?= htmlspecialchars($req['urgency']) ?></span></td>
                            <td><span class="request-status <?= strtolower($req['status']) ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentRequests)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No recent requests found.</td></tr>
                    <?php endif; ?>
                </tbody></table></div></article></div>
                    <div class="col-xl-4" id="activity"><article class="admin-panel activity-panel"><div class="admin-panel-heading"><div><h2>Recent activity</h2><p>Updates from the platform.</p></div></div><div class="activity-list">
                        <?php foreach($recentActivity as $act): ?>
                            <div>
                                <?php if($act['type'] === 'donor'): ?>
                                    <span class="activity-icon icon-blue"><i class="fa-solid fa-user-plus"></i></span>
                                    <p><strong>New donor registered</strong><small><?= htmlspecialchars($act['full_name']) ?> joined LifeFlow</small><time><?= date('M j, H:i', strtotime($act['created_at'])) ?></time></p>
                                <?php else: ?>
                                    <span class="activity-icon icon-red"><i class="fa-solid fa-heart-pulse"></i></span>
                                    <p><strong>New <?= htmlspecialchars(strtolower($act['urgency'])) ?> request</strong><small><?= htmlspecialchars($act['blood_group']) ?> blood needed in <?= htmlspecialchars($act['city']) ?></small><time><?= date('M j, H:i', strtotime($act['created_at'])) ?></time></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentActivity)): ?>
                            <p class="text-muted text-center mt-3">No recent activity.</p>
                        <?php endif; ?>
                    </div></article></div></section>

                <section class="admin-panel mt-4" id="donors"><div class="admin-panel-heading"><div><h2>Donor availability</h2><p>Current available donor distribution by blood group.</p></div><a href="manage-donors.php">Manage donors <i class="fa-solid fa-arrow-right"></i></a></div><div class="blood-distribution">
                    <?php foreach($bloodGroupsData as $bg => $count): ?>
                        <div><span class="admin-blood-badge"><?= htmlspecialchars($bg) ?></span><strong><?= $count ?></strong><small>available donors</small></div>
                    <?php endforeach; ?>
                    <?php if (empty($bloodGroupsData)): ?>
                        <p class="text-muted w-100 text-center">No available donors found.</p>
                    <?php endif; ?>
                </div></section>
            </main>
        </div>
    </div>
    <script>
        document.querySelector('.admin-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    </script>
</body>
</html>

<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        if ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM blood_requests WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
    }
    exit;
}

$stmt = $pdo->query("SELECT * FROM blood_requests ORDER BY created_at DESC");
$requests = $stmt->fetchAll();

// Fetch stats
$activeRequestsCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status != 'Completed'")->fetchColumn();
$awaitingMatch = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Pending'")->fetchColumn();
$fulfilledToday = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Completed' AND DATE(created_at) = CURDATE()")->fetchColumn();
$donorsNotified = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Available'")->fetchColumn();


// Function to calculate time ago
function time_ago($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);           
    $hours           = round($seconds / 3600);           
    $days          = round($seconds / 86400);           
    
    if($seconds <= 60) {
        return "Just now";
    } else if($minutes <= 60) {
        return "$minutes min ago";
    } else if($hours <= 24) {
        return "$hours hrs ago";
    } else {
        return "$days days ago";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage LifeFlow blood requests.">
    <title>Manage Blood Requests | LifeFlow Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-home.css" rel="stylesheet">
</head>

<body class="admin-home-page">
    <div class="admin-shell"><aside class="admin-sidebar" id="adminSidebar"><a class="admin-sidebar-brand" href="index.php"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span></a><p class="admin-sidebar-label">Administration</p><nav class="admin-menu" aria-label="Admin navigation"><a href="index.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a><a class="active" href="manage-requests.php"><i class="fa-solid fa-heart-pulse"></i> Blood requests <span><?= $activeRequestsCount ?></span></a><a href="manage-donors.php"><i class="fa-solid fa-users"></i> Donors</a><a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a><a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></nav><div class="admin-sidebar-bottom"><a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
        <div class="admin-main"><header class="admin-topbar"><button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="adminSidebar"><i class="fa-solid fa-bars"></i></button><div><p class="admin-eyebrow">Administrator portal</p><h1>Blood requests</h1></div><div class="admin-profile"><span class="admin-profile-avatar">AD</span><div><strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong><small>LifeFlow team</small></div></div></header>
            <main class="admin-content"><section class="admin-welcome"><div><span class="section-kicker">Request coordination</span><h2>Manage blood requests</h2><p>Review request details, coordinate donor matches and update fulfilment status.</p></div><a href="../blood-request.php" class="btn btn-danger"><i class="fa-solid fa-plus me-1"></i> Create request</a></section>
                <section class="row g-4 admin-stats"><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-red"><i class="fa-solid fa-heart-pulse"></i></span><div><small>Active requests</small><strong><?= $activeRequestsCount ?></strong><p>Pending completion</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-gold"><i class="fa-solid fa-clock"></i></span><div><small>Awaiting match</small><strong><?= $awaitingMatch ?></strong><p>Needs attention</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-blue"><i class="fa-solid fa-bullhorn"></i></span><div><small>Available donors</small><strong><?= $donorsNotified ?></strong><p>Can be notified</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-green"><i class="fa-solid fa-circle-check"></i></span><div><small>Fulfilled today</small><strong><?= $fulfilledToday ?></strong><p>Successful requests</p></div></article></div></section>
                <section class="admin-panel mt-4"><div class="admin-panel-heading donor-management-heading"><div><h2>All blood requests</h2><p id="requestCount">Showing <?= count($requests) ?> blood requests</p></div><div class="admin-donor-filters"><div class="admin-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="requestSearch" placeholder="Search hospital or location"></div><select id="requestFilter" aria-label="Filter by request status"><option value="">All statuses</option><option value="matching">Matching donors</option><option value="open">Open</option><option value="fulfilled">Fulfilled</option></select></div></div><div class="table-responsive"><table class="table admin-table admin-request-table align-middle"><thead><tr><th>Request</th><th>Blood / units</th><th>Location</th><th>Urgency</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody id="requestTable">
                <?php foreach ($requests as $req): 
                    $statusClass = 'open';
                    if (strtolower($req['status']) === 'fulfilled') $statusClass = 'fulfilled';
                    if (strtolower($req['status']) === 'matching donors' || strtolower($req['status']) === 'pending') $statusClass = 'pending';
                    
                    $urgencyClass = strtolower($req['urgency']) === 'urgent' ? 'urgent' : 'standard';
                ?>
                <tr data-name="<?= htmlspecialchars($req['hospital'] . ' ' . $req['city']) ?>" data-status="<?= strtolower(htmlspecialchars($req['status'])) ?>">
                    <td><strong><?= htmlspecialchars($req['hospital']) ?></strong><small>Requested <?= time_ago($req['created_at']) ?></small></td>
                    <td><span class="admin-blood-badge"><?= htmlspecialchars($req['blood_group']) ?></span><small class="request-units"><?= htmlspecialchars($req['units']) ?> unit(s)</small></td>
                    <td><?= htmlspecialchars($req['city']) ?></td>
                    <td><span class="urgency <?= $urgencyClass ?>"><?= htmlspecialchars($req['urgency']) ?></span></td>
                    <td><span class="request-status <?= $statusClass ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-danger request-action" type="button" data-request="<?= htmlspecialchars($req['hospital']) ?>" data-id="<?= $req['id'] ?>">Manage</button></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($requests) === 0): ?>
                <tr><td colspan="6" class="text-center">No blood requests found in the database.</td></tr>
                <?php endif; ?>
                </tbody></table></div><div class="admin-table-footer"><span>Showing requests from the LifeFlow response directory</span><div><button class="btn btn-sm btn-outline-secondary" disabled>Previous</button><button class="btn btn-sm btn-outline-secondary" disabled>Next</button></div></div></section></main>
        </div></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script>
        document.querySelector('.admin-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        const rows = [...document.querySelectorAll('#requestTable tr')]; const search = document.querySelector('#requestSearch'); const filter = document.querySelector('#requestFilter'); const count = document.querySelector('#requestCount');
        const updateRequests = () => { let visible = 0; rows.forEach(row => { const matchesSearch = row.dataset.name.toLowerCase().includes(search.value.toLowerCase()); const matchesStatus = !filter.value || row.dataset.status === filter.value; row.classList.toggle('d-none', !(matchesSearch && matchesStatus)); if (matchesSearch && matchesStatus) visible++; }); count.textContent = `Showing ${visible} ${visible === 1 ? 'blood request' : 'blood requests'}`; };
        search.addEventListener('input', updateRequests); filter.addEventListener('change', updateRequests);
        document.querySelectorAll('.request-action').forEach(button => button.addEventListener('click', function() {
            const reqId = this.dataset.id;
            const reqName = this.dataset.request;
            Swal.fire({
                title: `Manage ${reqName}`,
                html: `
                    <select id="swal-status" class="form-select mb-3">
                        <option value="Pending">Pending</option>
                        <option value="Matching donors">Matching donors</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <button id="swal-delete" class="btn btn-danger w-100"><i class="fa-solid fa-trash me-1"></i> Delete Request</button>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Status',
                didOpen: () => {
                    document.getElementById('swal-delete').addEventListener('click', () => {
                        if (confirm('Are you sure you want to delete this request?')) {
                            fetch('manage-requests.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'action=delete&id=' + reqId
                            }).then(() => location.reload());
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newStatus = document.getElementById('swal-status').value;
                    fetch('manage-requests.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=update_status&id=' + reqId + '&status=' + newStatus
                    }).then(() => location.reload());
                }
            });
        }));
    </script>
</body>
</html>

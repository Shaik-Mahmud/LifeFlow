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
            $stmt = $pdo->prepare("DELETE FROM donors WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['status'])) {
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE donors SET availability_status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } elseif ($_POST['action'] === 'send_message' && isset($_POST['message'])) {
            $message = trim($_POST['message']);
            if (!empty($message)) {
                $stmt = $pdo->prepare("INSERT INTO admin_donor_messages (donor_id, admin_id, subject, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $_SESSION['admin_id'], 'Message from Admin', $message]);
            }
        }
    }
    exit;
}

// Fetch donors from database
$stmt = $pdo->query("SELECT * FROM donors ORDER BY created_at DESC");
$donors = $stmt->fetchAll();

// Fetch stats
$totalDonors = $pdo->query("SELECT COUNT(*) FROM donors")->fetchColumn();
$availableDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Available'")->fetchColumn();
$pendingDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Pending'")->fetchColumn();
$unavailableDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Unavailable'")->fetchColumn();
$activeRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status != 'Completed'")->fetchColumn();
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage LifeFlow blood donor records.">
    <title>Manage Donors | LifeFlow Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-home.css" rel="stylesheet">
</head>

<body class="admin-home-page">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar"><a class="admin-sidebar-brand" href="index.php"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span></a><p class="admin-sidebar-label">Administration</p><nav class="admin-menu" aria-label="Admin navigation"><a href="index.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a><a href="manage-requests.php"><i class="fa-solid fa-heart-pulse"></i> Blood requests <span><?= $activeRequests ?></span></a><a class="active" href="manage-donors.php"><i class="fa-solid fa-users"></i> Donors</a><a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a><a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></nav><div class="admin-sidebar-bottom"><a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
        <div class="admin-main"><header class="admin-topbar"><button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="adminSidebar"><i class="fa-solid fa-bars"></i></button><div><p class="admin-eyebrow">Administrator portal</p><h1>Manage donors</h1></div><div class="admin-profile"><span class="admin-profile-avatar">AD</span><div><strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong><small>LifeFlow team</small></div></div></header>
            <main class="admin-content"><section class="admin-welcome"><div><span class="section-kicker">Donor directory</span><h2>Donor management</h2><p>Review profiles, update availability and keep donor records current.</p></div><a href="../donor-register.php" class="btn btn-danger"><i class="fa-solid fa-user-plus me-1"></i> Add donor</a></section>
                <section class="row g-4 admin-stats"><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-blue"><i class="fa-solid fa-users"></i></span><div><small>Total donors</small><strong><?= $totalDonors ?></strong><p>Registered</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-green"><i class="fa-solid fa-circle-check"></i></span><div><small>Available now</small><strong><?= $availableDonors ?></strong><p>Ready to donate</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-gold"><i class="fa-solid fa-clock"></i></span><div><small>Pending verification</small><strong><?= $pendingDonors ?></strong><p>Needs review</p></div></article></div><div class="col-sm-6 col-xl-3"><article class="admin-stat-card"><span class="admin-stat-icon icon-red"><i class="fa-solid fa-user-slash"></i></span><div><small>Unavailable</small><strong><?= $unavailableDonors ?></strong><p>Temporarily inactive</p></div></article></div></section>
                <section class="admin-panel mt-4"><div class="admin-panel-heading donor-management-heading"><div><h2>All donors</h2><p id="donorCount">Showing <?= count($donors) ?> donor records</p></div><div class="admin-donor-filters"><div class="admin-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="donorSearch" placeholder="Search name or location"></div><select id="statusFilter" aria-label="Filter by status"><option value="">All statuses</option><option value="available">Available</option><option value="pending">Pending</option><option value="unavailable">Unavailable</option></select></div></div><div class="table-responsive"><table class="table admin-table admin-donor-table align-middle"><thead><tr><th>Donor</th><th>Blood group</th><th>Location</th><th>Last donation</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody id="donorTable">
                <?php foreach ($donors as $donor): 
                    $statusClass = 'pending';
                    if (strtolower($donor['availability_status']) === 'available') $statusClass = 'fulfilled';
                    if (strtolower($donor['availability_status']) === 'unavailable') $statusClass = 'open';
                    
                    $initials = strtoupper(substr($donor['full_name'], 0, 1));
                    $parts = explode(' ', $donor['full_name']);
                    if (count($parts) > 1) {
                        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
                    }
                ?>
                <tr data-name="<?= htmlspecialchars($donor['full_name']) ?>" data-location="<?= htmlspecialchars($donor['area'] . ' ' . $donor['city']) ?>" data-status="<?= strtolower(htmlspecialchars($donor['availability_status'])) ?>">
                    <td>
                        <div class="donor-name-cell">
                            <span class="donor-avatar avatar-red"><?= htmlspecialchars($initials) ?></span>
                            <div><strong><?= htmlspecialchars($donor['full_name']) ?></strong><small><?= htmlspecialchars($donor['email']) ?></small></div>
                        </div>
                    </td>
                    <td><span class="admin-blood-badge"><?= htmlspecialchars($donor['blood_group']) ?></span></td>
                    <td><?= htmlspecialchars($donor['area'] . ', ' . $donor['city']) ?></td>
                    <td><?= !empty($donor['last_donation']) ? htmlspecialchars(date('d M Y', strtotime($donor['last_donation']))) : 'Never' ?></td>
                    <td><span class="request-status <?= $statusClass ?>"><?= htmlspecialchars($donor['availability_status']) ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary msg-donor-action" type="button" data-donor="<?= htmlspecialchars($donor['full_name']) ?>" data-id="<?= $donor['id'] ?>"><i class="fa-solid fa-envelope"></i></button>
                        <button class="btn btn-sm btn-outline-danger donor-action" type="button" data-donor="<?= htmlspecialchars($donor['full_name']) ?>" data-id="<?= $donor['id'] ?>">Manage</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($donors) === 0): ?>
                <tr><td colspan="6" class="text-center">No donors found in the database.</td></tr>
                <?php endif; ?>
                </tbody></table></div><div class="admin-table-footer"><span>Showing donor records from the LifeFlow directory</span><div><button class="btn btn-sm btn-outline-secondary" disabled>Previous</button><button class="btn btn-sm btn-outline-secondary" disabled>Next</button></div></div></section></main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script>
        document.querySelector('.admin-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        const donorRows = [...document.querySelectorAll('#donorTable tr')];
        const search = document.querySelector('#donorSearch');
        const status = document.querySelector('#statusFilter');
        const count = document.querySelector('#donorCount');
        const filterDonors = () => { let visible = 0; donorRows.forEach(row => { const matchesSearch = `${row.dataset.name} ${row.dataset.location}`.toLowerCase().includes(search.value.toLowerCase()); const matchesStatus = !status.value || row.dataset.status === status.value; row.classList.toggle('d-none', !(matchesSearch && matchesStatus)); if (matchesSearch && matchesStatus) visible++; }); count.textContent = `Showing ${visible} ${visible === 1 ? 'donor record' : 'donor records'}`; };
        search.addEventListener('input', filterDonors); status.addEventListener('change', filterDonors);
        document.querySelectorAll('.donor-action').forEach(button => button.addEventListener('click', function() {
            const donorId = this.dataset.id;
            const donorName = this.dataset.donor;
            Swal.fire({
                title: `Manage ${donorName}`,
                html: `
                    <select id="swal-status" class="form-select mb-3">
                        <option value="Available">Available</option>
                        <option value="Pending">Pending</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                    <button id="swal-delete" class="btn btn-danger w-100"><i class="fa-solid fa-trash me-1"></i> Delete Donor</button>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Status',
                didOpen: () => {
                    document.getElementById('swal-delete').addEventListener('click', () => {
                        if (confirm('Are you sure you want to delete this donor?')) {
                            fetch('manage-donors.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'action=delete&id=' + donorId
                            }).then(() => location.reload());
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newStatus = document.getElementById('swal-status').value;
                    fetch('manage-donors.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=update_status&id=' + donorId + '&status=' + newStatus
                    }).then(() => location.reload());
                }
            });
        }));

        document.querySelectorAll('.msg-donor-action').forEach(button => button.addEventListener('click', function() {
            const donorId = this.dataset.id;
            const donorName = this.dataset.donor;
            Swal.fire({
                title: `Message ${donorName}`,
                input: 'textarea',
                inputPlaceholder: 'Type your message here...',
                showCancelButton: true,
                confirmButtonText: 'Send Message',
                preConfirm: (text) => {
                    if (!text) {
                        Swal.showValidationMessage('Message cannot be empty');
                    }
                    return text;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('manage-donors.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=send_message&id=' + donorId + '&message=' + encodeURIComponent(result.value)
                    }).then(() => Swal.fire('Sent!', 'Message delivered to donor.', 'success'));
                }
            });
        }));
    </script>
</body>
</html>

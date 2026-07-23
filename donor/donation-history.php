<?php
session_start();
require_once '../includes/db.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header('Location: login.php');
    exit;
}

$donorId = $_SESSION['donor_id'];

// Fetch full donor profile
$stmt = $pdo->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->execute([$donorId]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$donorName = $donor['full_name'];
$lastDonation = $donor['last_donation'];

// Fetch donations
$stmtDonations = $pdo->prepare("
    SELECT d.*, br.hospital, br.city as request_city 
    FROM donations d 
    JOIN blood_requests br ON d.request_id = br.id 
    WHERE d.donor_id = ? 
    ORDER BY d.donation_date DESC
");
$stmtDonations->execute([$donorId]);
$donations = $stmtDonations->fetchAll(PDO::FETCH_ASSOC);

// Get initial for avatar
$initials = strtoupper(substr($donorName, 0, 1));
$parts = explode(' ', $donorName);
if (count($parts) > 1) {
    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
}

// Function to calculate time ago
function time_ago($datetime) {
    if (!$datetime) return '';
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);           
    $hours           = round($seconds / 3600);           
    $days          = round($seconds / 86400);           
    
    if($seconds <= 60) return "Just now";
    else if($minutes <= 60) return "$minutes min ago";
    else if($hours <= 24) return "$hours hrs ago";
    else return "$days days ago";
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View your donation history.">
    <title>Donation History | LifeFlow Donor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/donor-dashboard.css" rel="stylesheet">
</head>

<body class="donor-dashboard-page">
    <div class="donor-shell">
        <aside class="donor-sidebar" id="donorSidebar">
            <a class="donor-sidebar-brand" href="dashboard.php">
                <span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span>
            </a>
            <p class="donor-sidebar-label">Donor Portal</p>
            <nav class="donor-menu" aria-label="Donor navigation">
                <a href="dashboard.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a>
                <a href="nearby-requests.php"><i class="fa-solid fa-heart-pulse"></i> Nearby Requests</a>
                <a class="active" href="donation-history.php"><i class="fa-solid fa-clock-rotate-left"></i> Donation History</a>
                <a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
            </nav>
            <div class="donor-sidebar-bottom">
                <a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a>
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
            </div>
        </aside>

        <div class="donor-main">
            <header class="donor-topbar">
                <button class="donor-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="donorSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <p class="donor-eyebrow">Donor portal</p>
                    <h1>Donation History</h1>
                </div>
                <div class="donor-profile">
                    <span class="donor-profile-avatar"><?= htmlspecialchars($initials) ?></span>
                    <div>
                        <strong><?= htmlspecialchars($donorName) ?></strong>
                        <small><?= htmlspecialchars($donor['blood_group']) ?> Blood Group</small>
                    </div>
                </div>
            </header>

            <main class="donor-content">
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-md-4">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-red"><i class="fa-solid fa-droplet"></i></span>
                            <div>
                                <small>Total Donations</small>
                                <strong><?= $lastDonation ? '1' : '0' ?></strong>
                                <p>Bags donated</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-blue"><i class="fa-solid fa-award"></i></span>
                            <div>
                                <small>Donor Rank</small>
                                <strong><?= $lastDonation ? 'Bronze' : 'Starter' ?></strong>
                                <p>Keep donating to rank up</p>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <article class="donor-panel">
                            <div class="donor-panel-heading">
                                <div>
                                    <h2>Past Donations</h2>
                                    <p>A record of all your life-saving contributions.</p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" onclick="alert('Feature coming soon')"><i class="fa-solid fa-download"></i> Download Report</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table donor-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Hospital / Organization</th>
                                            <th>Location</th>
                                            <th>Donation Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($donations) > 0): ?>
                                        <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><strong><?= date('F j, Y', strtotime($donation['donation_date'])) ?></strong><small><?= time_ago($donation['donation_date']) ?></small></td>
                                            <td><?= htmlspecialchars($donation['hospital']) ?></td>
                                            <td><?= htmlspecialchars($donation['request_city']) ?></td>
                                            <td>Whole Blood (<?= htmlspecialchars($donation['units_donated']) ?> Bag)</td>
                                            <td><span class="request-status fulfilled"><?= htmlspecialchars($donation['status']) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No donation history found. Start your journey by donating!</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.querySelector('.donor-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    </script>
</body>
</html>

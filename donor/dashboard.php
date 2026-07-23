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
    // Session exists but donor doesn't (maybe deleted)
    session_destroy();
    header('Location: login.php');
    exit;
}

$donorName = $donor['full_name'];
$donorBloodGroup = $donor['blood_group'];
$donorCity = $donor['city'];

// Fetch nearby requests (matching blood group and city, or just matching blood group for broader match)
$reqStmt = $pdo->prepare("SELECT * FROM blood_requests WHERE blood_group = ? AND status != 'Completed' ORDER BY created_at DESC LIMIT 5");
$reqStmt->execute([$donorBloodGroup]);
$nearbyRequests = $reqStmt->fetchAll(PDO::FETCH_ASSOC);

$nearbyCount = count($nearbyRequests);

// Get initial for avatar
$initials = strtoupper(substr($donorName, 0, 1));
$parts = explode(' ', $donorName);
if (count($parts) > 1) {
    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
}

// Function to calculate time ago
function time_ago($datetime) {
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
    <meta name="description" content="LifeFlow donor dashboard.">
    <title>Donor Dashboard | LifeFlow</title>
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
                <a class="active" href="dashboard.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a>
                <a href="nearby-requests.php"><i class="fa-solid fa-heart-pulse"></i> Nearby Requests <span><?= $nearbyCount ?></span></a>
                <a href="donation-history.php"><i class="fa-solid fa-clock-rotate-left"></i> Donation History</a>
                <a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
                <a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
            </nav>
            <div class="donor-sidebar-bottom">
                <a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a>
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
            </div>
        </aside>

        <div class="donor-main">
            <header class="donor-topbar">
                <button class="donor-menu-toggle" type="button" aria-label="Toggle navigation"
                    aria-controls="donorSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <p class="donor-eyebrow">Donor portal</p>
                    <h1>Dashboard</h1>
                </div>
                <div class="donor-profile">
                    <span class="donor-profile-avatar"><?= htmlspecialchars($initials) ?></span>
                    <div>
                        <strong><?= htmlspecialchars($donorName) ?></strong>
                        <small><?= htmlspecialchars($donorBloodGroup) ?> Blood Group</small>
                    </div>
                </div>
            </header>

            <main class="donor-content">
                <section class="donor-welcome">
                    <div>
                        <span class="section-kicker">Overview</span>
                        <h2>Welcome back, <?= htmlspecialchars(explode(' ', $donorName)[0]) ?>.</h2>
                        <p>Thank you for being a lifesaver. Here is your impact summary.</p>
                    </div>
                    <a href="../search-donor.php" class="btn btn-danger"><i
                            class="fa-solid fa-magnifying-glass me-1"></i> Find Donors</a>
                </section>

                <section class="row g-4 donor-stats">
                    <div class="col-sm-6 col-xl-3">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-red"><i class="fa-solid fa-heart"></i></span>
                            <div>
                                <small>Total Donations</small>
                                <strong>0</strong>
                                <p>0 this year</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-blue"><i class="fa-solid fa-user-group"></i></span>
                            <div>
                                <small>Lives Impacted</small>
                                <strong>0</strong>
                                <p>0 recent</p>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-green"><i class="fa-solid fa-calendar-check"></i></span>
                            <div>
                                <small>Next Eligible Date</small>
                                <strong><?= !empty($donor['last_donation']) ? date('M j, Y', strtotime($donor['last_donation'] . ' + 3 months')) : 'Anytime' ?></strong>
                                <p style="color: #428558;"><?= strtolower($donor['availability_status']) === 'available' ? 'You are eligible to donate' : 'Currently unavailable' ?></p>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="donor-stat-card">
                            <span class="donor-stat-icon icon-gold"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <small>Compatible Requests</small>
                                <strong><?= $nearbyCount ?></strong>
                                <p>Needs <?= htmlspecialchars($donorBloodGroup) ?> blood</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4 mt-1">
                    <div class="col-xl-8" id="requests">
                        <article class="donor-panel">
                            <div class="donor-panel-heading">
                                <div>
                                    <h2>Compatible Blood Requests</h2>
                                    <p>Requests matching your blood group.</p>
                                </div>
                                <a href="nearby-requests.php">View all <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="table donor-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Patient / Hospital</th>
                                            <th>Blood Type</th>
                                            <th>Location</th>
                                            <th>Urgency</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($nearbyRequests as $req): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($req['hospital']) ?></strong><small>Requested <?= time_ago($req['created_at']) ?></small></td>
                                                <td><span class="donor-blood-badge"><?= htmlspecialchars($req['blood_group']) ?></span></td>
                                                <td><?= htmlspecialchars($req['city']) ?></td>
                                                <td><span class="urgency <?= strtolower($req['urgency']) === 'urgent' ? 'urgent' : 'standard' ?>"><?= htmlspecialchars($req['urgency']) ?></span></td>
                                                <td><button class="btn btn-sm btn-outline-danger" onclick="alert('Response feature coming soon.')">Respond</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if(empty($nearbyRequests)): ?>
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No compatible requests found at the moment.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div class="col-xl-4" id="activity">
                        <article class="donor-panel activity-panel">
                            <div class="donor-panel-heading">
                                <div>
                                    <h2>Your Activity</h2>
                                    <p>Recent updates on your profile.</p>
                                </div>
                            </div>
                            <div class="activity-list">
                                <div>
                                    <span class="activity-icon icon-blue"><i class="fa-solid fa-user-check"></i></span>
                                    <p><strong>Profile Updated</strong><small>Availability set to <?= htmlspecialchars($donor['availability_status']) ?></small><time><?= date('F j, Y') ?></time></p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        document.querySelector('.donor-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    </script>
</body>
</html>
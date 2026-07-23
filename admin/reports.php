<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch general stats
$totalRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
$completedRequests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'Completed'")->fetchColumn();
$fulfilmentRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100) : 0;

$activeDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE availability_status = 'Available'")->fetchColumn();
$partnerHospitals = $pdo->query("SELECT COUNT(DISTINCT hospital) FROM blood_requests")->fetchColumn();

// Fetch active requests count for the sidebar
$activeRequestsCount = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status != 'Completed'")->fetchColumn();

// Fetch stats from View
$viewStats = $pdo->query("SELECT * FROM vw_blood_requests_summary")->fetchAll(PDO::FETCH_ASSOC);

// Subquery example: Donors who donated more than average
$topDonors = $pdo->query("
    SELECT d.full_name, COUNT(don.id) as donation_count 
    FROM donors d 
    JOIN donations don ON d.id = don.donor_id 
    GROUP BY d.id 
    HAVING donation_count >= (
        SELECT AVG(cnt) FROM (SELECT COUNT(*) as cnt FROM donations GROUP BY donor_id) as avg_donations
    )
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LifeFlow platform analytics and reports.">
    <title>Reports | LifeFlow Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-home.css" rel="stylesheet">
    <style>
        .report-card { padding: 25px; border: 1px solid #e5e9eb; border-radius: 14px; background: #fff; height: 100%; display: flex; flex-direction: column; }
        .report-card-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 12px; font-size: 1.25rem; margin-bottom: 15px; }
        .report-card h3 { font-size: 1.1rem; font-weight: 800; color: var(--ink); margin-bottom: 8px; }
        .report-card p { color: var(--muted); font-size: .85rem; flex-grow: 1; margin-bottom: 20px; }
    </style>
</head>

<body class="admin-home-page">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <a class="admin-sidebar-brand" href="index.php">
                <span class="brand-mark"><i class="fa-solid fa-droplet"></i></span><span>LifeFlow</span>
            </a>
            <p class="admin-sidebar-label">Administration</p>
            <nav class="admin-menu" aria-label="Admin navigation">
                <a href="index.php"><i class="fa-solid fa-grid-2"></i> Dashboard</a>
                <a href="manage-requests.php"><i class="fa-solid fa-heart-pulse"></i> Blood requests <span><?= $activeRequestsCount ?></span></a>
                <a href="manage-donors.php"><i class="fa-solid fa-users"></i> Donors</a>
                <a href="messages.php"><i class="fa-solid fa-envelope"></i> Messages</a>
                <a class="active" href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a>
            </nav>
            <div class="admin-sidebar-bottom">
                <a href="../index.html"><i class="fa-solid fa-arrow-up-right-from-square"></i> View website</a>
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="adminSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <p class="admin-eyebrow">Administrator portal</p>
                    <h1>Platform Analytics</h1>
                </div>
                <div class="admin-profile">
                    <span class="admin-profile-avatar">AD</span>
                    <div>
                        <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>
                        <small>LifeFlow team</small>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                <section class="admin-welcome">
                    <div>
                        <span class="section-kicker">Reports & Insights</span>
                        <h2>Data and Analytics</h2>
                        <p>Generate, view, and export platform usage and donation statistics.</p>
                    </div>
                    <button class="btn btn-outline-danger" onclick="alert('Export functionality coming soon.')"><i class="fa-solid fa-download me-1"></i> Export Master Data</button>
                </section>

                <section class="row g-4 admin-stats mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon icon-blue"><i class="fa-solid fa-chart-pie"></i></span>
                            <div>
                                <small>Fulfilment Rate</small>
                                <strong><?= $fulfilmentRate ?>%</strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon icon-red"><i class="fa-solid fa-droplet"></i></span>
                            <div>
                                <small>Completed Requests</small>
                                <strong><?= $completedRequests ?></strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon icon-green"><i class="fa-solid fa-users-viewfinder"></i></span>
                            <div>
                                <small>Active Donors</small>
                                <strong><?= $activeDonors ?></strong>
                            </div>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="admin-stat-card">
                            <span class="admin-stat-icon icon-gold"><i class="fa-solid fa-hospital"></i></span>
                            <div>
                                <small>Partner Hospitals</small>
                                <strong><?= $partnerHospitals ?></strong>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-md-4">
                        <div class="report-card">
                            <span class="report-card-icon icon-blue"><i class="fa-solid fa-file-invoice"></i></span>
                            <h3>Monthly Summary</h3>
                            <p>Detailed breakdown of all requests, donors registered, and donations completed over the last 30 days.</p>
                            <button class="btn btn-light w-100 text-primary fw-bold" onclick="alert('Report generation coming soon.')">Generate Report</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-card">
                            <span class="report-card-icon icon-red"><i class="fa-solid fa-layer-group"></i></span>
                            <h3>Blood Type Distribution</h3>
                            <p>Current analysis of available donor base broken down by blood groups and geography.</p>
                            <button class="btn btn-light w-100 text-danger fw-bold" onclick="alert('Report generation coming soon.')">Generate Report</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-card">
                            <span class="report-card-icon icon-green"><i class="fa-solid fa-hospital-user"></i></span>
                            <h3>Hospital Demand</h3>
                            <p>Historical trends of blood requests submitted by hospitals to identify peak seasons.</p>
                            <button class="btn btn-light w-100 text-success fw-bold" onclick="alert('Report generation coming soon.')">Generate Report</button>
                        </div>
                    </div>
                </section>

                <section class="admin-panel mt-4">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>Blood Request Summary (From View)</h2>
                            <p>Aggregated data using database views and joins.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead>
                                <tr>
                                    <th>Blood Group</th>
                                    <th>Total Requests</th>
                                    <th>Completed</th>
                                    <th>Units Requested</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewStats as $stat): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($stat['blood_group']) ?></strong></td>
                                    <td><?= htmlspecialchars($stat['total_requests']) ?></td>
                                    <td><?= htmlspecialchars($stat['completed_requests']) ?></td>
                                    <td><?= htmlspecialchars($stat['total_units_requested']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($viewStats) === 0): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <section class="admin-panel mt-4">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>Top Donors (Subqueries)</h2>
                            <p>Donors who have donated equal to or more than the average.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead>
                                <tr>
                                    <th>Donor Name</th>
                                    <th>Total Donations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDonors as $donor): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($donor['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($donor['donation_count']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($topDonors) === 0): ?>
                                <tr><td colspan="2" class="text-center text-muted py-4">No top donors found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
    
    <script>
        document.querySelector('.admin-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    </script>
</body>
</html>

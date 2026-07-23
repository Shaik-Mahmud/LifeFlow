<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/blood_matching.php';
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
$donorBloodGroup = $donor['blood_group'];
$donorCity = $donor['city'];

// Fetch nearby requests (matching compatible blood groups)
$compatibleGroups = getCompatibleBloodGroups($donorBloodGroup, true); // As a donor giving
$placeholders = implode(',', array_fill(0, count($compatibleGroups), '?'));
$reqStmt = $pdo->prepare("SELECT * FROM blood_requests WHERE blood_group IN ($placeholders) AND status != 'Completed' ORDER BY created_at DESC");
$reqStmt->execute($compatibleGroups);
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
    <meta name="description" content="View nearby blood requests.">
    <title>Nearby Requests | LifeFlow Donor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/donor-dashboard.css" rel="stylesheet">
    <style>
        .request-card { padding: 20px; border: 1px solid var(--line); border-radius: 12px; background: #fff; margin-bottom: 15px; display: flex; flex-direction: column; gap: 15px; }
        @media(min-width: 768px) { .request-card { flex-direction: row; align-items: center; justify-content: space-between; } }
        .request-details h3 { margin: 0 0 5px; font-size: 1.1rem; font-weight: 800; color: var(--ink); }
        .request-details p { margin: 0; color: var(--muted); font-size: .85rem; }
        .request-meta { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px; }
        .request-meta span { display: inline-flex; align-items: center; gap: 5px; color: #59646d; font-size: .78rem; }
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters select { border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; color: var(--ink); outline: none; }
        .filters select:focus { border-color: #f2aeb5; box-shadow: 0 0 0 .15rem rgba(220,53,69,.1); }
    </style>
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
                <a class="active" href="nearby-requests.php"><i class="fa-solid fa-heart-pulse"></i> Nearby Requests <span><?= $nearbyCount ?></span></a>
                <a href="donation-history.php"><i class="fa-solid fa-clock-rotate-left"></i> Donation History</a>
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
                    <h1>Nearby Requests</h1>
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
                <div class="row">
                    <div class="col-12">
                        <article class="donor-panel">
                            <div class="donor-panel-heading align-items-center flex-wrap">
                                <div>
                                    <h2>Compatible Requests in Your Area</h2>
                                    <p>Patients near <?= htmlspecialchars($donorCity) ?> who can receive your <?= htmlspecialchars($donorBloodGroup) ?> blood.</p>
                                </div>
                                <div class="filters mt-3 mt-md-0">
                                    <select aria-label="Sort by">
                                        <option value="recent">Sort by: Most Recent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="requests-list mt-4">
                                <?php foreach($nearbyRequests as $req): ?>
                                    <div class="request-card">
                                        <div class="request-details">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="donor-blood-badge"><?= htmlspecialchars($req['blood_group']) ?></span>
                                                <span class="urgency <?= strtolower($req['urgency']) === 'urgent' ? 'urgent' : 'standard' ?>"><?= htmlspecialchars($req['urgency']) ?></span>
                                            </div>
                                            <h3><?= htmlspecialchars($req['hospital']) ?></h3>
                                            <p><?= htmlspecialchars($req['notes'] ?? 'Patient requires blood for treatment.') ?></p>
                                            <div class="request-meta">
                                                <span><i class="fa-solid fa-location-dot text-danger"></i> <?= htmlspecialchars($req['city']) ?></span>
                                                <span><i class="fa-regular fa-clock"></i> Requested <?= time_ago($req['created_at']) ?></span>
                                                <span><i class="fa-solid fa-droplet text-danger"></i> <?= htmlspecialchars($req['units']) ?> Bag(s) needed</span>
                                            </div>
                                        </div>
                                        <div class="request-actions mt-3 mt-md-0 text-md-end">
                                            <button class="btn btn-danger w-100 mb-2 respond-btn" data-blood="<?= htmlspecialchars($req['blood_group']) ?>" data-id="<?= $req['id'] ?>" data-units="<?= $req['units'] ?>">Respond Now</button>
                                            <button class="btn btn-outline-secondary w-100 btn-sm" onclick="alert('Map view coming soon.')"><i class="fa-solid fa-map"></i> View Map</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if(empty($nearbyRequests)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <h4>No requests found</h4>
                                        <p>There are currently no compatible blood requests in your area.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector('.donor-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        
        document.querySelectorAll('.respond-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bloodGrp = this.getAttribute('data-blood');
                const reqId = this.getAttribute('data-id');
                const units = this.getAttribute('data-units');
                
                Swal.fire({
                    title: '<strong>Ready to save a life?</strong>',
                    html: `
                        <div class="mb-3 mt-2 text-muted">
                            You are about to respond to this blood request. The hospital will securely receive your contact details to coordinate the donation.
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 p-3 bg-light rounded-3 mb-2 border">
                            <span class="donor-blood-badge fs-6">${bloodGrp}</span>
                            <i class="fa-solid fa-arrow-right-long text-muted mx-2"></i>
                            <span class="fw-bold text-dark"><i class="fa-solid fa-hospital text-danger me-1"></i> Hospital</span>
                        </div>
                    `,
                    icon: 'info',
                    showCloseButton: true,
                    showCancelButton: true,
                    focusConfirm: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'btn btn-danger btn-lg px-4 mx-2',
                        cancelButton: 'btn btn-light btn-lg px-4 mx-2 border',
                        title: 'fs-3 fw-bold text-dark'
                    },
                    confirmButtonText: '<i class="fa-solid fa-heart-pulse me-1"></i> Yes, notify them',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('request_id', reqId);
                        formData.append('units', units);
                        
                        fetch('process_donation.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Confirmed!', data.message, 'success');
                                this.disabled = true;
                                this.textContent = 'Responded';
                                this.classList.remove('btn-danger');
                                this.classList.add('btn-success');
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'An error occurred processing your response.', 'error');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>

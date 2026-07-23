<?php
session_start();
require_once 'includes/db.php';

$requestIdInput = $_GET['requestId'] ?? '';
$requestData = null;
$error = '';

if (!empty($requestIdInput)) {
    // Extract only numbers from the input
    $id = preg_replace('/[^0-9]/', '', $requestIdInput);
    
    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM blood_requests WHERE id = ?");
            $stmt->execute([$id]);
            $requestData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$requestData) {
                $error = 'No request found with this ID.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred while fetching the request.';
        }
    } else {
        $error = 'Invalid Request ID format. Please include the numeric ID.';
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Track your LifeFlow emergency blood request.">
    <title>Track Request | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .tracking-timeline { position: relative; padding-left: 30px; margin-top: 30px; }
        .tracking-timeline::before { content: ""; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background: var(--line); }
        .timeline-step { position: relative; margin-bottom: 25px; }
        .timeline-step::before { content: ""; position: absolute; left: -30px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: var(--line); border: 3px solid #fff; box-shadow: 0 0 0 1px var(--line); }
        .timeline-step.active::before { background: var(--primary); box-shadow: 0 0 0 1px var(--primary); }
        .timeline-step.completed::before { background: var(--primary); border-color: var(--primary); box-shadow: 0 0 0 3px rgba(220,53,69,.2); }
        .timeline-step h4 { font-size: 1rem; font-weight: 700; margin: 0 0 5px; color: var(--ink); }
        .timeline-step p { font-size: .85rem; color: var(--muted); margin: 0; }
        .timeline-step time { font-size: .75rem; color: #a1a9b0; display: block; margin-top: 4px; }
        .tracker-card { max-width: 600px; margin: 0 auto; padding: 40px; border-radius: var(--radius); background: #fff; box-shadow: var(--shadow); border: 1px solid var(--line); }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top lifeflow-nav" aria-label="Primary navigation">
        <div class="container"><a class="navbar-brand" href="index.html"><span class="brand-mark"><i
                        class="fa-solid fa-droplet"></i></span>LifeFlow</a><button class="navbar-toggler border-0"
                type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav"
                aria-expanded="false" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
            <div class="collapse navbar-collapse" id="siteNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="why-donate.html">Why Donate</a></li>
                    <li class="nav-item"><a class="nav-link" href="search-donor.php">Find Donors</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <div class="d-flex flex-wrap gap-2 mt-3 mt-lg-0"><a class="btn btn-outline-danger" href="donor/login.php">Donor
                        login</a><a class="btn btn-danger"
                        href="blood-request.php">Request blood</a></div>
            </div>
        </div>
    </nav>
    <main>
        <section class="page-hero pb-5">
            <div class="container text-center text-md-start">
                <span class="section-kicker">Request Tracking</span>
                <h1>Track your request</h1>
                <p>Enter your Request ID to check the real-time status of your emergency blood request.</p>
            </div>
        </section>

        <section class="section pt-0">
            <div class="container">
                <div class="tracker-card mt-n5 position-relative z-1">
                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form id="trackingForm" method="GET" action="request-tracking.php">
                        <label class="form-label fw-bold">Request ID</label>
                        <div class="input-group mb-3">
                            <input type="text" name="requestId" class="form-control form-control-lg" id="requestId" placeholder="e.g. 1024 or REQ-1024" value="<?= htmlspecialchars($requestIdInput) ?>" required>
                            <button class="btn btn-danger px-4" type="submit">Track</button>
                        </div>
                    </form>

                    <?php if ($requestData): ?>
                        <?php 
                            $status = $requestData['status']; // 'Pending', 'Active', 'Completed' (assuming these statuses based on typical flow)
                            $createdAt = date('M j, Y g:i A', strtotime($requestData['created_at']));
                        ?>
                        <div id="trackingResult" class="mt-5 border-top pt-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h3 class="h5 fw-bold mb-1">Request #<?= htmlspecialchars($requestData['id']) ?></h3>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= htmlspecialchars($requestData['urgency']) ?> • <?= htmlspecialchars($requestData['blood_group']) ?> Blood</span>
                                </div>
                            </div>

                            <div class="tracking-timeline">
                                <div class="timeline-step completed">
                                    <h4>Request Received</h4>
                                    <p>Your blood request has been successfully registered in our system.</p>
                                    <time><?= $createdAt ?></time>
                                </div>
                                <div class="timeline-step <?= ($status === 'Pending') ? 'active' : 'completed' ?>">
                                    <h4>Review & Verification</h4>
                                    <p>Our team verifies the hospital details and requirement.</p>
                                </div>
                                <div class="timeline-step <?= ($status === 'Active') ? 'active' : ($status === 'Completed' ? 'completed' : '') ?>">
                                    <h4>Matching Donors</h4>
                                    <p>The request is visible to compatible donors in the area.</p>
                                </div>
                                <div class="timeline-step <?= ($status === 'Completed') ? 'completed' : '' ?>">
                                    <h4>Donation Completed</h4>
                                    <p>The request is fully fulfilled.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

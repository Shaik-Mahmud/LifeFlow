<?php
session_start();
require_once '../includes/db.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header('Location: login.php');
    exit;
}

$donorId = $_SESSION['donor_id'];
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $bloodGroup = $_POST['blood_group'] ?? '';
    $lastDonation = $_POST['last_donation'] ?? null;
    if (empty($lastDonation)) $lastDonation = null;
    $weight = (int)($_POST['weight'] ?? 0);
    $city = $_POST['city'] ?? '';
    $area = $_POST['area'] ?? '';
    $availabilityStatus = isset($_POST['availability_status']) ? 'Available' : 'Unavailable';

    try {
        $stmt = $pdo->prepare("
            UPDATE donors SET 
            full_name = ?, email = ?, phone = ?, dob = ?, blood_group = ?, 
            last_donation = ?, weight = ?, city = ?, area = ?, availability_status = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $fullName, $email, $phone, $dob, $bloodGroup,
            $lastDonation, $weight, $city, $area, $availabilityStatus, $donorId
        ]);
        
        // Update session variables if changed
        $_SESSION['donor_name'] = $fullName;
        $_SESSION['donor_email'] = $email;
        
        $successMessage = 'Profile updated successfully!';
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $errorMessage = 'This email is already registered by another user.';
        } else {
            $errorMessage = 'An error occurred while updating the profile.';
        }
    }
}

// Fetch current donor details
$stmt = $pdo->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->execute([$donorId]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Prepare avatar initials
$donorName = $donor['full_name'];
$initials = strtoupper(substr($donorName, 0, 1));
$parts = explode(' ', $donorName);
if (count($parts) > 1) {
    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage your LifeFlow donor profile.">
    <title>My Profile | LifeFlow Donor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/donor-dashboard.css" rel="stylesheet">
    <style>
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .profile-header-avatar { width: 80px; height: 80px; border-radius: 50%; background: var(--primary); color: #fff; font-size: 2rem; display: grid; place-items: center; }
        .profile-header h2 { margin: 0; font-weight: 800; font-size: 1.5rem; color: var(--ink); }
        .profile-header p { margin: 0; color: var(--muted); }
        .availability-switch { margin-top: 10px; display: inline-flex; align-items: center; gap: 10px; background: var(--soft); padding: 8px 15px; border-radius: 8px; border: 1px solid #f2aeb5; }
        .form-section { padding-top: 20px; margin-top: 25px; border-top: 1px solid var(--line); }
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
                <a href="nearby-requests.php"><i class="fa-solid fa-heart-pulse"></i> Nearby Requests</a>
                <a href="donation-history.php"><i class="fa-solid fa-clock-rotate-left"></i> Donation History</a>
                <a class="active" href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
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
                    <h1>My Profile</h1>
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
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-8">
                        <article class="donor-panel">
                            
                            <?php if ($successMessage): ?>
                                <div class="alert alert-success mb-4"><?= htmlspecialchars($successMessage) ?></div>
                            <?php endif; ?>
                            <?php if ($errorMessage): ?>
                                <div class="alert alert-danger mb-4"><?= htmlspecialchars($errorMessage) ?></div>
                            <?php endif; ?>

                            <div class="profile-header">
                                <div class="profile-header-avatar">
                                    <?= htmlspecialchars($initials) ?>
                                </div>
                                <div>
                                    <h2><?= htmlspecialchars($donorName) ?></h2>
                                    <p>Joined LifeFlow in <?= date('F Y', strtotime($donor['created_at'])) ?></p>
                                </div>
                            </div>

                            <form method="POST" action="profile.php">
                                <div class="availability-switch mb-4">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="availability_status" role="switch" id="availabilitySwitch" <?= strtolower($donor['availability_status']) === 'available' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold <?= strtolower($donor['availability_status']) === 'available' ? 'text-danger' : 'text-secondary' ?>" for="availabilitySwitch"><?= strtolower($donor['availability_status']) === 'available' ? 'Available to Donate' : 'Currently Unavailable' ?></label>
                                    </div>
                                </div>
                                
                                <div class="donor-panel-heading mt-4">
                                    <div>
                                        <h2>Personal Information</h2>
                                        <p>Update your basic contact details.</p>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fullName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" id="fullName" value="<?= htmlspecialchars($donor['full_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($donor['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" id="phone" value="<?= htmlspecialchars($donor['phone']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dob" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" id="dob" value="<?= htmlspecialchars($donor['dob'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="donor-panel-heading">
                                        <div>
                                            <h2>Health & Donation Details</h2>
                                            <p>These details help match you with the right patients safely.</p>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="bloodGroup" class="form-label">Blood Group</label>
                                            <select class="form-select" name="blood_group" id="bloodGroup" required>
                                                <?php
                                                $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                                foreach ($groups as $g) {
                                                    $selected = $donor['blood_group'] === $g ? 'selected' : '';
                                                    echo "<option value=\"$g\" $selected>$g</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastDonation" class="form-label">Last Donation Date</label>
                                            <input type="date" class="form-control" name="last_donation" id="lastDonation" value="<?= !empty($donor['last_donation']) ? htmlspecialchars(date('Y-m-d', strtotime($donor['last_donation']))) : '' ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="weight" class="form-label">Weight (kg)</label>
                                            <input type="number" class="form-control" name="weight" id="weight" value="<?= htmlspecialchars($donor['weight']) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="donor-panel-heading">
                                        <div>
                                            <h2>Location</h2>
                                            <p>Helps us match you with emergency requests nearby.</p>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="city" class="form-label">City</label>
                                            <select class="form-select" name="city" id="city" required>
                                                <option value="">Select your city</option>
                                                <option value="Dhaka" <?= $donor['city'] === 'Dhaka' ? 'selected' : '' ?>>Dhaka</option>
                                                <option value="Chattogram" <?= $donor['city'] === 'Chattogram' ? 'selected' : '' ?>>Chattogram</option>
                                                <option value="Sylhet" <?= $donor['city'] === 'Sylhet' ? 'selected' : '' ?>>Sylhet</option>
                                                <option value="Rajshahi" <?= $donor['city'] === 'Rajshahi' ? 'selected' : '' ?>>Rajshahi</option>
                                                <option value="Khulna" <?= $donor['city'] === 'Khulna' ? 'selected' : '' ?>>Khulna</option>
                                                <option value="Barishal" <?= $donor['city'] === 'Barishal' ? 'selected' : '' ?>>Barishal</option>
                                                <option value="Rangpur" <?= $donor['city'] === 'Rangpur' ? 'selected' : '' ?>>Rangpur</option>
                                                <option value="Mymensingh" <?= $donor['city'] === 'Mymensingh' ? 'selected' : '' ?>>Mymensingh</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="area" class="form-label">Area / Thana</label>
                                            <input type="text" class="form-control" name="area" id="area" value="<?= htmlspecialchars($donor['area']) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 text-end border-top">
                                    <a href="dashboard.php" class="btn btn-light me-2">Cancel</a>
                                    <button type="submit" class="btn btn-danger px-4">Save Changes</button>
                                </div>
                            </form>
                        </article>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.donor-menu-toggle').addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        
        document.addEventListener('DOMContentLoaded', () => {
            const switchEl = document.getElementById('availabilitySwitch');
            switchEl.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if(this.checked) {
                    label.textContent = 'Available to Donate';
                    label.classList.add('text-danger');
                    label.classList.remove('text-secondary');
                } else {
                    label.textContent = 'Currently Unavailable';
                    label.classList.remove('text-danger');
                    label.classList.add('text-secondary');
                }
            });
        });
    </script>
</body>
</html>

<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/blood_matching.php';

$searchBlood = $_GET['searchBlood'] ?? '';
$searchLocation = trim($_GET['searchLocation'] ?? '');
$matchType = $_GET['matchType'] ?? 'exact';

$query = "SELECT * FROM donors WHERE availability_status = 'Available'";
$params = [];

if (!empty($searchBlood)) {
    if ($matchType === 'compatible') {
        $compatibleGroups = getCompatibleBloodGroups($searchBlood, false); // As a patient receiving
        $placeholders = implode(',', array_fill(0, count($compatibleGroups), '?'));
        $query .= " AND blood_group IN ($placeholders)";
        $params = array_merge($params, $compatibleGroups);
    } else {
        $query .= " AND blood_group = ?";
        $params[] = $searchBlood;
    }
}

if (!empty($searchLocation)) {
    $query .= " AND (city LIKE ? OR area LIKE ?)";
    $params[] = "%$searchLocation%";
    $params[] = "%$searchLocation%";
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $donors = [];
}

// Function to generate random avatar color class based on name length
function getAvatarClass($name) {
    $classes = ['avatar-red', 'avatar-pink', 'avatar-dark', 'avatar-gold', 'avatar-blue', 'avatar-purple'];
    return $classes[strlen($name) % count($classes)];
}

// Function to get initials
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper($w[0] ?? '');
        if (strlen($initials) >= 2) break;
    }
    return $initials;
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find compatible blood donors near you with LifeFlow.">
    <title>Find Donors | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/search-donor.css" rel="stylesheet">
</head>

<body class="donor-search-page">
    <nav class="navbar navbar-expand-lg lifeflow-nav donor-search-nav" aria-label="Primary navigation">
        <div class="container"><a class="navbar-brand" href="index.html"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span>LifeFlow</a><a class="btn btn-danger btn-sm" href="blood-request.php">Request blood</a></div>
    </nav>

    <main>
        <section class="search-hero"><div class="container text-center"><span class="section-kicker"><i class="fa-solid fa-magnifying-glass me-1"></i> Find support nearby</span><h1>Find a compatible <span class="text-danger">blood donor.</span></h1><p>Search our donor community by blood group and location to find people ready to help.</p></div></section>

        <section class="search-content"><div class="container">
            <form class="search-box" id="donorSearch" method="GET" action="search-donor.php">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="searchBlood">Blood group</label>
                        <select class="form-select" id="searchBlood" name="searchBlood">
                            <option value="">Any blood group</option>
                            <?php
                            $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            foreach ($bloodGroups as $bg) {
                                $selected = ($searchBlood === $bg) ? 'selected' : '';
                                echo "<option value=\"$bg\" $selected>$bg</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="searchLocation">City or area</label>
                        <div class="search-input-wrap">
                            <i class="fa-solid fa-location-dot"></i>
                            <input class="form-control" id="searchLocation" name="searchLocation" type="search" placeholder="e.g. Dhaka, Dhanmondi" value="<?= htmlspecialchars($searchLocation) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-danger w-100 search-button" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Search donors</button>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="matchType" name="matchType" value="compatible" <?= $matchType === 'compatible' ? 'checked' : '' ?>>
                            <label class="form-check-label text-muted small" for="matchType">
                                Show all compatible donors (not just exact matches)
                            </label>
                        </div>
                    </div>
                </div>
            </form>

            <div class="results-header"><div><span class="section-kicker">Available matches</span><h2>Donors ready to help</h2></div><p id="resultCount">Showing <?= count($donors) ?> nearby <?= count($donors) === 1 ? 'donor' : 'donors' ?></p></div>
            
            <div class="row g-4" id="donorResults">
                <?php if (count($donors) > 0): ?>
                    <?php foreach ($donors as $donor): ?>
                        <div class="col-md-6 col-xl-4 donor-item">
                            <article class="donor-card">
                                <div class="donor-card-top">
                                    <div class="donor-avatar <?= getAvatarClass($donor['full_name']) ?>"><?= getInitials($donor['full_name']) ?></div>
                                    <div>
                                        <h3><?= htmlspecialchars($donor['full_name']) ?></h3>
                                        <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($donor['area'] . ', ' . $donor['city']) ?></p>
                                    </div>
                                    <span class="availability-status"><i class="fa-solid fa-circle"></i> Available</span>
                                </div>
                                <div class="donor-details">
                                    <span class="blood-badge"><?= htmlspecialchars($donor['blood_group']) ?></span>
                                    <span><i class="fa-regular fa-clock"></i> 
                                        <?php 
                                            if ($donor['last_donation']) {
                                                echo 'Last donated ' . date('M j, Y', strtotime($donor['last_donation']));
                                            } else {
                                                echo 'No recent donation';
                                            }
                                        ?>
                                    </span>
                                </div>
                                <button class="btn btn-outline-danger w-100 contact-donor" type="button" data-donor="<?= htmlspecialchars($donor['full_name']) ?>">Contact donor <i class="fa-solid fa-arrow-right ms-1"></i></button>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-results"><i class="fa-solid fa-magnifying-glass"></i><h3>No matching donors found</h3><p>Try a different blood group or a broader location search.</p><a class="btn btn-outline-danger" href="search-donor.php">Clear search</a></div>
                <?php endif; ?>
            </div>
            
            <div class="search-help"><div class="search-help-icon"><i class="fa-solid fa-circle-info"></i></div><p><strong>Need blood urgently?</strong> Submit an emergency request and we’ll notify compatible donors nearby.</p><a class="btn btn-danger" href="blood-request.php">Request blood <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
        </div></section>
    </main>

    <footer class="search-footer"><div class="container d-flex flex-column flex-sm-row justify-content-between gap-2"><span>© 2026 LifeFlow. All rights reserved.</span><span><a href="about.html">About LifeFlow</a> · <a href="contact.php">Contact</a></span></div></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.querySelectorAll('.contact-donor').forEach(button => button.addEventListener('click', function() {
            const donorName = this.dataset.donor;
            Swal.fire({
                title: `Contact ${donorName}`,
                html: `
                    <input type="text" id="swal-name" class="swal2-input" placeholder="Your Name">
                    <input type="email" id="swal-email" class="swal2-input" placeholder="Your Email">
                    <textarea id="swal-msg" class="swal2-textarea" placeholder="Your Message" style="margin-top: 15px; width: 80%; height: 100px; padding: 10px; border-radius: 4px; border: 1px solid #d9d9d9;"></textarea>
                `,
                confirmButtonText: 'Send Message',
                confirmButtonColor: '#e63946',
                showCancelButton: true,
                preConfirm: () => {
                    const name = Swal.getPopup().querySelector('#swal-name').value;
                    const email = Swal.getPopup().querySelector('#swal-email').value;
                    const msg = Swal.getPopup().querySelector('#swal-msg').value;
                    if (!name || !email || !msg) {
                        Swal.showValidationMessage(`Please enter all fields`);
                    }
                    return { name: name, email: email, message: msg }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('name', result.value.name);
                    formData.append('email', result.value.email);
                    formData.append('message', result.value.message);
                    formData.append('donor', donorName);
                    
                    fetch('send-message.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Sent!', 'Your message has been sent securely.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to send message.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'An error occurred.', 'error');
                    });
                }
            });
        }));
    </script>
</body>
</html>

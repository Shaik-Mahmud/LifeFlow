<?php
session_start();
require_once 'includes/db.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dateOfBirth = $_POST['dateOfBirth'] ?? '';
    $bloodGroup = $_POST['bloodGroup'] ?? '';
    $weight = (int)($_POST['weight'] ?? 0);
    $lastDonation = !empty($_POST['lastDonation']) ? $_POST['lastDonation'] : null;
    $healthNotes = trim($_POST['healthNotes'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $available = isset($_POST['available']) ? 'Available' : 'Unavailable';
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($phone) || empty($email) || empty($password) || empty($dateOfBirth) || empty($bloodGroup) || empty($weight) || empty($city) || empty($area)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM donors WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO donors (full_name, phone, email, dob, blood_group, weight, last_donation, health_notes, city, area, availability_status, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([$fullName, $phone, $email, $dateOfBirth, $bloodGroup, $weight, $lastDonation, $healthNotes, $city, $area, $available, $hashedPassword]);
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'An error occurred during registration. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Register as a LifeFlow blood donor.">
    <title>Become a Donor | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/donor-register.css" rel="stylesheet">
</head>

<body class="donor-register-page">
    <nav class="navbar navbar-expand-lg lifeflow-nav donor-register-nav" aria-label="Primary navigation">
        <div class="container"><a class="navbar-brand" href="index.html"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span>LifeFlow</a><a class="btn btn-outline-danger btn-sm" href="donor/login.php">Already a donor? Sign in</a></div>
    </nav>

    <main>
        <section class="register-hero">
            <div class="container text-center">
                <span class="section-kicker"><i class="fa-solid fa-heart me-1"></i> Join the LifeFlow community</span>
                <h1>Become someone’s <span class="text-danger">lifeline.</span></h1>
                <p>Complete your donor profile today. It takes only a few minutes and could make a lasting difference.</p>
            </div>
        </section>

        <section class="register-content">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <aside class="col-lg-4">
                        <div class="register-aside">
                            <div class="aside-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
                            <h2>Every detail helps us make a safer match.</h2>
                            <p>Your information helps LifeFlow connect you with compatible requests near you.</p>
                            <ul class="register-benefits list-unstyled">
                                <li><i class="fa-solid fa-circle-check"></i> Respond only when you are available</li>
                                <li><i class="fa-solid fa-circle-check"></i> Keep your personal details protected</li>
                                <li><i class="fa-solid fa-circle-check"></i> Make a meaningful impact in your community</li>
                            </ul>
                        </div>
                    </aside>
                    <div class="col-lg-8">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">Registration successful! You are now a donor. <a href="donor/login.php" class="alert-link">Login here</a>.</div>
                        <?php endif; ?>
                        <form class="register-form needs-validation" method="POST" novalidate>
                            <div class="form-section"><div class="form-section-heading"><span>1</span><div><h2>About you</h2><p>Tell us the basics so we can create your donor profile.</p></div></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="fullName">Full name</label><input class="form-control" id="fullName" name="fullName" type="text" placeholder="Your full name" required><div class="invalid-feedback">Please enter your name.</div></div><div class="col-md-6"><label class="form-label" for="phone">Phone number</label><input class="form-control" id="phone" name="phone" type="tel" placeholder="01XXXXXXXXX" required><div class="invalid-feedback">Please enter your phone number.</div></div><div class="col-md-6"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" placeholder="you@example.com" required><div class="invalid-feedback">Please enter a valid email address.</div></div><div class="col-md-6"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" placeholder="Create a password" required><div class="invalid-feedback">Please enter a password.</div></div><div class="col-md-12"><label class="form-label" for="dateOfBirth">Date of birth</label><input class="form-control" id="dateOfBirth" name="dateOfBirth" type="date" required><div class="invalid-feedback">Please provide your date of birth.</div></div></div></div>

                            <div class="form-section"><div class="form-section-heading"><span>2</span><div><h2>Donation details</h2><p>This helps us find compatible requests for you.</p></div></div><div class="row g-3"><div class="col-md-4"><label class="form-label" for="bloodGroup">Blood group</label><select class="form-select" id="bloodGroup" name="bloodGroup" required><option value="" selected disabled>Select blood group</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option></select><div class="invalid-feedback">Please select your blood group.</div></div><div class="col-md-4"><label class="form-label" for="weight">Weight (kg)</label><input class="form-control" id="weight" name="weight" type="number" placeholder="e.g. 70" required><div class="invalid-feedback">Please enter your weight.</div></div><div class="col-md-4"><label class="form-label" for="lastDonation">Last donation date <span class="text-secondary fw-normal">(optional)</span></label><input class="form-control" id="lastDonation" name="lastDonation" type="date"></div><div class="col-12"><label class="form-label" for="healthNotes">Health notes <span class="text-secondary fw-normal">(optional)</span></label><textarea class="form-control" id="healthNotes" name="healthNotes" rows="3" placeholder="Share any information that may affect your donation eligibility."></textarea></div></div></div>

                            <div class="form-section"><div class="form-section-heading"><span>3</span><div><h2>Your location & availability</h2><p>We use this to notify you about relevant nearby requests.</p></div></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="city">City / district</label><input class="form-control" id="city" name="city" type="text" placeholder="e.g. Dhaka" required><div class="invalid-feedback">Please enter your city or district.</div></div><div class="col-md-6"><label class="form-label" for="area">Area</label><input class="form-control" id="area" name="area" type="text" placeholder="e.g. Dhanmondi" required><div class="invalid-feedback">Please enter your area.</div></div><div class="col-12"><div class="availability-box"><div><strong>Available to donate</strong><small>You can change this anytime from your donor account.</small></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="available" name="available" checked><label class="form-check-label" for="available">Available</label></div></div></div></div></div>

                            <div class="form-check agreement"><input class="form-check-input" type="checkbox" id="agreement" name="agreement" required><label class="form-check-label" for="agreement">I confirm that the information I provided is accurate and I agree to LifeFlow’s <a href="terms.html">terms</a> and <a href="privacy.html">privacy policy</a>.</label><div class="invalid-feedback">You must accept before creating an account.</div></div>
                            <button type="submit" class="btn btn-danger btn-lg register-submit">Create my donor account <i class="fa-solid fa-arrow-right ms-1"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="register-footer"><div class="container d-flex flex-column flex-sm-row justify-content-between gap-2"><span>© 2026 LifeFlow. All rights reserved.</span><span>Questions? <a href="contact.php">Contact our team</a></span></div></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

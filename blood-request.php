<?php
session_start();
require_once 'includes/db.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientName = trim($_POST['patientName'] ?? '');
    $bloodGroup = $_POST['bloodGroup'] ?? '';
    $units = $_POST['units'] ?? '';
    $urgency = $_POST['urgency'] ?? '';
    $hospital = trim($_POST['hospital'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contactName = trim($_POST['contactName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($patientName) || empty($bloodGroup) || empty($units) || empty($urgency) || empty($hospital) || empty($city) || empty($address) || empty($contactName) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO blood_requests (patient_name, blood_group, units, urgency, hospital, city, address, contact_name, phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$patientName, $bloodGroup, $units, $urgency, $hospital, $city, $address, $contactName, $phone, $notes]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'An error occurred: ' . $e->getMessage();
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
    <meta name="description" content="Create an emergency blood request with LifeFlow.">
    <title>Request Blood | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/blood-request.css" rel="stylesheet">
</head>

<body class="request-page">
    <nav class="navbar navbar-expand-lg lifeflow-nav request-nav" aria-label="Primary navigation">
        <div class="container"><a class="navbar-brand" href="index.html"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span>LifeFlow</a><a class="btn btn-outline-danger btn-sm" href="search-donor.php"><i class="fa-solid fa-magnifying-glass me-1"></i> Find donors</a></div>
    </nav>

    <main>
        <section class="request-hero">
            <div class="container text-center"><span class="section-kicker"><i class="fa-solid fa-triangle-exclamation me-1"></i> Emergency support</span><h1>Request blood when <span class="text-danger">every minute matters.</span></h1><p>Share the details below and we’ll help connect your request with compatible donors nearby.</p></div>
        </section>

        <section class="request-content">
            <div class="container"><div class="row g-4 align-items-start"><aside class="col-lg-4"><div class="request-aside"><span class="request-aside-icon"><i class="fa-solid fa-heart-pulse"></i></span><h2>You’re not alone in this.</h2><p>Once submitted, your request can be shared with available compatible donors in your area.</p><div class="request-timeline"><div><span>1</span><p><strong>Share the need</strong><small>Give us the required blood and hospital details.</small></p></div><div><span>2</span><p><strong>Reach nearby donors</strong><small>Compatible donors can receive the request.</small></p></div><div><span>3</span><p><strong>Track the response</strong><small>Stay updated as donors respond.</small></p></div></div></div><p class="emergency-note"><i class="fa-solid fa-phone-volume"></i> For a life-threatening emergency, please contact your local emergency services and hospital immediately.</p></aside>

                <div class="col-lg-8">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Your blood request has been successfully submitted and will be reviewed shortly.</div>
                    <?php endif; ?>
                    <form class="request-form needs-validation" method="POST" novalidate><div class="request-form-heading"><h2>Blood request details</h2><p>Fields marked with <span>*</span> are required.</p></div><div class="request-section"><div class="request-section-title"><span>1</span><h3>Patient & blood needed</h3></div><div class="row g-3"><div class="col-12"><label class="form-label" for="patientName">Patient name <em>*</em></label><input class="form-control" id="patientName" name="patientName" type="text" placeholder="Patient's name" required><div class="invalid-feedback">Please enter the patient name.</div></div><div class="col-md-6"><label class="form-label" for="bloodGroup">Blood group <em>*</em></label><select class="form-select" id="bloodGroup" name="bloodGroup" required><option value="" selected disabled>Select blood group</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option></select><div class="invalid-feedback">Please select a blood group.</div></div><div class="col-md-6"><label class="form-label" for="units">Units required <em>*</em></label><input class="form-control" id="units" name="units" type="number" min="1" placeholder="e.g. 2" required><div class="invalid-feedback">Please enter the number of units needed.</div></div><div class="col-12"><label class="form-label d-block">Urgency level <em>*</em></label><div class="urgency-options"><div><input type="radio" class="btn-check" name="urgency" id="urgent" value="Urgent" required><label for="urgent"><i class="fa-solid fa-bolt"></i><strong>Urgent</strong><small>Needed within 24 hours</small></label></div><div><input type="radio" class="btn-check" name="urgency" id="scheduled" value="Scheduled" required><label for="scheduled"><i class="fa-regular fa-calendar"></i><strong>Scheduled</strong><small>Needed in the coming days</small></label></div></div><div class="invalid-feedback">Please select the urgency level.</div></div></div></div>

                    <div class="request-section"><div class="request-section-title"><span>2</span><h3>Hospital & location</h3></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="hospital">Hospital name <em>*</em></label><input class="form-control" id="hospital" name="hospital" type="text" placeholder="Hospital or clinic name" required><div class="invalid-feedback">Please enter the hospital name.</div></div><div class="col-md-6"><label class="form-label" for="city">City / district <em>*</em></label><input class="form-control" id="city" name="city" type="text" placeholder="e.g. Dhaka" required><div class="invalid-feedback">Please enter the city or district.</div></div><div class="col-12"><label class="form-label" for="address">Hospital address <em>*</em></label><input class="form-control" id="address" name="address" type="text" placeholder="Area, road or landmark" required><div class="invalid-feedback">Please enter the hospital address.</div></div></div></div>

                    <div class="request-section"><div class="request-section-title"><span>3</span><h3>Contact person</h3></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="contactName">Full name <em>*</em></label><input class="form-control" id="contactName" name="contactName" type="text" placeholder="Contact person’s name" required><div class="invalid-feedback">Please enter the contact name.</div></div><div class="col-md-6"><label class="form-label" for="phone">Phone number <em>*</em></label><input class="form-control" id="phone" name="phone" type="tel" placeholder="01XXXXXXXXX" required><div class="invalid-feedback">Please enter the contact phone number.</div></div><div class="col-12"><label class="form-label" for="notes">Additional information <span class="text-secondary fw-normal">(optional)</span></label><textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Share any helpful information for donors."></textarea></div></div></div>

                    <div class="form-check request-consent"><input class="form-check-input" type="checkbox" id="consent" name="consent" required><label class="form-check-label" for="consent">I confirm that this request is accurate and I agree to share these details with potential compatible donors.</label><div class="invalid-feedback">You must confirm the request details before submitting.</div></div><button type="submit" class="btn btn-danger btn-lg request-submit"><i class="fa-solid fa-paper-plane me-1"></i> Submit blood request</button></form></div></div></div>
        </section>
    </main>

    <footer class="request-footer"><div class="container d-flex flex-column flex-sm-row justify-content-between gap-2"><span>© 2026 LifeFlow. All rights reserved.</span><span>Need help? <a href="contact.php">Contact our team</a></span></div></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
session_start();
require_once 'includes/db.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['topic'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $fullMessage = "Phone: $phone\n\n$message";
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $fullMessage]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'An error occurred while sending your message. Please try again.';
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
    <meta name="description" content="Contact the LifeFlow support team.">
    <title>Contact Us | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/contact.css" rel="stylesheet">
</head>

<body class="contact-page">
    <nav class="navbar navbar-expand-lg lifeflow-nav contact-nav" aria-label="Primary navigation">
        <div class="container"><a class="navbar-brand" href="index.html"><span class="brand-mark"><i
                        class="fa-solid fa-droplet"></i></span>LifeFlow</a>
            <div class="d-flex gap-2"><a class="btn btn-outline-danger btn-sm" href="search-donor.php">Find donors</a><a
                    class="btn btn-danger btn-sm" href="blood-request.php">Request blood</a></div>
        </div>
    </nav>

    <main>
        <section class="contact-hero">
            <div class="container text-center"><span class="section-kicker"><i class="fa-regular fa-message me-1"></i>
                    We’re here to help</span>
                <h1>Let’s start a <span class="text-danger">conversation.</span></h1>
                <p>Whether you need help with a request, your donor account, or LifeFlow, our team is ready to listen.
                </p>
            </div>
        </section>

        <section class="contact-content">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="contact-intro"><span class="contact-intro-icon"><i
                                    class="fa-solid fa-heart-pulse"></i></span>
                            <h2>Support when it matters most.</h2>
                            <p>Send us a message and we’ll get back to you as soon as possible. For urgent blood needs,
                                please submit a blood request directly.</p>
                            <div class="contact-methods"><a href="mailto:support@lifeflow.example"><span><i
                                            class="fa-regular fa-envelope"></i></span>
                                    <div><small>Email us</small><strong>support@lifeflow.example</strong></div>
                                </a><a href="tel:+8801700000000"><span><i class="fa-solid fa-phone"></i></span>
                                    <div><small>Call us</small><strong>+880 1700-000000</strong></div>
                                </a>
                                <div><span><i class="fa-regular fa-clock"></i></span>
                                    <div><small>Support hours</small><strong>Every day, 8:00 AM – 10:00 PM</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="urgent-contact"><i class="fa-solid fa-triangle-exclamation"></i>
                            <p><strong>Need blood urgently?</strong> Use our emergency request form to reach compatible
                                donors nearby.</p><a href="blood-request.php">Request now <i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">Your message has been sent successfully. We will get back to
                                you soon.</div>
                        <?php endif; ?>
                        <form class="contact-form needs-validation" method="POST" action="contact.php" novalidate>
                            <div class="contact-form-heading">
                                <h2>Send us a message</h2>
                                <p>We’ll respond as soon as we can.</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label" for="name">Your name</label><input
                                        class="form-control" id="name" name="name" type="text" placeholder="Full name"
                                        required>
                                    <div class="invalid-feedback">Please enter your name.</div>
                                </div>
                                <div class="col-md-6"><label class="form-label" for="email">Email address</label><input
                                        class="form-control" id="email" name="email" type="email"
                                        placeholder="you@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                <div class="col-md-6"><label class="form-label" for="phone">Phone number <span
                                            class="text-secondary fw-normal">(optional)</span></label><input
                                        class="form-control" id="phone" name="phone" type="tel"
                                        placeholder="01XXXXXXXXX"></div>
                                <div class="col-md-6"><label class="form-label" for="topic">How can we
                                        help?</label><select class="form-select" id="topic" name="topic" required>
                                        <option value="" selected disabled>Select a topic</option>
                                        <option value="Emergency blood request">Emergency blood request</option>
                                        <option value="Donor account">Donor account</option>
                                        <option value="Donation information">Donation information</option>
                                        <option value="Technical support">Technical support</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a topic.</div>
                                </div>
                                <div class="col-12"><label class="form-label" for="message">Your
                                        message</label><textarea class="form-control" id="message" name="message"
                                        rows="6" placeholder="Tell us how we can help…" required></textarea>
                                    <div class="invalid-feedback">Please enter your message.</div>
                                </div>
                            </div><button type="submit" class="btn btn-danger btn-lg contact-submit">Send message <i
                                    class="fa-solid fa-paper-plane ms-1"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="contact-footer">
        <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2"><span>© 2026 LifeFlow. All
                rights reserved.</span><span><a href="about.html">About LifeFlow</a> · <a
                    href="index.html#faq">FAQs</a></span></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
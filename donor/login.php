<?php
session_start();
require_once '../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['donor_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name, email, password FROM donors WHERE email = ?");
            $stmt->execute([$email]);
            $donor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($donor && password_verify($password, $donor['password'])) {
                $_SESSION['donor_id'] = $donor['id'];
                $_SESSION['donor_name'] = $donor['full_name'];
                $_SESSION['donor_email'] = $donor['email'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred during login. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/icons/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sign in to your LifeFlow donor account.">
    <title>Donor Login | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/donor-login.css" rel="stylesheet">
</head>

<body class="login-page">
    <main class="login-layout">
        <section class="login-intro">
            <a class="login-brand" href="../index.html"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span>LifeFlow</a>
            <div class="intro-content">
                <span class="login-kicker"><i class="fa-solid fa-heart-pulse me-1"></i> Welcome back, hero</span>
                <h1>Your willingness to give can be someone’s <span>lifeline.</span></h1>
                <p>Sign in to manage your donor profile, update availability and respond to nearby blood requests.</p>
                <div class="intro-points">
                    <div><i class="fa-solid fa-circle-check"></i><span>Keep your availability up to date</span></div>
                    <div><i class="fa-solid fa-circle-check"></i><span>Receive compatible donation requests</span></div>
                    <div><i class="fa-solid fa-circle-check"></i><span>Track your life-saving impact</span></div>
                </div>
            </div>
            <p class="intro-footer">© 2026 LifeFlow. Made for every act of kindness.</p>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <a class="mobile-brand" href="../index.html"><span class="brand-mark"><i class="fa-solid fa-droplet"></i></span>LifeFlow</a>
                <div class="login-heading">
                    <span class="login-icon"><i class="fa-solid fa-user-heart"></i></span>
                    <h2>Donor login</h2>
                    <p>Enter your details to access your account.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form class="needs-validation" method="POST" action="login.php" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group"><span class="input-group-text"><i class="fa-regular fa-envelope"></i></span><input type="email" name="email" class="form-control" id="email" placeholder="you@example.com" required></div>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center"><label for="password" class="form-label">Password</label><a href="../forgot-password.html" class="forgot-link">Forgot password?</a></div>
                        <div class="input-group"><span class="input-group-text"><i class="fa-solid fa-lock"></i></span><input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required><button class="btn password-toggle" type="button" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <div class="form-check mb-4"><input class="form-check-input" type="checkbox" value="" id="remember"><label class="form-check-label" for="remember">Remember me on this device</label></div>
                    <button type="submit" class="btn btn-danger login-submit w-100">Sign in to LifeFlow <i class="fa-solid fa-arrow-right ms-1"></i></button>
                </form>

                <div class="login-divider"><span>New to LifeFlow?</span></div>
                <a href="../donor-register.php" class="btn btn-outline-danger w-100 register-button">Create a donor account</a>
                <p class="login-help">Need help? <a href="../contact.php">Contact our support team</a></p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.querySelector('.password-toggle').addEventListener('click', function () {
            const password = document.querySelector('#password');
            const isPassword = password.type === 'password';
            password.type = isPassword ? 'text' : 'password';
            this.innerHTML = `<i class="fa-regular fa-eye${isPassword ? '-slash' : ''}"></i>`;
            this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    </script>
</body>
</html>
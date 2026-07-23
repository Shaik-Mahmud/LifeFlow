<?php
session_start();
require_once '../includes/db.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred during login.';
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
    <meta name="description" content="LifeFlow administrator login.">
    <title>Admin Login | LifeFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/admin-login.css" rel="stylesheet">
</head>

<body class="admin-login-page">
    <main class="admin-login-layout">
        <section class="admin-login-intro">
            <a class="admin-brand" href="../index.html"><span class="brand-mark"><i
                        class="fa-solid fa-droplet"></i></span>LifeFlow</a>
            <div class="admin-intro-content"><span class="admin-kicker"><i class="fa-solid fa-shield-halved me-1"></i>
                    Secure administration</span>
                <h1>Keep every response <span>moving forward.</span></h1>
                <p>Manage donor activity, blood requests and platform coordination from one secure place.</p>
                <div class="admin-intro-points">
                    <div><i class="fa-solid fa-circle-check"></i><span>Review and coordinate blood requests</span></div>
                    <div><i class="fa-solid fa-circle-check"></i><span>Support and verify donor profiles</span></div>
                    <div><i class="fa-solid fa-circle-check"></i><span>Monitor community response activity</span></div>
                </div>
            </div>
            <p class="admin-intro-footer">© 2026 LifeFlow. Administration portal.</p>
        </section>

        <section class="admin-login-panel">
            <div class="admin-login-card"><a class="admin-mobile-brand" href="../index.html"><span class="brand-mark"><i
                            class="fa-solid fa-droplet"></i></span>LifeFlow</a>
                <div class="admin-login-heading"><span class="admin-login-icon"><i
                            class="fa-solid fa-user-shield"></i></span>
                    <h2>Admin login</h2>
                    <p>Sign in to access the LifeFlow administration portal.</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form class="needs-validation" method="POST" action="login.php" novalidate>
                    <div class="mb-3"><label for="username" class="form-label">Admin username</label>
                        <div class="input-group"><span class="input-group-text"><i
                                    class="fa-regular fa-user"></i></span><input type="text" class="form-control"
                                name="username" id="username" placeholder="admin" required></div>
                        <div class="invalid-feedback">Please enter a valid admin username.</div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center"><label for="password"
                                class="form-label">Password</label></div>
                        <div class="input-group"><span class="input-group-text"><i
                                    class="fa-solid fa-lock"></i></span><input type="password" class="form-control"
                                name="password" id="password" placeholder="Enter your password" required><button
                                class="btn admin-password-toggle" type="button" aria-label="Show password"><i
                                    class="fa-regular fa-eye"></i></button></div>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <div class="form-check mb-4"><input class="form-check-input" type="checkbox" value=""
                            id="remember"><label class="form-check-label" for="remember" href="index.php">Remember this
                            device</label>
                    </div><button type="submit" class="btn btn-danger admin-login-submit w-100">Sign in
                        securely <i class="fa-solid fa-arrow-right ms-1"></i></button>
                </form>
                <div class="admin-security-note"><i class="fa-solid fa-lock"></i><span>This area is restricted to
                        authorised LifeFlow administrators.</span></div>
                <p class="admin-login-help">Not an administrator? <a href="../index.html">Return to LifeFlow</a></p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.querySelector('.admin-password-toggle').addEventListener('click', function () {
            const password = document.querySelector('#password');
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            this.innerHTML = `<i class="fa-regular fa-eye${isHidden ? '-slash' : ''}"></i>`;
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    </script>
</body>

</html>
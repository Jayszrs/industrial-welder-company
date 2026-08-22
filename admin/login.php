<?php
require_once __DIR__ . '/includes/auth.php';

if (admin_is_logged_in()) {
    redirect(admin_url('index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid session, please try again.';
    } else {
        $username = clean($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } elseif (admin_attempt_login($username, $password)) {
            redirect(admin_url('index.php'));
        } else {
            $error = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Yamato Welding Industries</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('assets/css/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-login-wrap">
    <div class="admin-login-box">
        <div class="admin-login-brand">
            <div class="mark">YAMATO<span>.</span></div>
            <div class="sub">Admin Panel</div>
        </div>

        <?php if ($error): ?>
            <div class="admin-login-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(admin_url('login.php')) ?>">
            <?= csrf_field() ?>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>

            <button type="submit">Log In</button>
        </form>

        <div class="admin-login-hint">
            Demo login — username: <strong>admin</strong> / password: <strong>admin123</strong><br>
            Change this immediately after import.
        </div>
    </div>
</div>
</body>
</html>

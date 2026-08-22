<?php
/**
 * Admin header include.
 * Expects: $adminTitle (string), $adminActive (string key), admin_require_login() already called.
 */
$adminTitle  = $adminTitle ?? 'Dashboard';
$adminActive = $adminActive ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($adminTitle) ?> — Admin | Yamato Welding Industries</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <div class="mark">YAMATO<span>.</span></div>
            <div class="sub">Admin Panel</div>
        </div>
        <nav class="admin-nav">
            <a href="<?= e(admin_url('index.php')) ?>" class="<?= $adminActive === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>

            <div class="admin-nav-section">Content</div>
            <a href="<?= e(admin_url('homepage.php')) ?>" class="<?= $adminActive === 'homepage' ? 'is-active' : '' ?>">Homepage</a>
            <a href="<?= e(admin_url('company-profile.php')) ?>" class="<?= $adminActive === 'company-profile' ? 'is-active' : '' ?>">Company Profile</a>

            <div class="admin-nav-section">Catalog</div>
            <a href="<?= e(admin_url('services.php')) ?>" class="<?= $adminActive === 'services' ? 'is-active' : '' ?>">Services</a>
            <a href="<?= e(admin_url('technologies.php')) ?>" class="<?= $adminActive === 'technologies' ? 'is-active' : '' ?>">Welding Technology</a>
            <a href="<?= e(admin_url('products.php')) ?>" class="<?= $adminActive === 'products' ? 'is-active' : '' ?>">Products</a>
            <a href="<?= e(admin_url('facilities.php')) ?>" class="<?= $adminActive === 'facilities' ? 'is-active' : '' ?>">Facilities</a>
            <a href="<?= e(admin_url('projects.php')) ?>" class="<?= $adminActive === 'projects' ? 'is-active' : '' ?>">Projects</a>
            <a href="<?= e(admin_url('news.php')) ?>" class="<?= $adminActive === 'news' ? 'is-active' : '' ?>">News</a>
            <a href="<?= e(admin_url('industries.php')) ?>" class="<?= $adminActive === 'industries' ? 'is-active' : '' ?>">Industries</a>
            <a href="<?= e(admin_url('stats.php')) ?>" class="<?= $adminActive === 'stats' ? 'is-active' : '' ?>">Strength / Stats</a>

            <div class="admin-nav-section">Inbox</div>
            <a href="<?= e(admin_url('inquiries.php')) ?>" class="<?= $adminActive === 'inquiries' ? 'is-active' : '' ?>">Contact Messages</a>

            <div class="admin-nav-section">System</div>
            <a href="<?= e(admin_url('settings.php')) ?>" class="<?= $adminActive === 'settings' ? 'is-active' : '' ?>">Site Settings</a>
            <a href="<?= e(admin_url('logout.php')) ?>">Logout</a>
        </nav>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <h1><?= e($adminTitle) ?></h1>
            <div class="admin-topbar-user">
                <span>Hi, <?= e($_SESSION['admin_name'] ?: $_SESSION['admin_username']) ?></span>
                <a href="<?= e(base_url('index.php')) ?>" target="_blank" class="admin-view-site">View Site &#8599;</a>
            </div>
        </div>
        <div class="admin-content">

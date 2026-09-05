<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

$counts = [
    'products'    => (int) $pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'],
    'services'    => (int) $pdo->query("SELECT COUNT(*) c FROM services")->fetch()['c'],
    'facilities'  => (int) $pdo->query("SELECT COUNT(*) c FROM facilities")->fetch()['c'],
    'projects'    => (int) $pdo->query("SELECT COUNT(*) c FROM projects")->fetch()['c'],
    'inquiries'   => (int) $pdo->query("SELECT COUNT(*) c FROM inquiries")->fetch()['c'],
    'unread'      => (int) $pdo->query("SELECT COUNT(*) c FROM inquiries WHERE is_read = 0")->fetch()['c'],
];

$recentInquiries = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();

$adminTitle  = 'Dashboard';
$adminActive = 'dashboard';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="stat-cards">
    <div class="stat-card"><div class="num"><?= $counts['products'] ?></div><div class="label">Total Products</div></div>
    <div class="stat-card"><div class="num"><?= $counts['services'] ?></div><div class="label">Total Services</div></div>
    <div class="stat-card"><div class="num"><?= $counts['facilities'] ?></div><div class="label">Total Facilities</div></div>
    <div class="stat-card"><div class="num"><?= $counts['projects'] ?></div><div class="label">Total Projects</div></div>
    <div class="stat-card" style="border-top-color:#697078;"><div class="num"><?= $counts['unread'] ?></div><div class="label">New Inquiries</div></div>
</div>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Recent Inquiries</h2>
        <a href="<?= e(admin_url('inquiries.php')) ?>" class="abtn abtn--outline abtn--sm">View All</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th></tr></thead>
            <tbody>
                <?php if (empty($recentInquiries)): ?>
                    <tr><td colspan="5" class="empty-row">No inquiries yet.</td></tr>
                <?php else: foreach ($recentInquiries as $inq): ?>
                    <tr>
                        <td><?= e(date('Y-m-d H:i', strtotime($inq['created_at']))) ?></td>
                        <td><?= e($inq['name']) ?></td>
                        <td><?= e($inq['email']) ?></td>
                        <td><?= e($inq['inquiry_type']) ?></td>
                        <td><span class="status-badge <?= $inq['is_read'] ? 'off' : 'on' ?>"><?= $inq['is_read'] ? 'Read' : 'New' ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

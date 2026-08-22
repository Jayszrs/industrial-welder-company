<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM inquiries WHERE id = :id")->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        set_flash('success', 'Inquiry deleted.');
    }
    redirect(admin_url('inquiries.php'));
}

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$viewRow = null;

if ($viewId) {
    $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = :id");
    $stmt->execute([':id' => $viewId]);
    $viewRow = $stmt->fetch();

    if ($viewRow && !$viewRow['is_read']) {
        $pdo->prepare("UPDATE inquiries SET is_read = 1 WHERE id = :id")->execute([':id' => $viewId]);
        $viewRow['is_read'] = 1;
    }
}

$inquiries = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Contact Messages';
$adminActive = 'inquiries';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<?php if ($viewRow): ?>
<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Inquiry from <?= e($viewRow['name']) ?></h2>
        <a href="<?= e(admin_url('inquiries.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
    </div>
    <div class="admin-form">
        <table class="spec-table" style="max-width:640px; margin-bottom:28px;">
            <tr><th>Date</th><td><?= e(date('Y-m-d H:i', strtotime($viewRow['created_at']))) ?></td></tr>
            <tr><th>Inquiry Type</th><td><?= e($viewRow['inquiry_type']) ?></td></tr>
            <tr><th>Company</th><td><?= e($viewRow['company_name']) ?: '—' ?></td></tr>
            <tr><th>Name</th><td><?= e($viewRow['name']) ?></td></tr>
            <tr><th>Email</th><td><?= e($viewRow['email']) ?></td></tr>
            <tr><th>Phone</th><td><?= e($viewRow['phone']) ?: '—' ?></td></tr>
            <tr><th>Subject</th><td><?= e($viewRow['subject']) ?: '—' ?></td></tr>
        </table>
        <div class="aform-label">Message</div>
        <div style="white-space:pre-wrap; background:var(--c-off-white); padding:20px; border:1px solid var(--c-light-gray); margin-top:8px; line-height:1.8;"><?= e($viewRow['message']) ?></div>

        <div class="aform-actions">
            <a href="mailto:<?= e($viewRow['email']) ?>" class="abtn abtn--primary">Reply by Email</a>
            <form method="post" class="confirm-delete" data-confirm="Delete this inquiry?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e($viewRow['id']) ?>">
                <button type="submit" class="abtn abtn--danger">Delete</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>All Messages (<?= count($inquiries) ?>)</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="6" class="empty-row">No inquiries yet.</td></tr>
                <?php else: foreach ($inquiries as $inq): ?>
                    <tr>
                        <td><?= e(date('Y-m-d H:i', strtotime($inq['created_at']))) ?></td>
                        <td><?= e($inq['name']) ?></td>
                        <td><?= e($inq['email']) ?></td>
                        <td><?= e($inq['inquiry_type']) ?></td>
                        <td><span class="status-badge <?= $inq['is_read'] ? 'off' : 'on' ?>"><?= $inq['is_read'] ? 'Read' : 'New' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('inquiries.php?view=' . $inq['id'])) ?>" class="abtn abtn--outline abtn--sm">View</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this inquiry?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($inq['id']) ?>">
                                <button type="submit" class="abtn abtn--danger abtn--sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

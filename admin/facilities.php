<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM facilities WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_uploaded_image($row['image'], 'facilities');
            $pdo->prepare("DELETE FROM facilities WHERE id = :id")->execute([':id' => $id]);
            set_flash('success', 'Facility deleted.');
        }
    }
    redirect(admin_url('facilities.php'));
}

$facilities = $pdo->query("SELECT * FROM facilities ORDER BY sort_order ASC, id ASC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Facilities';
$adminActive = 'facilities';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Facilities (<?= count($facilities) ?>)</h2>
        <a href="<?= e(admin_url('facility-form.php')) ?>" class="abtn abtn--accent">+ Add Facility</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Machine (EN)</th><th>Manufacturer</th><th>Model</th><th>Qty</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($facilities)): ?>
                    <tr><td colspan="7" class="empty-row">No facilities yet.</td></tr>
                <?php else: foreach ($facilities as $f): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(image_url($f['image'], 'facilities')) ?>" alt=""></td>
                        <td><?= e($f['machine_name_en']) ?></td>
                        <td><?= e($f['manufacturer']) ?></td>
                        <td><?= e($f['model']) ?></td>
                        <td><?= e($f['quantity']) ?></td>
                        <td><span class="status-badge <?= $f['status'] ? 'on' : 'off' ?>"><?= $f['status'] ? 'Active' : 'Hidden' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('facility-form.php?id=' . $f['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this facility?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($f['id']) ?>">
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

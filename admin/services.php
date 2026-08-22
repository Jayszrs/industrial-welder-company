<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM services WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_uploaded_image($row['image'], 'services');
            $pdo->prepare("DELETE FROM services WHERE id = :id")->execute([':id' => $id]);
            set_flash('success', 'Service deleted.');
        }
    }
    redirect(admin_url('services.php'));
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Services';
$adminActive = 'services';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Services (<?= count($services) ?>)</h2>
        <a href="<?= e(admin_url('service-form.php')) ?>" class="abtn abtn--accent">+ Add Service</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Order</th><th>Title (JA)</th><th>Title (EN)</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="6" class="empty-row">No services yet. Click "Add Service" to create one.</td></tr>
                <?php else: foreach ($services as $s): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(image_url($s['image'], 'services')) ?>" alt=""></td>
                        <td><?= e($s['sort_order']) ?></td>
                        <td><?= e($s['title_ja']) ?></td>
                        <td><?= e($s['title_en']) ?></td>
                        <td><span class="status-badge <?= $s['status'] ? 'on' : 'off' ?>"><?= $s['status'] ? 'Active' : 'Hidden' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('service-form.php?id=' . $s['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this service?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
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

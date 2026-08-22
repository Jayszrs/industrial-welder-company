<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM technologies WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_uploaded_image($row['image'], 'technologies');
            $pdo->prepare("DELETE FROM technologies WHERE id = :id")->execute([':id' => $id]);
            set_flash('success', 'Technology deleted.');
        }
    }
    redirect(admin_url('technologies.php'));
}

$technologies = $pdo->query("SELECT * FROM technologies ORDER BY sort_order ASC, id ASC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Welding Technology';
$adminActive = 'technologies';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Welding Technology (<?= count($technologies) ?>)</h2>
        <a href="<?= e(admin_url('technology-form.php')) ?>" class="abtn abtn--accent">+ Add Technology</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Order</th><th>Name (JA)</th><th>Name (EN)</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($technologies)): ?>
                    <tr><td colspan="6" class="empty-row">No technologies yet.</td></tr>
                <?php else: foreach ($technologies as $t): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(image_url($t['image'], 'technologies')) ?>" alt=""></td>
                        <td><?= e($t['sort_order']) ?></td>
                        <td><?= e($t['name_ja']) ?></td>
                        <td><?= e($t['name_en']) ?></td>
                        <td><span class="status-badge <?= $t['status'] ? 'on' : 'off' ?>"><?= $t['status'] ? 'Active' : 'Hidden' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('technology-form.php?id=' . $t['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this technology?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($t['id']) ?>">
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

<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM stats WHERE id = :id")->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        set_flash('success', 'Stat deleted.');
    }
    redirect(admin_url('stats.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
        redirect(admin_url('stats.php'));
    }
    $id     = (int) ($_POST['id'] ?? 0);
    $number = clean($_POST['number_value'] ?? '');
    $labJa  = clean($_POST['label_ja'] ?? '');
    $labEn  = clean($_POST['label_en'] ?? '');
    $sort   = (int) ($_POST['sort_order'] ?? 0);

    if ($number === '' || $labJa === '' || $labEn === '') {
        set_flash('error', 'Number, label (Japanese), and label (English) are all required.');
        redirect(admin_url('stats.php'));
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE stats SET number_value=:n, label_ja=:ja, label_en=:en, sort_order=:so WHERE id=:id");
        $stmt->execute([':n' => $number, ':ja' => $labJa, ':en' => $labEn, ':so' => $sort, ':id' => $id]);
        set_flash('success', 'Stat updated.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO stats (number_value, label_ja, label_en, sort_order) VALUES (:n, :ja, :en, :so)");
        $stmt->execute([':n' => $number, ':ja' => $labJa, ':en' => $labEn, ':so' => $sort]);
        set_flash('success', 'Stat created.');
    }
    redirect(admin_url('stats.php'));
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM stats WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $editRow = $stmt->fetch();
}

$stats = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC, id ASC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Strength / Stats';
$adminActive = 'stats';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head"><h2><?= $editRow ? 'Edit Stat' : 'Add Stat' ?></h2></div>
    <form method="post" class="admin-form" style="padding-top:20px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">

        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Number<span class="req">*</span></label>
                <input type="text" name="number_value" class="aform-control" value="<?= e($editRow['number_value'] ?? '') ?>" placeholder="e.g. 25+" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Label (Japanese)<span class="req">*</span></label>
                <input type="text" name="label_ja" class="aform-control" value="<?= e($editRow['label_ja'] ?? '') ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Label (English)<span class="req">*</span></label>
                <input type="text" name="label_en" class="aform-control" value="<?= e($editRow['label_en'] ?? '') ?>" required>
            </div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Sort Order</label>
            <input type="number" name="sort_order" class="aform-control" value="<?= e((string)($editRow['sort_order'] ?? 0)) ?>">
        </div>
        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary"><?= $editRow ? 'Update Stat' : 'Add Stat' ?></button>
            <?php if ($editRow): ?><a href="<?= e(admin_url('stats.php')) ?>" class="abtn abtn--outline">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>All Stats (<?= count($stats) ?>)</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Order</th><th>Number</th><th>Label (JA)</th><th>Label (EN)</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($stats)): ?>
                    <tr><td colspan="5" class="empty-row">No stats yet.</td></tr>
                <?php else: foreach ($stats as $s): ?>
                    <tr>
                        <td><?= e($s['sort_order']) ?></td>
                        <td><?= e($s['number_value']) ?></td>
                        <td><?= e($s['label_ja']) ?></td>
                        <td><?= e($s['label_en']) ?></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('stats.php?edit=' . $s['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this stat?">
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

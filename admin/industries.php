<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM industries WHERE id = :id")->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        set_flash('success', 'Industry deleted.');
    }
    redirect(admin_url('industries.php'));
}

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
        redirect(admin_url('industries.php'));
    }
    $id         = (int) ($_POST['id'] ?? 0);
    $nameJa     = clean($_POST['name_ja'] ?? '');
    $nameEn     = clean($_POST['name_en'] ?? '');
    $iconLabel  = clean($_POST['icon_label'] ?? '');
    $sortOrder  = (int) ($_POST['sort_order'] ?? 0);

    if ($nameJa === '' || $nameEn === '') {
        set_flash('error', 'Name (Japanese) and Name (English) are required.');
        redirect(admin_url('industries.php'));
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE industries SET name_ja=:ja, name_en=:en, icon_label=:icon, sort_order=:so WHERE id=:id");
        $stmt->execute([':ja' => $nameJa, ':en' => $nameEn, ':icon' => $iconLabel, ':so' => $sortOrder, ':id' => $id]);
        set_flash('success', 'Industry updated.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO industries (name_ja, name_en, icon_label, sort_order) VALUES (:ja, :en, :icon, :so)");
        $stmt->execute([':ja' => $nameJa, ':en' => $nameEn, ':icon' => $iconLabel, ':so' => $sortOrder]);
        set_flash('success', 'Industry created.');
    }
    redirect(admin_url('industries.php'));
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM industries WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $editRow = $stmt->fetch();
}

$industries = $pdo->query("SELECT * FROM industries ORDER BY sort_order ASC, id ASC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Industries';
$adminActive = 'industries';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head"><h2><?= $editRow ? 'Edit Industry' : 'Add Industry' ?></h2></div>
    <form method="post" class="admin-form" style="padding-top:20px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">

        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Name (Japanese)<span class="req">*</span></label>
                <input type="text" name="name_ja" class="aform-control" value="<?= e($editRow['name_ja'] ?? '') ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Name (English)<span class="req">*</span></label>
                <input type="text" name="name_en" class="aform-control" value="<?= e($editRow['name_en'] ?? '') ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Sort Order</label>
                <input type="number" name="sort_order" class="aform-control" value="<?= e((string)($editRow['sort_order'] ?? 0)) ?>">
            </div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Icon Label</label>
            <input type="text" name="icon_label" class="aform-control" value="<?= e($editRow['icon_label'] ?? '') ?>" maxlength="10">
            <div class="aform-hint">Short label shown in the icon box, e.g. "01" or "AUTO".</div>
        </div>
        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary"><?= $editRow ? 'Update Industry' : 'Add Industry' ?></button>
            <?php if ($editRow): ?><a href="<?= e(admin_url('industries.php')) ?>" class="abtn abtn--outline">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>All Industries (<?= count($industries) ?>)</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Order</th><th>Icon</th><th>Name (JA)</th><th>Name (EN)</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($industries)): ?>
                    <tr><td colspan="5" class="empty-row">No industries yet.</td></tr>
                <?php else: foreach ($industries as $ind): ?>
                    <tr>
                        <td><?= e($ind['sort_order']) ?></td>
                        <td><?= e($ind['icon_label']) ?></td>
                        <td><?= e($ind['name_ja']) ?></td>
                        <td><?= e($ind['name_en']) ?></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('industries.php?edit=' . $ind['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this industry?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($ind['id']) ?>">
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

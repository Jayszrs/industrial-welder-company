<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$tech = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM technologies WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $tech = $stmt->fetch();
    if (!$tech) { redirect(admin_url('technologies.php')); }
}

$errors = [];
$old = $tech ?: [
    'sort_order' => 0, 'name_ja' => '', 'name_en' => '', 'description_ja' => '',
    'description_en' => '', 'status' => 1, 'image' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        $old['sort_order']      = (int) ($_POST['sort_order'] ?? 0);
        $old['name_ja']         = clean($_POST['name_ja'] ?? '');
        $old['name_en']         = clean($_POST['name_en'] ?? '');
        $old['description_ja']  = clean($_POST['description_ja'] ?? '');
        $old['description_en']  = clean($_POST['description_en'] ?? '');
        $old['status']          = isset($_POST['status']) ? 1 : 0;

        if ($old['name_ja'] === '' || $old['name_en'] === '') {
            $errors[] = 'Name (Japanese) and Name (English) are both required.';
        }

        $newImage = null;
        try {
            $newImage = handle_image_upload('image', 'technologies');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $imageToSave = $newImage ?? $old['image'];

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE technologies SET sort_order=:sort_order, name_ja=:name_ja, name_en=:name_en,
                     description_ja=:description_ja, description_en=:description_en, image=:image, status=:status
                     WHERE id=:id"
                );
                $stmt->execute([
                    ':sort_order' => $old['sort_order'], ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':image' => $imageToSave, ':status' => $old['status'], ':id' => $id,
                ]);
                if ($newImage) { delete_uploaded_image($tech['image'] ?? null, 'technologies'); }
                set_flash('success', 'Technology updated.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO technologies (sort_order, name_ja, name_en, description_ja, description_en, image, status)
                     VALUES (:sort_order, :name_ja, :name_en, :description_ja, :description_en, :image, :status)"
                );
                $stmt->execute([
                    ':sort_order' => $old['sort_order'], ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':image' => $imageToSave, ':status' => $old['status'],
                ]);
                set_flash('success', 'Technology created.');
            }
            redirect(admin_url('technologies.php'));
        }
    }
}

$adminTitle  = $id ? 'Edit Technology' : 'Add Technology';
$adminActive = 'technologies';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2><?= e($adminTitle) ?></h2>
        <a href="<?= e(admin_url('technologies.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="padding:20px 28px 0;">
        <div class="admin-alert admin-alert--error"><?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Sort Order</label>
                <input type="number" name="sort_order" class="aform-control" value="<?= e((string)$old['sort_order']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Status</label>
                <select name="status" class="aform-control">
                    <option value="1" <?= $old['status'] ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !$old['status'] ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Name (Japanese)<span class="req">*</span></label>
                <input type="text" name="name_ja" class="aform-control" value="<?= e($old['name_ja']) ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Name (English)<span class="req">*</span></label>
                <input type="text" name="name_en" class="aform-control" value="<?= e($old['name_en']) ?>" required>
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Description (Japanese)</label>
                <textarea name="description_ja" class="aform-control"><?= e($old['description_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Description (English)</label>
                <textarea name="description_en" class="aform-control"><?= e($old['description_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-row">
            <label class="aform-label">Image</label>
            <?php if (!empty($old['image'])): ?>
            <div class="aform-current-image">
                <img src="<?= e(image_url($old['image'], 'technologies')) ?>" alt="">
                <span class="aform-hint">Current image. Upload a new file below to replace it.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">JPG, PNG, or WEBP. Max 5MB.</div>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save Technology</button>
            <a href="<?= e(admin_url('technologies.php')) ?>" class="abtn abtn--outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

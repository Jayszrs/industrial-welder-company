<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$service = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $service = $stmt->fetch();
    if (!$service) { redirect(admin_url('services.php')); }
}

$errors = [];
$old = $service ?: [
    'sort_order' => 0, 'title_ja' => '', 'title_en' => '', 'description_ja' => '',
    'description_en' => '', 'status' => 1, 'image' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        $old['sort_order']      = (int) ($_POST['sort_order'] ?? 0);
        $old['title_ja']        = clean($_POST['title_ja'] ?? '');
        $old['title_en']        = clean($_POST['title_en'] ?? '');
        $old['description_ja']  = clean($_POST['description_ja'] ?? '');
        $old['description_en']  = clean($_POST['description_en'] ?? '');
        $old['status']          = isset($_POST['status']) ? 1 : 0;

        if ($old['title_ja'] === '' || $old['title_en'] === '') {
            $errors[] = 'Title (Japanese) and Title (English) are both required.';
        }

        $newImage = null;
        try {
            $newImage = handle_image_upload('image', 'services');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $imageToSave = $newImage ?? $old['image'];

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE services SET sort_order=:sort_order, title_ja=:title_ja, title_en=:title_en,
                     description_ja=:description_ja, description_en=:description_en, image=:image, status=:status
                     WHERE id=:id"
                );
                $stmt->execute([
                    ':sort_order' => $old['sort_order'], ':title_ja' => $old['title_ja'], ':title_en' => $old['title_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':image' => $imageToSave, ':status' => $old['status'], ':id' => $id,
                ]);
                if ($newImage) { delete_uploaded_image($service['image'] ?? null, 'services'); }
                set_flash('success', 'Service updated.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO services (sort_order, title_ja, title_en, description_ja, description_en, image, status)
                     VALUES (:sort_order, :title_ja, :title_en, :description_ja, :description_en, :image, :status)"
                );
                $stmt->execute([
                    ':sort_order' => $old['sort_order'], ':title_ja' => $old['title_ja'], ':title_en' => $old['title_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':image' => $imageToSave, ':status' => $old['status'],
                ]);
                set_flash('success', 'Service created.');
            }
            redirect(admin_url('services.php'));
        }
    }
}

$adminTitle  = $id ? 'Edit Service' : 'Add Service';
$adminActive = 'services';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2><?= e($adminTitle) ?></h2>
        <a href="<?= e(admin_url('services.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
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
                <label class="aform-label">Title (Japanese)<span class="req">*</span></label>
                <input type="text" name="title_ja" class="aform-control" value="<?= e($old['title_ja']) ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Title (English)<span class="req">*</span></label>
                <input type="text" name="title_en" class="aform-control" value="<?= e($old['title_en']) ?>" required>
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
                <img src="<?= e(image_url($old['image'], 'services')) ?>" alt="">
                <span class="aform-hint">Current image. Upload a new file below to replace it.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">JPG, PNG, or WEBP. Max 5MB.</div>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save Service</button>
            <a href="<?= e(admin_url('services.php')) ?>" class="abtn abtn--outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

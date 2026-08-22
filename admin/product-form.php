<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    if (!$product) { redirect(admin_url('products.php')); }
}

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY id ASC")->fetchAll();

$errors = [];
$old = $product ?: [
    'category_id' => '', 'name_ja' => '', 'name_en' => '', 'model' => '', 'manufacturer' => '',
    'short_description_ja' => '', 'short_description_en' => '', 'description_ja' => '', 'description_en' => '',
    'features_ja' => '', 'features_en' => '', 'application_ja' => '', 'application_en' => '',
    'spec_power' => '', 'spec_output' => '', 'spec_current_range' => '', 'spec_dimensions' => '', 'spec_weight' => '',
    'status' => 1, 'image' => null, 'slug' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        foreach (['category_id','name_ja','name_en','model','manufacturer','short_description_ja','short_description_en',
                  'description_ja','description_en','features_ja','features_en','application_ja','application_en',
                  'spec_power','spec_output','spec_current_range','spec_dimensions','spec_weight'] as $field) {
            $old[$field] = clean($_POST[$field] ?? '');
        }
        $old['category_id'] = $old['category_id'] !== '' ? (int) $old['category_id'] : null;
        $old['status'] = isset($_POST['status']) ? 1 : 0;

        if ($old['name_ja'] === '' || $old['name_en'] === '') {
            $errors[] = 'Name (Japanese) and Name (English) are both required.';
        }

        $newImage = null;
        try {
            $newImage = handle_image_upload('image', 'products');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $imageToSave = $newImage ?? $old['image'];
            $slug = unique_slug($pdo, 'products', $old['name_en'], $id ?: null);

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE products SET category_id=:category_id, name_ja=:name_ja, name_en=:name_en, model=:model,
                     manufacturer=:manufacturer, short_description_ja=:short_description_ja, short_description_en=:short_description_en,
                     description_ja=:description_ja, description_en=:description_en, features_ja=:features_ja, features_en=:features_en,
                     application_ja=:application_ja, application_en=:application_en, spec_power=:spec_power, spec_output=:spec_output,
                     spec_current_range=:spec_current_range, spec_dimensions=:spec_dimensions, spec_weight=:spec_weight,
                     image=:image, status=:status, slug=:slug WHERE id=:id"
                );
                $stmt->execute([
                    ':category_id' => $old['category_id'], ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'],
                    ':model' => $old['model'], ':manufacturer' => $old['manufacturer'],
                    ':short_description_ja' => $old['short_description_ja'], ':short_description_en' => $old['short_description_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':features_ja' => $old['features_ja'], ':features_en' => $old['features_en'],
                    ':application_ja' => $old['application_ja'], ':application_en' => $old['application_en'],
                    ':spec_power' => $old['spec_power'], ':spec_output' => $old['spec_output'],
                    ':spec_current_range' => $old['spec_current_range'], ':spec_dimensions' => $old['spec_dimensions'],
                    ':spec_weight' => $old['spec_weight'], ':image' => $imageToSave, ':status' => $old['status'],
                    ':slug' => $slug, ':id' => $id,
                ]);
                if ($newImage) { delete_uploaded_image($product['image'] ?? null, 'products'); }
                set_flash('success', 'Product updated.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (category_id, name_ja, name_en, model, manufacturer, short_description_ja, short_description_en,
                     description_ja, description_en, features_ja, features_en, application_ja, application_en, spec_power, spec_output,
                     spec_current_range, spec_dimensions, spec_weight, image, status, slug)
                     VALUES (:category_id, :name_ja, :name_en, :model, :manufacturer, :short_description_ja, :short_description_en,
                     :description_ja, :description_en, :features_ja, :features_en, :application_ja, :application_en, :spec_power, :spec_output,
                     :spec_current_range, :spec_dimensions, :spec_weight, :image, :status, :slug)"
                );
                $stmt->execute([
                    ':category_id' => $old['category_id'], ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'],
                    ':model' => $old['model'], ':manufacturer' => $old['manufacturer'],
                    ':short_description_ja' => $old['short_description_ja'], ':short_description_en' => $old['short_description_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':features_ja' => $old['features_ja'], ':features_en' => $old['features_en'],
                    ':application_ja' => $old['application_ja'], ':application_en' => $old['application_en'],
                    ':spec_power' => $old['spec_power'], ':spec_output' => $old['spec_output'],
                    ':spec_current_range' => $old['spec_current_range'], ':spec_dimensions' => $old['spec_dimensions'],
                    ':spec_weight' => $old['spec_weight'], ':image' => $imageToSave, ':status' => $old['status'], ':slug' => $slug,
                ]);
                set_flash('success', 'Product created.');
            }
            redirect(admin_url('products.php'));
        }
    }
}

$adminTitle  = $id ? 'Edit Product' : 'Add Product';
$adminActive = 'products';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2><?= e($adminTitle) ?></h2>
        <a href="<?= e(admin_url('products.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="padding:20px 28px 0;">
        <div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>

        <div class="aform-section-title">Basic Info</div>
        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Category</label>
                <select name="category_id" class="aform-control">
                    <option value="">— None —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['id']) ?>" <?= (string)$old['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="aform-row">
                <label class="aform-label">Model</label>
                <input type="text" name="model" class="aform-control" value="<?= e($old['model']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Manufacturer</label>
                <input type="text" name="manufacturer" class="aform-control" value="<?= e($old['manufacturer']) ?>">
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
                <label class="aform-label">Short Description (Japanese)</label>
                <textarea name="short_description_ja" class="aform-control"><?= e($old['short_description_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Short Description (English)</label>
                <textarea name="short_description_en" class="aform-control"><?= e($old['short_description_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-section-title">Details</div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Full Description (Japanese)</label>
                <textarea name="description_ja" class="aform-control"><?= e($old['description_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Full Description (English)</label>
                <textarea name="description_en" class="aform-control"><?= e($old['description_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Features (Japanese)</label>
                <textarea name="features_ja" class="aform-control"><?= e($old['features_ja']) ?></textarea>
                <div class="aform-hint">One feature per line.</div>
            </div>
            <div class="aform-row">
                <label class="aform-label">Features (English)</label>
                <textarea name="features_en" class="aform-control"><?= e($old['features_en']) ?></textarea>
                <div class="aform-hint">One feature per line.</div>
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Application (Japanese)</label>
                <textarea name="application_ja" class="aform-control"><?= e($old['application_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Application (English)</label>
                <textarea name="application_en" class="aform-control"><?= e($old['application_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-section-title">Technical Specification</div>
        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Power Supply</label>
                <input type="text" name="spec_power" class="aform-control" value="<?= e($old['spec_power']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Rated Output</label>
                <input type="text" name="spec_output" class="aform-control" value="<?= e($old['spec_output']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Current Range</label>
                <input type="text" name="spec_current_range" class="aform-control" value="<?= e($old['spec_current_range']) ?>">
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Dimensions</label>
                <input type="text" name="spec_dimensions" class="aform-control" value="<?= e($old['spec_dimensions']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Weight</label>
                <input type="text" name="spec_weight" class="aform-control" value="<?= e($old['spec_weight']) ?>">
            </div>
        </div>

        <div class="aform-section-title">Image &amp; Status</div>
        <div class="aform-row">
            <label class="aform-label">Image</label>
            <?php if (!empty($old['image'])): ?>
            <div class="aform-current-image">
                <img src="<?= e(image_url($old['image'], 'products')) ?>" alt="">
                <span class="aform-hint">Current image. Upload a new file below to replace it.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">JPG, PNG, or WEBP. Max 5MB.</div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Status</label>
            <select name="status" class="aform-control">
                <option value="1" <?= $old['status'] ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !$old['status'] ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save Product</button>
            <a href="<?= e(admin_url('products.php')) ?>" class="abtn abtn--outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

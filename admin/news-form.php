<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $article = $stmt->fetch();
    if (!$article) { redirect(admin_url('news.php')); }
}

$errors = [];
$old = $article ?: [
    'category_ja' => 'COMPANY', 'category_en' => 'COMPANY', 'title_ja' => '', 'title_en' => '',
    'content_ja' => '', 'content_en' => '', 'publish_date' => date('Y-m-d'), 'status' => 1, 'image' => null, 'slug' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        foreach (['category_ja','category_en','title_ja','title_en','content_ja','content_en','publish_date'] as $field) {
            $old[$field] = clean($_POST[$field] ?? '');
        }
        $old['status'] = isset($_POST['status']) ? 1 : 0;

        if ($old['title_ja'] === '' || $old['title_en'] === '') {
            $errors[] = 'Title (Japanese) and Title (English) are both required.';
        }
        if ($old['publish_date'] === '') {
            $old['publish_date'] = date('Y-m-d');
        }

        $newImage = null;
        try {
            $newImage = handle_image_upload('image', 'news');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $imageToSave = $newImage ?? $old['image'];
            $slug = unique_slug($pdo, 'news', $old['title_en'], $id ?: null);

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE news SET category_ja=:category_ja, category_en=:category_en, title_ja=:title_ja, title_en=:title_en,
                     content_ja=:content_ja, content_en=:content_en, publish_date=:publish_date, image=:image, status=:status, slug=:slug
                     WHERE id=:id"
                );
                $stmt->execute([
                    ':category_ja' => $old['category_ja'], ':category_en' => $old['category_en'],
                    ':title_ja' => $old['title_ja'], ':title_en' => $old['title_en'],
                    ':content_ja' => $old['content_ja'], ':content_en' => $old['content_en'],
                    ':publish_date' => $old['publish_date'], ':image' => $imageToSave, ':status' => $old['status'],
                    ':slug' => $slug, ':id' => $id,
                ]);
                if ($newImage) { delete_uploaded_image($article['image'] ?? null, 'news'); }
                set_flash('success', 'News article updated.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO news (category_ja, category_en, title_ja, title_en, content_ja, content_en, publish_date, image, status, slug)
                     VALUES (:category_ja, :category_en, :title_ja, :title_en, :content_ja, :content_en, :publish_date, :image, :status, :slug)"
                );
                $stmt->execute([
                    ':category_ja' => $old['category_ja'], ':category_en' => $old['category_en'],
                    ':title_ja' => $old['title_ja'], ':title_en' => $old['title_en'],
                    ':content_ja' => $old['content_ja'], ':content_en' => $old['content_en'],
                    ':publish_date' => $old['publish_date'], ':image' => $imageToSave, ':status' => $old['status'], ':slug' => $slug,
                ]);
                set_flash('success', 'News article created.');
            }
            redirect(admin_url('news.php'));
        }
    }
}

$adminTitle  = $id ? 'Edit News' : 'Add News';
$adminActive = 'news';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2><?= e($adminTitle) ?></h2>
        <a href="<?= e(admin_url('news.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="padding:20px 28px 0;">
        <div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>

        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Category (Japanese)</label>
                <input type="text" name="category_ja" class="aform-control" value="<?= e($old['category_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Category (English)</label>
                <input type="text" name="category_en" class="aform-control" value="<?= e($old['category_en']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Publish Date</label>
                <input type="date" name="publish_date" class="aform-control" value="<?= e($old['publish_date']) ?>">
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
                <label class="aform-label">Content (Japanese)</label>
                <textarea name="content_ja" class="aform-control" style="min-height:180px;"><?= e($old['content_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Content (English)</label>
                <textarea name="content_en" class="aform-control" style="min-height:180px;"><?= e($old['content_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-row">
            <label class="aform-label">Image</label>
            <?php if (!empty($old['image'])): ?>
            <div class="aform-current-image">
                <img src="<?= e(image_url($old['image'], 'news')) ?>" alt="">
                <span class="aform-hint">Current image. Upload a new file below to replace it.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">JPG, PNG, or WEBP. Max 5MB.</div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Status</label>
            <select name="status" class="aform-control">
                <option value="1" <?= $old['status'] ? 'selected' : '' ?>>Published</option>
                <option value="0" <?= !$old['status'] ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save News</button>
            <a href="<?= e(admin_url('news.php')) ?>" class="abtn abtn--outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

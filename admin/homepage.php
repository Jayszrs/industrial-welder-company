<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$sections = ['hero', 'about', 'strength', 'cta'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        $sectionKey = clean($_POST['section_key'] ?? '');
        if (!in_array($sectionKey, $sections, true)) {
            $errors[] = 'Unknown section.';
        } else {
            $titleJa    = clean($_POST['title_ja'] ?? '');
            $titleEn    = clean($_POST['title_en'] ?? '');
            $subtitleJa = clean($_POST['subtitle_ja'] ?? '');
            $subtitleEn = clean($_POST['subtitle_en'] ?? '');
            $contentJa  = clean($_POST['content_ja'] ?? '');
            $contentEn  = clean($_POST['content_en'] ?? '');

            $stmt = $pdo->prepare("SELECT image FROM homepage_content WHERE section_key = :k");
            $stmt->execute([':k' => $sectionKey]);
            $current = $stmt->fetch();

            $newImage = null;
            try {
                $newImage = handle_image_upload('image', 'homepage');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }

            if (empty($errors)) {
                $imageToSave = $newImage ?? ($current['image'] ?? null);
                $stmt = $pdo->prepare(
                    "UPDATE homepage_content SET title_ja=:tja, title_en=:ten, subtitle_ja=:sja, subtitle_en=:sen,
                     content_ja=:cja, content_en=:cen, image=:image WHERE section_key=:key"
                );
                $stmt->execute([
                    ':tja' => $titleJa, ':ten' => $titleEn, ':sja' => $subtitleJa, ':sen' => $subtitleEn,
                    ':cja' => $contentJa, ':cen' => $contentEn, ':image' => $imageToSave, ':key' => $sectionKey,
                ]);
                if ($newImage && !empty($current['image'])) {
                    delete_uploaded_image($current['image'], 'homepage');
                }
                set_flash('success', ucfirst($sectionKey) . ' section updated.');
                redirect(admin_url('homepage.php'));
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM homepage_content");
$content = [];
foreach ($stmt->fetchAll() as $row) {
    $content[$row['section_key']] = $row;
}
$flash = get_flash();

$adminTitle  = 'Homepage Content';
$adminActive = 'homepage';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<!-- HERO -->
<div class="admin-panel">
    <div class="admin-panel-head"><h2>Hero Section</h2></div>
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="section_key" value="hero">
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Title (Japanese)</label>
                <textarea name="title_ja" class="aform-control"><?= e($content['hero']['title_ja'] ?? '') ?></textarea>
                <div class="aform-hint">Use a line break for a two-line headline.</div>
            </div>
            <div class="aform-row">
                <label class="aform-label">Title (English)</label>
                <textarea name="title_en" class="aform-control"><?= e($content['hero']['title_en'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Subtitle (Japanese)</label>
                <textarea name="content_ja" class="aform-control"><?= e($content['hero']['content_ja'] ?? '') ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Subtitle (English)</label>
                <textarea name="content_en" class="aform-control"><?= e($content['hero']['content_en'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Background Image</label>
            <?php if (!empty($content['hero']['image'])): ?>
            <div class="aform-current-image"><img src="<?= e(image_url($content['hero']['image'], 'homepage')) ?>" alt=""><span class="aform-hint">Current image.</span></div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save Hero</button></div>
    </form>
</div>

<!-- ABOUT -->
<div class="admin-panel">
    <div class="admin-panel-head"><h2>About Section</h2></div>
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="section_key" value="about">
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Title (Japanese)</label>
                <input type="text" name="title_ja" class="aform-control" value="<?= e($content['about']['title_ja'] ?? '') ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Title (English)</label>
                <input type="text" name="title_en" class="aform-control" value="<?= e($content['about']['title_en'] ?? '') ?>">
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Content (Japanese)</label>
                <textarea name="content_ja" class="aform-control" style="min-height:160px;"><?= e($content['about']['content_ja'] ?? '') ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Content (English)</label>
                <textarea name="content_en" class="aform-control" style="min-height:160px;"><?= e($content['about']['content_en'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Image</label>
            <?php if (!empty($content['about']['image'])): ?>
            <div class="aform-current-image"><img src="<?= e(image_url($content['about']['image'], 'homepage')) ?>" alt=""><span class="aform-hint">Current image.</span></div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save About</button></div>
    </form>
</div>

<!-- STRENGTH -->
<div class="admin-panel">
    <div class="admin-panel-head"><h2>Strength / Quality Section Title</h2></div>
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="section_key" value="strength">
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Title (Japanese)</label>
                <input type="text" name="title_ja" class="aform-control" value="<?= e($content['strength']['title_ja'] ?? '') ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Title (English)</label>
                <input type="text" name="title_en" class="aform-control" value="<?= e($content['strength']['title_en'] ?? '') ?>">
            </div>
        </div>
        <div class="aform-hint" style="margin-bottom:16px;">Manage the individual numbers under "Strength / Stats" in the sidebar.</div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save Section Title</button></div>
    </form>
</div>

<!-- CTA -->
<div class="admin-panel">
    <div class="admin-panel-head"><h2>Contact CTA Band</h2></div>
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="section_key" value="cta">
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Title (Japanese)</label>
                <input type="text" name="title_ja" class="aform-control" value="<?= e($content['cta']['title_ja'] ?? '') ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Title (English)</label>
                <input type="text" name="title_en" class="aform-control" value="<?= e($content['cta']['title_en'] ?? '') ?>">
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Subtitle (Japanese)</label>
                <textarea name="subtitle_ja" class="aform-control"><?= e($content['cta']['subtitle_ja'] ?? '') ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Subtitle (English)</label>
                <textarea name="subtitle_en" class="aform-control"><?= e($content['cta']['subtitle_en'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save CTA</button></div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

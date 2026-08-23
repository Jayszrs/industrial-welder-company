<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
ensure_hero_slides_table($pdo);
$sections = ['hero', 'about', 'strength', 'cta'];
$errors = [];
$maxSlidesPerBatch = max(1, (int) ini_get('max_file_uploads'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        $action = clean($_POST['action'] ?? 'update_section');

        if ($action === 'add_hero_slides') {
            $uploadedSlides = [];
            try {
                $uploadedSlides = handle_multiple_image_uploads('slides', 'hero-slides');
                if (empty($uploadedSlides)) {
                    $errors[] = 'Choose at least one image to upload.';
                } else {
                    $nextOrder = (int) $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM hero_slides")->fetchColumn();
                    $stmt = $pdo->prepare("INSERT INTO hero_slides (image, sort_order, status) VALUES (:image, :sort_order, 1)");
                    $pdo->beginTransaction();
                    foreach ($uploadedSlides as $offset => $filename) {
                        $stmt->execute([':image' => $filename, ':sort_order' => $nextOrder + $offset]);
                    }
                    $pdo->commit();
                    set_flash('success', count($uploadedSlides) . ' hero slide(s) uploaded.');
                    redirect(admin_url('homepage.php#hero-slideshow'));
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                foreach ($uploadedSlides as $filename) {
                    delete_uploaded_image($filename, 'hero-slides');
                }
                $errors[] = $exception->getMessage();
            }
        } elseif ($action === 'update_hero_slide') {
            $slideId = (int) ($_POST['slide_id'] ?? 0);
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $status = (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE hero_slides SET sort_order=:sort_order, status=:status WHERE id=:id");
            $stmt->execute([':sort_order' => $sortOrder, ':status' => $status, ':id' => $slideId]);
            set_flash('success', 'Hero slide updated.');
            redirect(admin_url('homepage.php#hero-slideshow'));
        } elseif ($action === 'delete_hero_slide') {
            $slideId = (int) ($_POST['slide_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT image FROM hero_slides WHERE id=:id");
            $stmt->execute([':id' => $slideId]);
            $slide = $stmt->fetch();
            if ($slide) {
                $pdo->prepare("DELETE FROM hero_slides WHERE id=:id")->execute([':id' => $slideId]);
                delete_uploaded_image($slide['image'], 'hero-slides');
            }
            set_flash('success', 'Hero slide deleted.');
            redirect(admin_url('homepage.php#hero-slideshow'));
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
                redirect(admin_url('homepage.php#section-' . $sectionKey));
            }
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM homepage_content");
$content = [];
foreach ($stmt->fetchAll() as $row) {
    $content[$row['section_key']] = $row;
}
$heroSlides = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC")->fetchAll();
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
<div class="admin-panel" id="section-hero">
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
            <label class="aform-label">Fallback Background Image</label>
            <?php if (!empty($content['hero']['image'])): ?>
            <div class="aform-current-image"><img src="<?= e(image_url($content['hero']['image'], 'homepage')) ?>" alt=""><span class="aform-hint">Current image.</span></div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">Only used when the slideshow list below is empty.</div>
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save Hero</button></div>
    </form>
</div>

<!-- UNLIMITED HERO SLIDESHOW -->
<div class="admin-panel" id="hero-slideshow">
    <div class="admin-panel-head">
        <h2>Hero Slideshow (<?= count($heroSlides) ?> images)</h2>
        <span class="aform-hint">Automatically changes every 3 seconds</span>
    </div>
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_hero_slides">
        <div class="aform-row">
            <label class="aform-label">Upload Slideshow Images</label>
            <input type="file" name="slides[]" class="aform-control" accept=".jpg,.jpeg,.png,.webp" multiple data-image-upload>
            <div class="aform-hint">This server accepts up to <?= $maxSlidesPerBatch ?> files per batch. Upload additional batches as needed; the slideshow itself has no fixed total limit.</div>
        </div>
        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Upload Images</button>
        </div>
    </form>

    <?php if (empty($heroSlides)): ?>
    <div class="ahero-empty">No uploaded slides yet. The three sample images remain active until the first upload.</div>
    <?php else: ?>
    <div class="ahero-slide-grid">
        <?php foreach ($heroSlides as $slide): ?>
        <article class="ahero-slide-card">
            <div class="ahero-slide-card__image">
                <img src="<?= e(image_url($slide['image'], 'hero-slides')) ?>" alt="Hero slide <?= (int) $slide['id'] ?>">
                <span>#<?= (int) $slide['id'] ?></span>
            </div>
            <form method="post" class="ahero-slide-card__form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_hero_slide">
                <input type="hidden" name="slide_id" value="<?= (int) $slide['id'] ?>">
                <div>
                    <label class="aform-label">Order</label>
                    <input type="number" name="sort_order" class="aform-control" value="<?= (int) $slide['sort_order'] ?>">
                </div>
                <div>
                    <label class="aform-label">Status</label>
                    <select name="status" class="aform-control">
                        <option value="1" <?= (int) $slide['status'] === 1 ? 'selected' : '' ?>>Visible</option>
                        <option value="0" <?= (int) $slide['status'] === 0 ? 'selected' : '' ?>>Hidden</option>
                    </select>
                </div>
                <button type="submit" class="abtn abtn--outline abtn--sm">Save</button>
            </form>
            <form method="post" class="confirm-delete ahero-slide-card__delete" data-confirm="Delete this slideshow image permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_hero_slide">
                <input type="hidden" name="slide_id" value="<?= (int) $slide['id'] ?>">
                <button type="submit" class="abtn abtn--danger abtn--sm">Delete</button>
            </form>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ABOUT -->
<div class="admin-panel" id="section-about">
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
<div class="admin-panel" id="section-strength">
    <div class="admin-panel-head"><h2>Homepage Strength / Quality</h2><a class="aform-hint" href="<?= e(base_url('index.php#strength')) ?>" target="_blank" rel="noopener">Open live section ↗</a></div>
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
        <div class="aform-row">
            <label class="aform-label">Strength Background Image</label>
            <?php if (!empty($content['strength']['image'])): ?>
            <div class="aform-current-image"><img src="<?= e(image_url($content['strength']['image'], 'homepage')) ?>" alt=""><span class="aform-hint">Current image.</span></div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">Shown behind the “Our Strength” statistics on the homepage and in the quality section of About Us. Recommended: landscape 1920 × 1080, JPG/PNG/WEBP, max 5MB.</div>
        </div>
        <div class="aform-hint" style="margin-bottom:16px;">Manage the individual numbers under "Strength / Stats" in the sidebar.</div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save Strength Section</button></div>
    </form>
</div>

<!-- CTA -->
<div class="admin-panel" id="section-cta">
    <div class="admin-panel-head"><h2>Contact CTA Band</h2><a class="aform-hint" href="<?= e(base_url('index.php#contact-cta')) ?>" target="_blank" rel="noopener">Open live section ↗</a></div>
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
        <div class="aform-row">
            <label class="aform-label">CTA Background Image</label>
            <?php if (!empty($content['cta']['image'])): ?>
            <div class="aform-current-image"><img src="<?= e(image_url($content['cta']['image'], 'homepage')) ?>" alt=""><span class="aform-hint">Current image.</span></div>
            <?php else: ?>
            <div class="aform-current-image"><img src="<?= e(base_url('assets/images/hero-welding-v2.jpg')) ?>" alt=""><span class="aform-hint">Sample fallback currently shown. Upload an image below to replace it.</span></div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">Shown behind the final contact invitation on the homepage and About Us page. Recommended: wide landscape 1920 × 900, JPG/PNG/WEBP, max 5MB.</div>
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save CTA</button></div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>

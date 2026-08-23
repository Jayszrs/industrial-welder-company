<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$facilities = $pdo->query("SELECT * FROM facilities WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$pageHeaderImage = page_header_image_url($facilities[0]['image'] ?? null, 'facilities', 2);

$pageTitle       = t('facility_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'products'; // facility sits under the products/equipment umbrella in the main nav

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('facility_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('facility_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_facility')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="section-head reveal">
            <p class="section-subtitle" style="max-width:680px;"><?= e(t('facility_subtitle')) ?></p>
        </div>

        <div class="grid-cards">
            <?php foreach ($facilities as $f): ?>
            <?php $facilityInquiryUrl = base_url('contact.php?inquiry_type=quote&product=' . urlencode(tf($f, 'machine_name'))); ?>
            <article class="card reveal facility-card" id="facility-<?= e($f['id']) ?>">
                <a href="<?= e($facilityInquiryUrl) ?>" class="card-media" aria-label="<?= e(tf($f, 'machine_name')) ?>">
                    <img src="<?= e(image_url($f['image'], 'facilities')) ?>" alt="<?= e(tf($f, 'machine_name')) ?>" loading="lazy">
                    <span class="card-media-action" aria-hidden="true">&#8594;</span>
                </a>
                <div class="card-body">
                    <div class="card-meta"><?= e($f['manufacturer']) ?> — <?= e($f['model']) ?></div>
                    <h3 class="card-title"><a href="<?= e($facilityInquiryUrl) ?>"><?= e(tf($f, 'machine_name')) ?></a></h3>
                    <p class="card-text"><?= e(tf($f, 'description')) ?></p>
                    <div class="card-foot">
                        <a href="<?= e($facilityInquiryUrl) ?>" class="text-link">
                            <?= $CURRENT_LANG === 'ja' ? 'この設備について相談' : 'Ask about this equipment' ?> <span class="arrow">&#8594;</span>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="facility-table-wrap reveal">
            <table class="facility-table">
                <thead>
                    <tr>
                        <th><?= e(t('nav_facility')) ?></th>
                        <th><?= e(t('facility_manufacturer')) ?></th>
                        <th><?= e(t('facility_model')) ?></th>
                        <th><?= e(t('facility_capacity')) ?></th>
                        <th><?= e(t('facility_quantity')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities as $f): ?>
                    <tr>
                        <td><?= e(tf($f, 'machine_name')) ?></td>
                        <td><?= e($f['manufacturer']) ?></td>
                        <td><?= e($f['model']) ?></td>
                        <td><?= e($f['capacity']) ?></td>
                        <td><?= e($f['quantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <h2 class="section-title"><?= e(t('about_cta_title')) ?></h2>
        <div class="cta-band-actions">
            <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--outline-dark">
                <?= e(t('about_cta_button')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

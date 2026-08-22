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
            <div class="card reveal">
                <div class="card-media">
                    <img src="<?= e(image_url($f['image'], 'facilities')) ?>" alt="<?= e(tf($f, 'machine_name')) ?>" loading="lazy">
                </div>
                <div class="card-body">
                    <div class="card-meta"><?= e($f['manufacturer']) ?> — <?= e($f['model']) ?></div>
                    <h3 class="card-title"><?= e(tf($f, 'machine_name')) ?></h3>
                    <p class="card-text"><?= e(tf($f, 'description')) ?></p>
                </div>
            </div>
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
